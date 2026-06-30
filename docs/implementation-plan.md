# HUN VA FDM — Implementation Plan

> **Repo:** anyeste/hunstratos | **Branch strategy:** feature branches → PR → main

Each step is a discrete unit of work with a clear deliverable and a defined test to confirm completion before moving to the next step.

---

## Step 1 — Database: Core Migrations + Seeder

**Branch:** `feat/db-migrations`

**Files to create:**
- `modules/HunFDM/Database/migrations/2026_06_30_000002_create_hun_fdm_rules_table.php`
- `modules/HunFDM/Database/migrations/2026_06_30_000003_create_hun_fdm_settings_table.php`
- `modules/HunFDM/Database/migrations/2026_06_30_000004_update_hun_fdm_reports_add_milestone_fields.php`
- `modules/HunFDM/Database/Seeders/HunFdmRulesSeeder.php` — all 45 default rules
- `modules/HunFDM/Database/Seeders/HunFdmSettingsSeeder.php` — all default settings + phase multipliers
- `modules/HunFDM/Database/Seeders/HunFdmDatabaseSeeder.php` — orchestrates both seeders

**✅ Done when:** `php artisan migrate` and `php artisan db:seed --class=HunFdmDatabaseSeeder` run without errors. All 45 rows exist in `hun_fdm_rules`. All settings exist in `hun_fdm_settings`.

---

## Step 2 — Models

**Branch:** `feat/models` (can merge into Step 1 branch)

**Files to create/update:**
- `modules/HunFDM/Models/HunFdmRule.php` — fillable, casts
- `modules/HunFDM/Models/HunFdmSetting.php` — key/value model with typed `get(key)` and `set(key, value)` helpers
- `modules/HunFDM/Models/HunFdmReport.php` — update fillable + casts with all new milestone fields

**✅ Done when:** `HunFdmRule::all()` returns 45 rows. `HunFdmSetting::get('landing_weight')` returns `0.40`.

---

## Step 3 — Config API Endpoint

**Branch:** `feat/config-api`

**Files to create/update:**
- `modules/HunFDM/Http/Controllers/Api/FdmConfigApiController.php`
  - `GET /api/hunfdm/config` — returns rules, settings, fleet profiles as JSON
- `modules/HunFDM/Http/Routes/api.php` — add new route

**Response shape:**
```json
{
  "rules": [ { "key": "overspeedBelow10k", "enabled": true, "deduction": 15, "critical": true, "cooldown_sec": 10, "once_per_flight": false } ],
  "settings": { "landing_weight": 0.40, "procedure_weight": 0.60, "phase_multipliers": { "FINAL": 2.0, ... } },
  "fleet_profiles": { "NARROWBODY": { "vmo": 350, "mmo": 0.82, ... } }
}
```

**✅ Done when:** `curl -H 'Authorization: Bearer <apikey>' https://your-phpvms/api/hunfdm/config` returns all 45 rules and settings.

---

## Step 4 — Plugin: TypeScript Types + Config Loader

**Branch:** `feat/plugin-config-loader`

**Files to create/update:**
- `packages/hun-fdm/src/types/fdm.ts` — expand with `FdmRule`, `FdmSettings`, `FdmConfig`, `FleetProfile`, all new milestone fields on `FdmReport`
- `packages/hun-fdm/src/config/loader.ts` — `fetchFdmConfig(client)` async function that hits `/api/hunfdm/config` and returns typed `FdmConfig`
- `packages/hun-fdm/src/config/thresholds.ts` — keep fleet hard limits; remove hardcoded deduction values (now come from API)

**✅ Done when:** `pnpm build` passes. Config loader function compiles with no type errors.

---

## Step 5 — Plugin: Full Background Module Rewrite

**Branch:** `feat/plugin-background-full`

**Files to update:**
- `packages/hun-fdm/src/background/index.ts` — full rewrite implementing all 45 rules

