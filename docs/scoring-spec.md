# HUN VA FDM — Full Scoring Specification v1.0

> **Project:** hunstratos | **VA:** Hungarian Airlines Virtual | **Status:** Implementation Blueprint

---

## Scoring Formula

The final score uses a two-component weighted model:

```
Final Score = clamp(
  (Landing Score × landing_weight) + (Procedure Score × procedure_weight),
  0, 100
)

Procedure Score = clamp(
  100 − Σ(deduction × phase_multiplier × repeat_multiplier),
  0, 100
)
```

**Default weights:** Landing 40% (`landing_weight = 0.40`), Procedure 60% (`procedure_weight = 0.60`). Both are admin-editable.

- **Landing Score (0–100):** Produced by the Stratos native landing analyser at touchdown. Factors: touchdown VS, G-force, pitch, bank, bounce, and centerline deviation. Read-only input — not modified, only weighted.
- **Procedure Score (0–100):** Calculated by the HUN FDM plugin from continuous telemetry throughout the entire flight. Starts at 100; exceedance deductions are subtracted.
- Each deduction is multiplied by a **phase multiplier** and a **repeat multiplier** before being subtracted.

---

## Phase Multipliers (Admin-Editable)

| Phase (Stratos) | Default Multiplier | Rationale |
|---|---|---|
| `BOARDING` / `PREFLIGHT` | ×0.0 | Not in flight — no penalties |
| `PUSHBACK` | ×0.3 | Ground, low risk |
| `TAXI` / `TAXIOUT` / `TAXIIN` | ×0.5 | Ground ops |
| `TAKEOFF` | ×1.5 | High-risk phase |
| `REJECTED_TAKEOFF` | ×1.5 | High-risk phase |
| `CLIMB` | ×1.0 | Baseline |
| `CRUISE` | ×0.8 | Lower criticality |
| `DESCENT` | ×1.0 | Baseline |
| `APPROACH` | ×1.5 | High-risk phase |
| `FINAL` | ×2.0 | Highest criticality |
| `GO_AROUND` | ×1.5 | High-risk |
| `LANDED` | ×0.5 | Post-touchdown rollout |
| `DEBOARDING` / `ARRIVED` | ×0.0 | Parked |

---

## Repeat Multiplier

If the same exceedance key fires more than once in a flight (after cooldown expiry), the repeat multiplier escalates:

| Occurrence | Multiplier |
|---|---|
| 1st | ×1.0 |
| 2nd | ×1.25 |
| 3rd | ×1.5 |
| 4+ | ×2.0 |

Admin-configurable on/off.

---

## Exceedance Rule Catalogue

All rules are stored in the `hun_fdm_rules` database table. Every column marked **Admin** is editable in the phpVMS admin panel. Structural hard limits (VMO, MMO, etc.) are visible but flagged read-only.

### Category 1 — Speed

| # | Key | Trigger Condition | Default Deduction | Critical | Cooldown | Admin-Editable |
|---|---|---|---|---|---|---|
| S1 | `overspeedBelow10k` | IAS > 250 kt AND alt < 10,000 ft | 15 | ✓ | 10s | Deduction, Critical |
| S2 | `overspeedVmo` | IAS > VMO (fleet) | 20 | ✓ | 10s | Deduction, Critical |
| S3 | `overspeedMmo` | Mach > MMO (fleet) | 20 | ✓ | 10s | Deduction, Critical |
| S4 | `flapsOverspeed` | IAS > max flap speed AND flaps > 0 | 15 | ✓ | 10s | Deduction, Critical |
| S5 | `gearOverspeed` | IAS > max gear speed AND gear down | 15 | ✓ | 10s | Deduction, Critical |
| S6 | `taxiOverspeed` | Ground speed > 30 kt during TAXI phases | 5 | ✗ | 15s | Deduction, Threshold |

### Category 2 — Vertical Speed

