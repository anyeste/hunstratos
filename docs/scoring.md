# HUN VA FDM — Scoring Design

> **Version:** 0.1 | **Status:** Draft

## Overview

Every completed flight receives a **Final Score (0–100)** composed of two components:

```
Final Score = clamp(
  (Landing Score × landing_weight)
  + (Procedure Score × procedure_weight),
  0, 100
)

Procedure Score = clamp(100 − Σ(deduction × phase_multiplier), 0, 100)
```

Default weights: **Landing 40% / Procedure 60%**. Admins can adjust weights in the phpVMS admin panel (`/admin/hunfdm`).

---

## Component 1 — Landing Score (from Stratos)

Stratos calculates a native 0–100 landing score based on its own landing analyser. This score is received via the `flight:event` socket event (`category: "LANDING"`, `data.score`). It measures:

- Touchdown vertical speed (primary factor)
- G-force at touchdown
- Bank/roll angle at touchdown
- Pitch attitude at touchdown
- Bounce behaviour
- Centerline deviation (MSFS only)

This component is **read-only** — we do not modify it, we weight it.

---

## Component 2 — Procedure Score

Calculated entirely by the HUN FDM plugin from continuous telemetry monitoring throughout the flight. Starts at 100 and has exceedance deductions applied.

### Exceedance Deductions

Each exceedance type has:
- A **base deduction** (configurable in admin panel)
- A **phase multiplier** (approach/landing exceedances are penalised harder)
- A **critical flag** (critical exceedances flag the PIREP for admin review)
- A **cooldown** of 10 seconds (same event cannot fire again within 10s)

### Phase Multipliers (default)

| Phase | Multiplier | Rationale |
|---|---|---|
| PREFLIGHT / BOARDING | ×0 | Not in flight |
| PUSH_BACK / TAXI | ×0.5 | Ground ops |
| TAKE_OFF | ×1.5 | High-risk phase |
| CLIMB | ×1.0 | Baseline |
| CRUISE | ×0.8 | Less critical |
| DESCENT | ×1.0 | Baseline |
| APPROACH / FINAL | ×1.5 | High-risk phase |
| LANDED / TAXI_TO_GATE | ×0.5 | Post-flight |

---

## Exceedance Catalogue

### Speed Exceedances

| Key | Condition | Base Deduction | Critical | Notes |
|---|---|---|---|---|
| `overspeedBelow10k` | IAS > 250 kt below 10,000 ft | −15 | ✓ | ICAO speed limit |
| `overspeedVmo` | IAS > VMO (fleet-specific) | −20 | ✓ | Structural limit |
| `overspeedMmo` | Mach > MMO (fleet-specific) | −20 | ✓ | Structural limit |
| `flapsOverspeed` | IAS > max flap speed with flaps extended | −15 | ✓ | Structural limit |
| `gearOverspeed` | IAS > max gear speed with gear extended | −15 | ✓ | Structural limit |

### Vertical Speed Exceedances

| Key | Condition | Base Deduction | Critical | Notes |
|---|---|---|---|---|
| `excessiveVsDescent` | VS < −2500 fpm below 10,000 ft | −10 | ✗ | |
| `excessiveVsApproach` | VS < −1500 fpm below 2,500 ft on approach | −15 | ✗ | VASI/PAPI indication |
| `excessiveVsCruise` | VS < −6000 fpm above 10,000 ft | −10 | ✗ | Emergency descent pattern |

### Attitude / Structural Exceedances

| Key | Condition | Base Deduction | Critical | Notes |
|---|---|---|---|---|
| `excessiveBankAngle` | Bank > limit (fleet-specific) | −10 | ✗ | NARROWBODY: 35°, WIDEBODY: 33° |
| `excessivePitchUp` | Pitch > limit | −10 | ✗ | NARROWBODY/WIDEBODY: 20° |
| `excessivePitchDown` | Pitch < limit | −10 | ✗ | NARROWBODY/WIDEBODY: −10° |
| `excessiveGForce` | G > max (fleet-specific) | −15 | ✓ | NARROWBODY: 2.5g, FREIGHTER: 2.0g |
| `negativeGForce` | G < 0g | −15 | ✓ | Bunt manoeuvre |

