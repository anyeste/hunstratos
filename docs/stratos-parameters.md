# Stratos SDK — Available Simulator Parameters

> Source: [Stratos Additional Fields Reference](https://docs.skyvexsoftware.com/guide/additionalfields)  
> SDK: `@skyvexsoftware/stratos-sdk` — `SimDataSnapshot.data` (`FlightData`)

This document lists every simulator variable available to a Stratos plugin via `useSimData()` / `simulator:data` socket events. Parameters marked ✅ are currently monitored by the HUN FDM plugin. Parameters marked 🔵 are available and could be added in future versions.

---

## Always-On (Every Position Update)

These are always present on every tick — no configuration needed.

| SDK Field | Unit | Description | FDM Use |
|---|---|---|---|
| `latitude` | ° | Aircraft latitude WGS-84 | 🔵 Track logging |
| `longitude` | ° | Aircraft longitude WGS-84 | 🔵 Track logging |
| `heading` | ° | True heading | 🔵 Runway alignment check |
| `altitude` | ft | Altitude MSL | ✅ Phase gating (10,000 ft, 2,500 ft) |
| `groundSpeed` | kts | Ground speed | ✅ Taxi overspeed detection |
| `phase` | string | Current flight phase (UPPERCASE) | ✅ Phase gating for all exceedances |

---

## Position & Navigation

| SDK Field | Unit | FDM Use | Notes |
|---|---|---|---|
| `verticalSpeed` | fpm | ✅ VS exceedance detection | X-Plane: undamped `local_vy` |
| `indicatedAirspeed` | kts | ✅ Overspeed checks (IAS, flap, gear) | Rounded value |
| `trueAirspeed` | kts | 🔵 Crosswind component | |
| `altitudeAgl` | ft | 🔵 Low-altitude alert, CFIT detection | |
| `altitudeAglGear` | ft | 🔵 Precision AGL (gear reference) | MSFS only; falls back to `altitudeAgl` on X-Plane |

---

## Orientation / Attitude

| SDK Field | Unit | FDM Use | Notes |
|---|---|---|---|
| `pitch` / `pitchAngle` | ° | ✅ Pitch exceedance (up/down) | Positive = nose up |
| `bank` / `bankAngle` | ° | ✅ Bank angle exceedance | Positive = right wing down |

---

## Flight Controls

| SDK Field | Unit | FDM Use | Notes |
|---|---|---|---|
| `gearHandlePosition` / `gearControl` | boolean/0–1 | ✅ Gear overspeed, gear in cruise | Debounced ~3s in Additional Fields API |
| `flapsHandleIndex` / `flapsControl` | % | ✅ Flap overspeed detection | Detent index in SDK, % in Additional Fields |
| `onGround` | boolean | 🔵 Takeoff/touchdown detection | |

---

## Engine Data

| SDK Field | Unit | FDM Use | Notes |
|---|---|---|---|
| `engine1On` … `engine4On` | boolean | 🔵 Engine-out detection | Recommended over `engineNFiring` |
| `engine1N1` … `engine4N1` | % | 🔵 Engine-out, abnormal N1 | Most reliable on MSFS 2024 |
| `engine1N2` … `engine4N2` | % | 🔵 High-pressure spool | Can read wrong on some MSFS 2024 aircraft |
| `enginesCount` | number | 🔵 Know how many engines to monitor | |

---

## Fuel

| SDK Field | Unit | FDM Use | Notes |
|---|---|---|---|
| `fuelQuantity` | lbs | 🔵 Fuel remaining check | Easy to misread as kg — it's **lbs** |
| `fuelUsed` | lbs | 🔵 Fuel burn efficiency | 0 until block-off milestone |

---

## Weather

| SDK Field | Unit | FDM Use | Notes |
|---|---|---|---|
| `windDirection` | ° | 🔵 Crosswind component calc | True, from |
| `windSpeed` | kts | 🔵 Crosswind/tailwind check | |
| `pressureQnh` | inHg | 🔵 Altimeter setting verification | ~29.92 standard |
| `altimeterSettings` | inHg | 🔵 QNH set vs actual | X-Plane: duplicates `pressureQnh` |

---

## Simulator State

| SDK Field | Unit | FDM Use | Notes |
|---|---|---|---|
| `pauseFlag` | boolean | ✅ Implied — phase reports PAUSED | Suppress exceedances while paused |
| `slewMode` | boolean | 🔵 Invalidate flight if slew used | MSFS/FSX/P3D only |
| `simulationRate` | × | 🔵 Invalidate if sim rate > 1× | |
| `isXplane` | boolean | 🔵 Sim discriminator for field fallbacks | |

---

## Warning Flags (Native Sim)

| SDK Field | Unit | FDM Use | Notes |
|---|---|---|---|
| `stallWarning` | boolean | 🔵 Stall event logging | |
| `overspeedWarning` | boolean | 🔵 Cross-validate with IAS check | |
| `crashed` | boolean | 🔵 Auto-reject PIREP on crash | |

---

## G-Force

| SDK Field | Unit | FDM Use | Notes |
|---|---|---|---|
| `gForceTouchdown` / `gForce` | G | ✅ G-force exceedance (in-flight) | Live instantaneous G, not a held peak |
| `mach` | Mach | ✅ MMO overspeed check | |

---

## Lights (Native Sim)

| SDK Field | Unit | FDM Use | Notes |
|---|---|---|---|
| `lightsLanding` | boolean | ✅ Landing lights check below 10,000 ft | |

> **Note:** Additional light states (strobes, nav lights, beacon) are not currently exposed in the Stratos SDK's `FlightData` type. If needed, they can be tracked via raw SimConnect variables in a custom MSFS panel plugin.

---

## Landing Analysis Milestone Fields

Captured once at touchdown by the Stratos landing analyser. Available via `flight:event` (category `LANDING`) and the completion PIREP.

| Field | Unit | FDM Use |
|---|---|---|
| `landing_score` | 0–100 | ✅ Component 1 of Final Score |
| `landing_rate` | fpm | 🔵 Cross-validate with VS exceedances |
| `landing_g_force` | G | 🔵 Touchdown G logging |
| `landing_pitch` | ° | 🔵 Pitch at touchdown |
| `landing_roll` | ° | 🔵 Bank at touchdown |
| `landing_speed` | kts | 🔵 IAS at touchdown (Vref check) |
| `landing_flaps` | index | 🔵 Flap setting at landing |
| `landing_weight` | lbs | 🔵 MLW check |
| `arrival_threshold_distance` | ft | 🔵 Touchdown zone check |
| `arrival_centerline_deviation` | m | 🔵 Centerline deviation (MSFS only) |
| `arrival_heading_deviation` | ° | 🔵 Runway alignment |

---

## Takeoff Milestone Fields

| Field | Unit | FDM Use |
|---|---|---|
| `takeoff_weight` | lbs | 🔵 MTOW check |
| `takeoff_flaps` | index | 🔵 Correct flap setting |
| `departure_centerline_deviation` | m | 🔵 Takeoff centerline (MSFS only) |
| `taxi_fuel_used` | lbs | 🔵 Taxi fuel efficiency |

---

## Currently NOT Available in Stratos SDK

The following parameters are **not** exposed in the Stratos `FlightData` type and cannot be monitored by a plugin today:

| Parameter | Why Needed | Workaround |
|---|---|---|
| Spoiler/speedbrake position | Early descent spoiler use check | Not in SDK |
| Autopilot engagement state | Check if hand-flying required segments | Not in SDK |
| Thrust reverser deployment | Check reverser use above/below speed | Not in SDK |
| Individual light states (strobes, nav, beacon) | Full lights check procedure | Not in SDK |
| Transponder mode (Standby/ALT) | Mode C check | `transponder` squawk code available, mode not |
| Anti-ice system state | Ice protection procedure | Not in SDK |
| TCAS/ACAS resolution advisory | Safety net compliance | Not in SDK |

---

## FDM Parameter Coverage Summary

| Category | Parameters Available | Currently Monitored | Future Candidates |
|---|---|---|---|
| Speed | 3 (IAS, TAS, Mach) | 3 | — |
| Altitude / VS | 4 | 2 | AGL, gear-AGL |
| Attitude | 2 (pitch, bank) | 2 | — |
| G-Force | 1 | 1 | — |
| Flight Controls | 3 (gear, flaps, onGround) | 2 | onGround |
| Engines | 8 (4× N1, 4× on/off) | 0 | All |
| Fuel | 2 | 0 | Both |
| Weather | 4 | 0 | Wind for crosswind |
| Warnings | 3 | 0 | All |
| Lights | 1 (landing) | 1 | — |
| Sim State | 4 | 1 (implied) | slewMode, simRate |
| Landing Analysis | 11 | 1 (score) | 10 |
| Takeoff Analysis | 4 | 0 | All |
| **Total** | **~50** | **~13** | **~25** |