| # | Key | Trigger Condition | Default Deduction | Critical | Cooldown | Admin-Editable |
|---|---|---|---|---|---|---|
| V1 | `excessiveVsDescent` | VS < −2500 fpm AND alt < 10,000 ft (non-landing) | 10 | ✗ | 10s | Deduction, Threshold |
| V2 | `excessiveVsApproach` | VS < −1500 fpm AND alt < 2,500 ft AND phase APPROACH/FINAL | 15 | ✗ | 10s | Deduction, Threshold |
| V3 | `excessiveVsCruise` | VS < −6000 fpm AND alt > 10,000 ft | 10 | ✗ | 10s | Deduction, Threshold |

### Category 3 — Attitude / Structural

| # | Key | Trigger Condition | Default Deduction | Critical | Cooldown | Admin-Editable |
|---|---|---|---|---|---|---|
| A1 | `excessiveBankAngle` | Bank > max bank (fleet-specific) | 10 | ✗ | 10s | Deduction |
| A2 | `excessivePitchUp` | Pitch > max pitch up (fleet-specific) | 10 | ✗ | 10s | Deduction |
| A3 | `excessivePitchDown` | Pitch < max pitch down (fleet-specific) | 10 | ✗ | 10s | Deduction |
| A4 | `excessiveGForce` | G-force > max G (fleet-specific) | 15 | ✓ | 10s | Deduction |
| A5 | `negativeGForce` | G-force < 0g | 15 | ✓ | 10s | Deduction |
| A6 | `hardLanding` | Touchdown G > 2.1g OR landing rate < −800 fpm | 20 | ✓ | once | Deduction, Thresholds |
| A7 | `veryHardLanding` | Touchdown G > 2.8g OR landing rate < −1200 fpm | 35 | ✓ | once | Deduction, Thresholds |

### Category 4 — Procedures

| # | Key | Trigger Condition | Default Deduction | Critical | Cooldown | Admin-Editable |
|---|---|---|---|---|---|---|
| P1 | `lightsOffBelow10k` | Landing lights OFF AND alt < 10,000 ft (airborne, non-PREFLIGHT) | 5 | ✗ | 30s | Deduction |
| P2 | `gearExtendedCruise` | Gear down AND alt > 10,000 ft AND phase CRUISE | 10 | ✗ | 10s | Deduction |
| P3 | `wrongTakeoffFlaps` | Takeoff flap index = 0 (no flaps) at liftoff | 10 | ✓ | once | Deduction |
| P4 | `wrongLandingFlaps` | Landing flap index < full landing setting at touchdown | 8 | ✗ | once | Deduction |

### Category 5 — Engine

| # | Key | Trigger Condition | Default Deduction | Critical | Cooldown | Admin-Editable |
|---|---|---|---|---|---|---|
| E1 | `engineOutAirborne` | Any engine On = false AND airborne AND not TAKEOFF phase | 25 | ✓ | 30s | Deduction |
| E2 | `engineShutdownGround` | All engines On = false during TAXI phases | 0 | ✗ | 60s | Event log only |
| E3 | `abnormalN1` | Any engine N1 < 15% AND engine On = true AND phase CLIMB/CRUISE | 10 | ✓ | 30s | Deduction, Threshold |

### Category 6 — Fuel

| # | Key | Trigger Condition | Default Deduction | Critical | Cooldown | Admin-Editable |
|---|---|---|---|---|---|---|
| F1 | `unlimitedFuel` | Unlimited Fuel = true (MSFS only) | 100 | ✓ | once | Deduction |
| F2 | `lowFuelLanding` | Fuel Quantity < minimum threshold at landing | 10 | ✓ | once | Deduction, Threshold |

### Category 7 — Weight (Milestone)

| # | Key | Trigger Condition | Default Deduction | Critical | Cooldown | Admin-Editable |
|---|---|---|---|---|---|---|
| W1 | `mtowExceedance` | Takeoff Weight > MTOW (fleet) | 20 | ✓ | once | Deduction |
| W2 | `mlwExceedance` | Landing Weight > MLW (fleet) | 20 | ✓ | once | Deduction |