**Key implementation points:**
- Fetch config from API on `onStart`
- Track `repeatCount: Record<string, number>` for repeat multiplier logic
- Track all milestone fields: `takeoff_weight`, `landing_g_force`, `landing_rate`, `slew_used`, `sim_rate_exceeded`, `crashed`, `unlimited_fuel`, `total_pause_min`, runway deviations, touchdown position
- Implement per-category monitors:
  - **Speed:** S1–S6 from `indicatedAirspeed`, `mach`, `groundSpeed`, `flapsPosition`, `gearPosition`
  - **VS:** V1–V3 from `verticalSpeed`, `altitudeAgl`
  - **Attitude:** A1–A5 from `pitch`, `bank`, `gForce`
  - **Hard landing:** A6/A7 from `flight:event` LANDING milestone `gForceTouchdown`, `landingRate`
  - **Procedures:** P1–P4 from `lightsLanding`, `gearPosition`, `takeoffFlaps`, `landingFlaps`
  - **Engines:** E1–E3 from `engine1-4On`, `engine1-4N1`
  - **Fuel:** F1 from `unlimitedFuel` departure field; F2 from `fuelQuantity` at LANDED
  - **Weight:** W1/W2 from `takeoffWeight` / `landingWeight` milestone fields
  - **Integrity:** I1–I4 from `slewMode`, `simRate`, `crashed`, `totalPauseTime`
  - **Nav/ATC:** N1/N2 from `transponder`, `altimeterSetting`, `pressureQnh`
  - **Runway:** R1–R5 from departure/arrival milestone fields (MSFS only, guarded by `isXplane === false`)
  - **Warnings:** WN1/WN2 from `stallWarning`, `overspeedWarning`
- Implement weighted scoring formula on `pirep:filing`
- Include `scoring_snapshot` in report payload

**✅ Done when:** `pnpm build` passes. Test flight records all expected fields. Report JSON posted to phpVMS contains all milestone data.

---

## Step 6 — Plugin: Store Report API Update

**Branch:** `feat/store-report-expanded` (can combine with Step 5)

**Files to update:**
- `modules/HunFDM/Http/Controllers/Api/FdmApiController.php` — expand `store()` validation + model fill to accept all new milestone fields
- `modules/HunFDM/Models/HunFdmReport.php` — ensure all new columns in `$fillable`

**✅ Done when:** POST to `/api/hunfdm/report` with full payload stores all milestone fields correctly.

---

## Step 7 — Admin UI: Settings Page

**Branch:** `feat/admin-settings`

**Files to create:**
- `modules/HunFDM/Http/Controllers/Admin/AdminFdmSettingsController.php`
  - `GET /admin/hunfdm/settings` — show settings form
  - `POST /admin/hunfdm/settings` — save settings
- `modules/HunFDM/Resources/views/admin/settings.blade.php`
  - Tab 1: Scoring Weights (landing_weight, procedure_weight sliders)
  - Tab 2: Phase Multipliers (table with one input per phase)
  - Tab 3: Review Thresholds (flag_score_threshold, critical_flag_count, auto_reject_score)
  - Tab 4: Repeat Penalty (enabled toggle, multiplier table)
- `modules/HunFDM/Http/Routes/admin.php` — add new routes

**✅ Done when:** Admin can change `landing_weight` to 0.50, save, and see the new value persisted.

---

## Step 8 — Admin UI: Rule Manager

**Branch:** `feat/admin-rules`

**Files to create:**
- `modules/HunFDM/Http/Controllers/Admin/AdminFdmRulesController.php`
  - `GET /admin/hunfdm/rules` — list all rules grouped by category
  - `POST /admin/hunfdm/rules/{key}` — update single rule (enabled, deduction, critical, cooldown_sec, notes)
  - `POST /admin/hunfdm/rules/bulk` — bulk enable/disable by category
