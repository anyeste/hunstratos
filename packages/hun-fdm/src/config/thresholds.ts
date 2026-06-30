// ============================================================
// HUN VA FDM – Fleet-aware threshold configuration
// All speeds in knots, altitudes in feet, VS in fpm, angles in degrees
// Deduction values are subtracted from the Stratos landing score
// ============================================================

export type FlightProfile = "NARROWBODY" | "WIDEBODY" | "FREIGHTER" | "REGIONAL" | "GA";

export interface Threshold {
  /** Human-readable label shown in FDM report */
  label: string;
  /** Score deduction per occurrence */
  deduction: number;
  /** Whether this is a hard reject (marks PIREP for review) */
  critical: boolean;
}

export interface FleetThresholds {
  // Speed
  overspeedBelow10k: Threshold;
  overspeedMmo: Threshold;
  overspeedVmo: Threshold;
  flapsOverspeed: Threshold;
  gearOverspeed: Threshold;

  // Vertical
  excessiveVsDescent: Threshold;
  excessiveVsApproach: Threshold;
  excessiveVsCruise: Threshold;

  // Attitude / Structure
  excessiveBankAngle: Threshold;
  excessivePitchUp: Threshold;
  excessivePitchDown: Threshold;
  excessiveGForce: Threshold;
  negativeGForce: Threshold;

  // Procedures
  lightsOffBelow10k: Threshold;
  gearExtendedCruise: Threshold;
  taxiOverspeed: Threshold;

  // Limits (raw values, used by monitoring logic)
  limits: {
    vmo: number;
    mmo: number;
    maxFlapSpeed: number;
    maxGearSpeed: number;
    maxBankAngle: number;
    maxPitchUp: number;
    maxPitchDown: number;
    maxGForce: number;
    minGForce: number;
  };
}

// ── Per-profile threshold definitions ───────────────────────────────────────

const NARROWBODY: FleetThresholds = {
  overspeedBelow10k:    { label: "Overspeed below 10,000 ft (>250 kt)",    deduction: 15, critical: true },
  overspeedMmo:         { label: "Mach overspeed (>M0.82)",                deduction: 20, critical: true },
  overspeedVmo:         { label: "VMO overspeed (>350 kt IAS)",            deduction: 20, critical: true },
  flapsOverspeed:       { label: "Flap overspeed",                         deduction: 15, critical: true },
  gearOverspeed:        { label: "Gear overspeed (>270 kt)",               deduction: 15, critical: true },
  excessiveVsDescent:   { label: "Excessive VS below 10,000 ft (<-2500 fpm)", deduction: 10, critical: false },
  excessiveVsApproach:  { label: "Excessive VS on approach (<-1500 fpm)",  deduction: 15, critical: false },
  excessiveVsCruise:    { label: "Excessive VS in cruise (<-6000 fpm)",    deduction: 10, critical: false },
  excessiveBankAngle:   { label: "Excessive bank angle (>35°)",            deduction: 10, critical: false },
  excessivePitchUp:     { label: "Excessive pitch up (>20°)",              deduction: 10, critical: false },
  excessivePitchDown:   { label: "Excessive pitch down (<-10°)",           deduction: 10, critical: false },
  excessiveGForce:      { label: "Excessive G-force (>2.5g)",              deduction: 15, critical: true },
  negativeGForce:       { label: "Negative G-force (<0g)",                 deduction: 15, critical: true },
  lightsOffBelow10k:    { label: "Landing lights off below 10,000 ft",     deduction:  5, critical: false },
  gearExtendedCruise:   { label: "Gear extended above 10,000 ft",          deduction: 10, critical: false },
  taxiOverspeed:        { label: "Taxi overspeed (>30 kt GS)",             deduction:  5, critical: false },
  limits: {
    vmo: 350, mmo: 0.82,
    maxFlapSpeed: 230, maxGearSpeed: 270,
    maxBankAngle: 35, maxPitchUp: 20, maxPitchDown: -10,
    maxGForce: 2.5, minGForce: 0,
  },
};

