export interface FdmExceedance {
  key: string;
  label: string;
  deduction: number;
  critical: boolean;
  timestamp: number;
  value: number;
  phase: string;
}

export interface FdmReport {
  pirepId?: string | number;
  flightId?: string | number;
  aircraftIcao: string;
  flightProfile: string;
  landingScore: number;
  fdmDeductions: number;
  finalScore: number;
  exceedances: FdmExceedance[];
  exceedanceCount: number;
  criticalCount: number;
  flightDurationMin: number;
  recordedAt: string;
}