### Procedure Exceedances

| Key | Condition | Base Deduction | Critical | Notes |
|---|---|---|---|---|
| `lightsOffBelow10k` | Landing lights off below 10,000 ft | −5 | ✗ | ICAO SERA / HUN VA SOP |
| `gearExtendedCruise` | Gear down above 10,000 ft in CRUISE phase | −10 | ✗ | |
| `taxiOverspeed` | Ground speed > 30 kt during TAXI phases | −5 | ✗ | |

---

## Fleet Profiles

Exceedance limits vary by fleet profile. The profile is resolved from the aircraft ICAO type at flight start.

| Profile | Aircraft Types | VMO | MMO | Max Bank | Max G |
|---|---|---|---|---|---|
| NARROWBODY | A320, A20N, A321, A21N, A21NLR, B738, B38M | 350 kt | M0.82 | 35° | 2.5g |
| WIDEBODY | A359, B77W | 340 kt | M0.89 | 33° | 2.5g |
| FREIGHTER | A30F, B738F, B77F | 340 kt | M0.82 | 35° | 2.0g |
| REGIONAL | E175, E190, DH8D | 320 kt | M0.82 | 30° | 2.5g |
| GA | GA-Multiengine/Turboprop | 200 kt | M0.50 | 30° | 3.0g |

> **Unknown types** fall back to NARROWBODY profile.

---

## Score Grades

| Score | Grade | Colour | Meaning |
|---|---|---|---|
| 90–100 | Excellent | 🟢 Green | No or minor violations |
| 75–89 | Good | 🟡 Yellow | Minor violations |
| 60–74 | Acceptable | 🟠 Orange | Notable violations |
| 40–59 | Poor | 🔴 Red | Significant violations |
| 0–39 | Unacceptable | 🔴 Dark Red | Severe violations, PIREP flagged |

PIREPs with **any critical exceedance** are automatically flagged for admin review regardless of final score.

---

## Admin Configuration (phpVMS Panel)

All values below are stored in the `hun_fdm_config` table (future) and read by the plugin at flight start via `GET /api/hunfdm/config`.

| Setting | Default | Description |
|---|---|---|
| `landing_weight` | 0.40 | Weight of Stratos landing score in final score |
| `procedure_weight` | 0.60 | Weight of procedure score in final score |
| `phase_multipliers` | see table above | Per-phase deduction multipliers |
| Per-exceedance `deduction` | see catalogue | Base deduction value, 0–50 |
| Per-exceedance `enabled` | true | Toggle individual checks on/off |
| `critical_flag_threshold` | 1 | Min critical exceedances to flag PIREP |
| `auto_reject_score` | 0 | Min score to auto-reject PIREP (0 = disabled) |

> **Note:** Fleet speed/attitude limits (VMO, MMO, max bank, max G) are **not** admin-editable. They are hard limits defined in `thresholds.ts` based on aircraft certification data. Only deduction values and weights are configurable.

---

## Data Flow Summary

```
Stratos Simulator
       │
       ├─ simulator:data (10–20 Hz) ──► background/index.ts ──► exceedance detection
       │                                      │
       ├─ flight:event (LANDING)  ───────────►│ capture landing_score
       │                                      │
       └─ pirep:filing  ──────────────────────►│
                                               │
                              build FdmReport  │
                              finalScore = f(landing_score, exceedances)
                                               │
                        ┌──────────────────────┤
                        │                      │
                        ▼                      ▼
              POST /api/hunfdm/report   PATCH /api/pireps/{id}
              (store FDM data)          (set score_percent)
                        │
                        ▼
              phpVMS hun_fdm_reports table
```