- `modules/HunFDM/Resources/views/admin/rules.blade.php`
  - Table grouped by category
  - Inline-editable: enabled toggle, deduction input, critical toggle, cooldown input, notes
  - Fleet hard limits section: read-only display with superadmin unlock
- `modules/HunFDM/Http/Routes/admin.php` — add routes

**✅ Done when:** Admin can disable `taxiOverspeed`, save, fetch `/api/hunfdm/config`, and confirm the rule has `enabled: false`.

---

## Step 9 — Admin UI: Report Detail Expansion

**Branch:** `feat/admin-report-detail`

**Files to update:**
- `modules/HunFDM/Resources/views/admin/show.blade.php` — add:
  - Milestone data card (takeoff/landing weights, runway deviations, touchdown coordinates)
  - Scoring snapshot viewer (collapsible JSON showing exact weights + rules used)
  - Phase breakdown: pie/bar of deductions per phase

**✅ Done when:** Report detail page shows touchdown lat/lon, landing weight, and the scoring snapshot section.

---

## Step 10 — Plugin: UI Panel Expansion

**Branch:** `feat/plugin-ui-expanded`

**Files to update:**
- `packages/hun-fdm/src/ui/index.tsx` — expand live panel:
  - Engine status row (N1 bars for each engine)
  - Fuel quantity display
  - Wind/crosswind display
  - Integrity flags (slew/sim rate/crash indicators)
  - Exceedance timeline (scrollable list with phase badges)
  - Post-flight: two-component score breakdown card (Landing + Procedure)

**✅ Done when:** `pnpm build` passes. Panel shows engine N1 values and post-flight score breakdown.

---

## Step 11 — GitHub Actions: Full CI Pipeline

**Branch:** `feat/ci`

**Files to update:**
- `.github/workflows/plugin-build.yml` — add TypeScript type check step (`pnpm tsc --noEmit`)
- `.github/workflows/php-lint.yml` — add PHPStan static analysis step
- `.github/workflows/release.yml` (new) — on tag push, build plugin and attach `dist/` as release asset

**✅ Done when:** A push to main triggers both workflows green. A `v0.1.0` tag push creates a GitHub Release.

---

## Step 12 — Integration Test & Stratos Config

**Branch:** `feat/integration-test`

**Actions (not code):**
1. In Stratos Additional Fields, enable all 112 fields on appropriate phases per `docs/stratos-parameters.md`
2. Run a test flight with deliberate violations (overspeed, slew, hard landing)
3. Confirm `hun_fdm_reports` row contains correct values
4. Confirm PIREP `score_percent` is updated
5. Confirm flagged PIREP appears in admin review queue
6. Document any discrepancies in `docs/known-issues.md`

**✅ Done when:** End-to-end flow works: Stratos plugin → POST to phpVMS → Report visible in admin panel → PIREP score updated.

---

## Milestone Summary

| Milestone | Steps | Deliverable |
|---|---|---|
| **M1: Database Ready** | 1–2 | All tables migrated, seeded with 45 rules |
| **M2: Backend API Complete** | 3, 6 | Plugin can fetch config + post reports |
| **M3: Plugin Full Coverage** | 4–5 | All 45 rules monitored, weighted formula |
| **M4: Admin UI Complete** | 7–9 | Admins can configure scoring live |
| **M5: Polish & Release** | 10–12 | Expanded UI, CI pipeline, integration tested |

---

## Branch Strategy

```
main
└── feat/db-migrations       (Steps 1–2)
└── feat/config-api          (Step 3)
└── feat/plugin-config-loader (Step 4)
└── feat/plugin-background-full (Step 5–6)
└── feat/admin-settings      (Step 7)
└── feat/admin-rules         (Step 8)
└── feat/admin-report-detail (Step 9)
└── feat/plugin-ui-expanded  (Step 10)
└── feat/ci                  (Step 11)
└── feat/integration-test    (Step 12)
```

Each branch merges to main via PR. No direct pushes to main after Step 1.
