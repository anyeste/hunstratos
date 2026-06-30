import { useFlightEvents, useSimData, EventCategory } from "@skyvexsoftware/stratos-sdk";
import "./styles.css";

function ScoreBadge({ score }: { score: number }) {
  const colour =
    score >= 80 ? "bg-green-600" :
    score >= 60 ? "bg-yellow-500" :
    score >= 40 ? "bg-orange-500" : "bg-red-600";
  return (
    <span className={`inline-block rounded px-3 py-1 text-white font-bold text-lg ${colour}`}>
      {score}
    </span>
  );
}

function Row({ label, value, warn }: { label: string; value: string | number; warn?: boolean }) {
  return (
    <div className={`flex justify-between text-sm py-0.5 ${warn ? "text-red-400 font-semibold" : "text-text-muted"}`}>
      <span>{label}</span>
      <span className="font-mono">{value}</span>
    </div>
  );
}

export default function Plugin() {
  const { data: sim } = useSimData({
    select: (s) => ({
      ias:    s.data?.indicatedAirspeed ?? 0,
      vs:     s.data?.verticalSpeed ?? 0,
      alt:    s.data?.altitude ?? 0,
      bank:   Math.abs(s.data?.bankAngle ?? 0),
      pitch:  s.data?.pitchAngle ?? 0,
      gForce: s.data?.gForce ?? 1,
      mach:   s.data?.mach ?? 0,
      phase:  (s as any).phase ?? "—",
    }),
  });

  const { events } = useFlightEvents({
    categories: [EventCategory.WARNING, EventCategory.LANDING],
  });

  const landingEvent = [...events].reverse().find(
    (e) => e.category === EventCategory.LANDING
  );
  const landingScore: number | null = (landingEvent as any)?.data?.score ?? null;
  const warnings = events.filter((e) => e.category === EventCategory.WARNING);
  const totalDeductions = warnings.reduce((s: number, _: any) => s, 0);

  return (
    <div className="p-4 space-y-4 text-foreground">
      {/* Header */}
      <div className="flex items-center justify-between">
        <h1 className="text-base font-bold tracking-wide">HUN VA · FDM</h1>
        <span className="text-xs text-text-muted uppercase tracking-wider">
          {sim?.phase ?? "—"}
        </span>
      </div>

      {/* Live parameter strip */}
      <div className="rounded-lg bg-surface-secondary p-3 space-y-1">
        <p className="text-xs font-semibold text-text-muted uppercase tracking-wider mb-2">Live Parameters</p>
        <Row label="IAS"        value={`${(sim?.ias ?? 0).toFixed(0)} kt`}   warn={(sim?.ias ?? 0) > 250 && (sim?.alt ?? 0) < 10000} />
        <Row label="Mach"       value={(sim?.mach ?? 0).toFixed(3)}           warn={(sim?.mach ?? 0) > 0.82} />
        <Row label="Altitude"   value={`${(sim?.alt ?? 0).toFixed(0)} ft`} />
        <Row label="Vert Speed" value={`${(sim?.vs ?? 0).toFixed(0)} fpm`}   warn={(sim?.vs ?? 0) < -2500 && (sim?.alt ?? 0) < 10000} />
        <Row label="Bank"       value={`${(sim?.bank ?? 0).toFixed(1)}°`}    warn={(sim?.bank ?? 0) > 35} />
        <Row label="Pitch"      value={`${(sim?.pitch ?? 0).toFixed(1)}°`}   warn={(sim?.pitch ?? 0) > 20 || (sim?.pitch ?? 0) < -10} />
        <Row label="G-Force"    value={`${(sim?.gForce ?? 1).toFixed(2)} g`} warn={(sim?.gForce ?? 1) > 2.5 || (sim?.gForce ?? 1) < 0} />
      </div>

      {/* Active exceedances */}
      {warnings.length > 0 && (
        <div className="rounded-lg bg-surface-secondary p-3">
          <p className="text-xs font-semibold text-red-400 uppercase tracking-wider mb-2">
            Exceedances ({warnings.length})
          </p>
          <ul className="space-y-1 max-h-36 overflow-y-auto">
            {warnings.map((w: any) => (
              <li key={w.eventId} className="text-xs text-red-300 flex justify-between">
                <span>{w.message}</span>
                <span className="text-text-muted font-mono">
                  {new Date(w.timestamp ?? Date.now()).toLocaleTimeString()}
                </span>
              </li>
            ))}
          </ul>
        </div>
      )}

      {/* Post-flight score card */}
      {landingScore !== null && (
        <div className="rounded-lg bg-surface-secondary p-3 flex items-center justify-between">
          <div>
            <p className="text-xs font-semibold text-text-muted uppercase tracking-wider">Landing Score</p>
            <p className="text-xs text-text-muted mt-0.5">
              {warnings.length} exceedance{warnings.length !== 1 ? "s" : ""}
            </p>
          </div>
          <ScoreBadge score={landingScore} />
        </div>
      )}
    </div>
  );
}