const WIDEBODY: FleetThresholds = {
  ...NARROWBODY,
  overspeedMmo:         { label: "Mach overspeed (>M0.89)",                deduction: 20, critical: true },
  overspeedVmo:         { label: "VMO overspeed (>340 kt IAS)",            deduction: 20, critical: true },
  gearOverspeed:        { label: "Gear overspeed (>270 kt)",               deduction: 15, critical: true },
  excessiveBankAngle:   { label: "Excessive bank angle (>33°)",            deduction: 10, critical: false },
  limits: {
    vmo: 340, mmo: 0.89,
    maxFlapSpeed: 250, maxGearSpeed: 270,
    maxBankAngle: 33, maxPitchUp: 20, maxPitchDown: -10,
    maxGForce: 2.5, minGForce: 0,
  },
};

const FREIGHTER: FleetThresholds = {
  ...NARROWBODY,
  overspeedMmo:         { label: "Mach overspeed (>M0.82)",                deduction: 20, critical: true },
  overspeedVmo:         { label: "VMO overspeed (>340 kt IAS)",            deduction: 20, critical: true },
  excessiveGForce:      { label: "Excessive G-force (>2.0g)",              deduction: 15, critical: true },
  limits: {
    vmo: 340, mmo: 0.82,
    maxFlapSpeed: 230, maxGearSpeed: 270,
    maxBankAngle: 35, maxPitchUp: 20, maxPitchDown: -10,
    maxGForce: 2.0, minGForce: 0,
  },
};

const REGIONAL: FleetThresholds = {
  ...NARROWBODY,
  overspeedMmo:         { label: "Mach overspeed (>M0.82)",                deduction: 20, critical: true },
  overspeedVmo:         { label: "VMO overspeed (>320 kt IAS)",            deduction: 20, critical: true },
  gearOverspeed:        { label: "Gear overspeed (>250 kt)",               deduction: 15, critical: true },
  excessiveVsApproach:  { label: "Excessive VS on approach (<-1200 fpm)",  deduction: 15, critical: false },
  limits: {
    vmo: 320, mmo: 0.82,
    maxFlapSpeed: 200, maxGearSpeed: 250,
    maxBankAngle: 30, maxPitchUp: 18, maxPitchDown: -8,
    maxGForce: 2.5, minGForce: 0,
  },
};

const GA: FleetThresholds = {
  ...REGIONAL,
  overspeedVmo:         { label: "VMO overspeed (>200 kt IAS)",            deduction: 20, critical: true },
  limits: {
    vmo: 200, mmo: 0.5,
    maxFlapSpeed: 120, maxGearSpeed: 150,
    maxBankAngle: 30, maxPitchUp: 20, maxPitchDown: -10,
    maxGForce: 3.0, minGForce: 0,
  },
};

// ── ICAO type → profile mapping ─────────────────────────────────────────────

const TYPE_TO_PROFILE: Record<string, FlightProfile> = {
  A320: "NARROWBODY", A20N: "NARROWBODY",
  A321: "NARROWBODY", A21N: "NARROWBODY", A21NLR: "NARROWBODY",
  B738: "NARROWBODY", B38M: "NARROWBODY",
  A359: "WIDEBODY",   B77W: "WIDEBODY",
  A30F: "FREIGHTER",  B738F: "FREIGHTER", B77F: "FREIGHTER",
  E175: "REGIONAL",   E190: "REGIONAL",   DH8D: "REGIONAL",
  "GA-Multiengine/Turboprop": "GA",
};

const PROFILE_MAP: Record<FlightProfile, FleetThresholds> = {
  NARROWBODY: NARROWBODY,
  WIDEBODY:   WIDEBODY,
  FREIGHTER:  FREIGHTER,
  REGIONAL:   REGIONAL,
  GA:         GA,
};

export function getThresholds(icaoType: string): FleetThresholds {
  const profile = TYPE_TO_PROFILE[icaoType] ?? "NARROWBODY";
  return PROFILE_MAP[profile];
}

export function getProfile(icaoType: string): FlightProfile {
  return TYPE_TO_PROFILE[icaoType] ?? "NARROWBODY";
}
