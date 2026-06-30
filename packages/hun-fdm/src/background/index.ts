import { createPlugin } from "@skyvexsoftware/stratos-sdk/helpers";
import { getThresholds, getProfile } from "../config/thresholds";
import type { FdmExceedance, FdmReport } from "../types/fdm";

// Cooldown prevents the same exceedance from firing repeatedly (ms)
const EXCEEDANCE_COOLDOWN_MS = 10_000;

export default createPlugin({
  async onStart(ctx) {
    ctx.logger.info("HunFDM", "FDM background module started");

    // ── State ──────────────────────────────────────────────────────────────
    const exceedances: FdmExceedance[] = [];
    const lastFired: Record<string, number> = {};
    let currentPhase = "PREFLIGHT";
    let flightStartMs: number | null = null;
    let aircraftIcao = "UNKN";
    let landingScore = 100;

    // ── Flight phase tracking ──────────────────────────────────────────────
    ctx.socket.on("flight:phase", (payload: { phase: string }) => {
      currentPhase = payload.phase ?? currentPhase;
      ctx.logger.debug("HunFDM", `Phase → ${currentPhase}`);
      if (currentPhase === "TAXIOUT" && flightStartMs === null) {
        flightStartMs = Date.now();
      }
    });

    // ── Capture Stratos native landing score ──────────────────────────────
    ctx.socket.on("flight:event", (event: {
      category: string;
      type?: string;
      data?: { score?: number; aircraft?: { icao?: string } };
    }) => {
      if (event.category === "LANDING" && typeof event.data?.score === "number") {
        landingScore = Math.max(0, Math.min(100, event.data.score));
        ctx.logger.info("HunFDM", `Landing score received: ${landingScore}`);
      }
      if (event.data?.aircraft?.icao) {
        aircraftIcao = event.data.aircraft.icao;
      }
    });

    // ── Main telemetry monitoring ──────────────────────────────────────────
    ctx.socket.on("simulator:data", (payload: {
      data?: {
        indicatedAirspeed?: number;
        groundSpeed?: number;
        altitude?: number;
        verticalSpeed?: number;
        bankAngle?: number;
        pitchAngle?: number;
        gForce?: number;
        mach?: number;
        flapsHandleIndex?: number;
        gearHandlePosition?: number;
        lightsLanding?: boolean;
      };
    }) => {
      const d = payload.data;
      if (!d) return;

      const now = Date.now();
      const t = getThresholds(aircraftIcao);
      const lim = t.limits;

      const record = (key: keyof typeof t, value: number) => {
        const threshold = t[key];
        if (typeof threshold !== "object" || !("deduction" in threshold)) return;
        const last = lastFired[key] ?? 0;
        if (now - last < EXCEEDANCE_COOLDOWN_MS) return;
        lastFired[key] = now;
        exceedances.push({
          key,
          label: threshold.label,
          deduction: threshold.deduction,
          critical: threshold.critical,
          timestamp: now,
          value,
          phase: currentPhase,
        });
        ctx.logger.warn("HunFDM", `Exceedance: ${threshold.label} @ ${value}`);
      };

      const ias = d.indicatedAirspeed ?? 0;
      const alt = d.altitude ?? 0;
      const vs  = d.verticalSpeed ?? 0;
      const gs  = d.groundSpeed ?? 0;

      // Speed exceedances
      if (alt < 10_000 && ias > 250)                                         record("overspeedBelow10k", ias);
      if ((d.mach ?? 0) > lim.mmo)                                           record("overspeedMmo", d.mach ?? 0);
      if (ias > lim.vmo)                                                     record("overspeedVmo", ias);
      if ((d.flapsHandleIndex ?? 0) > 0 && ias > lim.maxFlapSpeed)          record("flapsOverspeed", ias);
      if ((d.gearHandlePosition ?? 0) > 0.5 && ias > lim.maxGearSpeed)      record("gearOverspeed", ias);

      // Vertical speed exceedances
      if (alt < 10_000 && vs < -2500 && currentPhase !== "LANDING")         record("excessiveVsDescent", vs);
      if (alt < 2_500 && vs < -1500 && currentPhase === "APPROACH")         record("excessiveVsApproach", vs);
      if (alt > 10_000 && vs < -6_000)                                       record("excessiveVsCruise", vs);

      // Attitude exceedances
      const bank  = Math.abs(d.bankAngle ?? 0);
      const pitch = d.pitchAngle ?? 0;
      if (bank > lim.maxBankAngle)                                           record("excessiveBankAngle", bank);
      if (pitch > lim.maxPitchUp)                                            record("excessivePitchUp", pitch);
      if (pitch < lim.maxPitchDown)                                          record("excessivePitchDown", pitch);

      // G-force
      const g = d.gForce ?? 1;
      if (g > lim.maxGForce)                                                 record("excessiveGForce", g);
      if (g < lim.minGForce)                                                 record("negativeGForce", g);

      // Procedure exceedances
      if (alt < 10_000 && !d.lightsLanding
        && currentPhase !== "PREFLIGHT" && currentPhase !== "SHUTDOWN")      record("lightsOffBelow10k", alt);
      if (alt > 10_000 && (d.gearHandlePosition ?? 0) > 0.5
        && currentPhase === "CRUISE")                                         record("gearExtendedCruise", alt);
      if (["TAXIOUT", "TAXIIN"].includes(currentPhase) && gs > 30)           record("taxiOverspeed", gs);
    });

    // ── PIREP filed → build and submit FDM report ─────────────────────────
    ctx.socket.on("pirep:filing", async (payload: {
      pirepId?: string | number;
      flightId?: string | number;
      aircraft?: { icao?: string };
    }) => {
      aircraftIcao = payload.aircraft?.icao ?? aircraftIcao;

      const totalDeductions = exceedances.reduce((sum, e) => sum + e.deduction, 0);
      const finalScore = Math.max(0, landingScore - totalDeductions);
      const durationMin = flightStartMs
        ? Math.round((Date.now() - flightStartMs) / 60_000)
        : 0;

      const report: FdmReport = {
        pirepId: payload.pirepId,
        flightId: payload.flightId,
        aircraftIcao,
        flightProfile: getProfile(aircraftIcao),
        landingScore,
        fdmDeductions: totalDeductions,
        finalScore,
        exceedances,
        exceedanceCount: exceedances.length,
        criticalCount: exceedances.filter((e) => e.critical).length,
        flightDurationMin: durationMin,
        recordedAt: new Date().toISOString(),
      };

      ctx.logger.info("HunFDM", `Submitting FDM report. Score: ${finalScore}`);

      try {
        const client = ctx.airline.createClient();

        // POST full FDM report to phpVMS HunFDM module
        await client.post("/api/hunfdm/report", report);

        // PATCH PIREP score_percent with FDM final score
        if (payload.pirepId) {
          await client.patch(`/api/pireps/${payload.pirepId}`, {
            score_percent: finalScore,
          });
          ctx.logger.info("HunFDM", `PIREP ${payload.pirepId} score updated to ${finalScore}`);
        }
      } catch (err) {
        ctx.logger.error("HunFDM", "Failed to submit FDM report", err);
      }

      // Reset state for next flight
      exceedances.length = 0;
      Object.keys(lastFired).forEach((k) => delete lastFired[k]);
      flightStartMs = null;
      landingScore = 100;
      currentPhase = "PREFLIGHT";
    });
  },

  async onStop(ctx) {
    ctx.logger.info("HunFDM", "FDM background module stopped");
  },
});