### Category 8 — Simulator Integrity

| # | Key | Trigger Condition | Default Deduction | Critical | Cooldown | Admin-Editable |
|---|---|---|---|---|---|---|
| I1 | `slewUsed` | Slew Mode = true at any point during flight | 100 | ✓ | once | Deduction |
| I2 | `simRateAboveNormal` | Sim Rate > 1× during airborne phases | 50 | ✓ | once | Deduction |
| I3 | `crashed` | Crashed = true | 100 | ✓ | once | Deduction |
| I4 | `pausedExcessively` | Total Pause Time > threshold (default: 10 min) | 10 | ✗ | once | Deduction, Threshold |

### Category 9 — Navigation & ATC

| # | Key | Trigger Condition | Default Deduction | Critical | Cooldown | Admin-Editable |
|---|---|---|---|---|---|---|
| N1 | `transponderOff` | Transponder squawk = 0 AND airborne | 5 | ✗ | 30s | Deduction |
| N2 | `altimeterNotSet` | Altimeter Setting deviates > 0.10 inHg from QNH AND below transition alt | 5 | ✗ | 60s | Deduction, Threshold |

### Category 10 — Runway Alignment (MSFS only)

| # | Key | Trigger Condition | Default Deduction | Critical | Cooldown | Admin-Editable |
|---|---|---|---|---|---|---|
| R1 | `departureHeadingDev` | Departure heading deviation > 10° at liftoff | 5 | ✗ | once | Deduction, Threshold |
| R2 | `departureCenterlineDev` | Departure centerline deviation > 15m at liftoff | 5 | ✗ | once | Deduction, Threshold |
| R3 | `arrivalHeadingDev` | Arrival heading deviation > 10° at touchdown | 5 | ✗ | once | Deduction, Threshold |
| R4 | `arrivalCenterlineDev` | Arrival centerline deviation > 15m at touchdown | 5 | ✗ | once | Deduction, Threshold |
| R5 | `longLanding` | Arrival threshold distance > 1000m | 10 | ✗ | once | Deduction, Threshold |

### Category 11 — Native Sim Warnings

| # | Key | Trigger Condition | Default Deduction | Critical | Cooldown | Admin-Editable |
|---|---|---|---|---|---|---|
| WN1 | `stallWarning` | Stall Warning = true AND airborne | 10 | ✗ | 15s | Deduction |
| WN2 | `overspeedWarningNative` | Overspeed Warning = true | 5 | ✗ | 10s | Deduction |

---

## Score Grades

| Score | Grade | Colour | PIREP Action |
|---|---|---|---|
| 90–100 | Excellent | 🟢 Green | Auto-accept |
| 75–89 | Good | 🟡 Yellow | Auto-accept |
| 60–74 | Acceptable | 🟠 Orange | Auto-accept |
| 40–59 | Poor | 🔴 Red | Flag for review |
| 0–39 | Unacceptable | 🔴 Dark Red | Flag for review |
| Any score with ≥1 critical exceedance | — | — | Always flag for review |

---

## Fleet Profiles — Hard Limits

| Profile | VMO (kt) | MMO | Max Flap (kt) | Max Gear (kt) | Max Bank (°) | Max Pitch Up (°) | Max Pitch Down (°) | Max G | Min G |
|---|---|---|---|---|---|---|---|---|---|
| NARROWBODY | 350 | 0.82 | 230 | 270 | 35 | 20 | −10 | 2.5 | 0 |
| WIDEBODY | 340 | 0.89 | 250 | 270 | 33 | 20 | −10 | 2.5 | 0 |
| FREIGHTER | 340 | 0.82 | 230 | 270 | 35 | 20 | −10 | 2.0 | 0 |
| REGIONAL | 320 | 0.82 | 200 | 250 | 30 | 18 | −8 | 2.5 | 0 |
| GA | 200 | 0.50 | 120 | 150 | 30 | 20 | −10 | 3.0 | 0 |

---

## Database Schema

### `hun_fdm_rules`

| Column | Type | Description |
|---|---|---|
| `id` | PK | |
| `key` | varchar(60) | Rule key (e.g. `overspeedBelow10k`) |
| `category` | varchar(30) | Speed / Vertical / Attitude / etc. |
| `label` | varchar(120) | Human-readable label |
| `enabled` | boolean | Toggle rule on/off |
| `deduction` | tinyint | Base deduction value (0–100) |
| `critical` | boolean | Flags PIREP for admin review |
| `cooldown_sec` | smallint | Seconds before rule can re-fire |
| `once_per_flight` | boolean | Rule fires at most once per flight |
| `notes` | text | Admin notes |

### `hun_fdm_settings`

| Key | Default | Description |
|---|---|---|
| `landing_weight` | 0.40 | Landing score weight |
| `procedure_weight` | 0.60 | Procedure score weight |
| `repeat_multiplier_enabled` | true | Enable escalating repeat penalties |
| `phase_multipliers` | JSON | Per-phase multiplier map |
| `flag_score_threshold` | 40 | Auto-flag PIREPs below this score |
| `critical_flag_count` | 1 | Min critical exceedances to force flag |
| `auto_reject_score` | 0 | Auto-reject below this (0 = disabled) |
| `pause_time_threshold_min` | 10 | Minutes of pause before I4 fires |
| `low_fuel_threshold_kg` | 500 | F2 fuel quantity threshold |

### `hun_fdm_reports` expanded columns

| Column | Type | Description |
|---|---|---|
| `takeoff_weight` | int | kg |
| `landing_weight` | int | kg |
| `landing_rate` | smallint | fpm |
| `landing_g_force` | decimal(4,2) | G at touchdown |
| `landing_pitch` | decimal(4,1) | Pitch at touchdown |
| `landing_bank` | decimal(4,1) | Bank at touchdown |
| `landing_speed_ias` | smallint | kts IAS at touchdown |
| `landing_flaps` | tinyint | Flap index at touchdown |
| `touchdown_lat` | decimal(9,6) | Touchdown latitude |
| `touchdown_lon` | decimal(9,6) | Touchdown longitude |
| `arrival_threshold_dist` | smallint | m |
| `arrival_centerline_dev` | decimal(5,1) | m |
| `arrival_heading_dev` | decimal(4,1) | ° |
| `departure_centerline_dev` | decimal(5,1) | m |
| `departure_heading_dev` | decimal(4,1) | ° |
| `takeoff_flaps` | tinyint | Flap index at takeoff |
| `slew_used` | boolean | I1 flag |
| `sim_rate_exceeded` | boolean | I2 flag |
| `crashed` | boolean | I3 flag |
| `unlimited_fuel` | boolean | F1 flag |
| `total_pause_min` | smallint | Total pause time |
| `scoring_snapshot` | json | Exact weights/rules used at time of scoring |

---

## Admin UI Structure

### `/admin/hunfdm` — Reports List
- Paginated table with filters (score range, flagged, aircraft type, date)

### `/admin/hunfdm/{id}` — Report Detail
- Score breakdown card, exceedance log, milestone data, scoring snapshot viewer

### `/admin/hunfdm/settings` — General Settings
- Tab 1: Scoring Weights
- Tab 2: Phase Multipliers
- Tab 3: Review Thresholds
- Tab 4: Repeat Penalty settings

### `/admin/hunfdm/rules` — Rule Manager
- Inline-editable table per rule, grouped by category
- Hard-limit values read-only with superadmin unlock toggle

---

## Plugin Config Fetch

On `onStart`, the plugin calls:

```
GET /api/hunfdm/config
→ { rules: [...], settings: {...}, fleetProfiles: {...} }
```

The ruleset is held in memory for the flight. Settings changes take effect on the **next** flight.
