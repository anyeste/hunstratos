<?php

namespace Modules\HunFDM\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pirep;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\HunFDM\Models\HunFdmReport;

class FdmApiController extends Controller
{
    /**
     * POST /api/hunfdm/report
     * Receives the FDM report from the Stratos plugin and stores it.
     * Also updates the PIREP score_percent with the FDM final score.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pirepId'           => 'nullable',
            'flightId'          => 'nullable',
            'aircraftIcao'      => 'nullable|string|max:20',
            'flightProfile'     => 'nullable|string|max:20',
            'landingScore'      => 'required|integer|min:0|max:100',
            'fdmDeductions'     => 'required|integer|min:0',
            'finalScore'        => 'required|integer|min:0|max:100',
            'exceedances'       => 'nullable|array',
            'exceedanceCount'   => 'required|integer|min:0',
            'criticalCount'     => 'required|integer|min:0',
            'flightDurationMin' => 'nullable|integer|min:0',
            'recordedAt'        => 'nullable|string',
        ]);

        $pirepId = $validated['pirepId'] ?? null;
        $pirep   = $pirepId ? Pirep::find($pirepId) : null;

        $report = HunFdmReport::create([
            'pirep_id'            => $pirep?->id,
            'flight_id'           => $validated['flightId'] ?? null,
            'aircraft_icao'       => $validated['aircraftIcao'] ?? null,
            'flight_profile'      => $validated['flightProfile'] ?? null,
            'landing_score'       => $validated['landingScore'],
            'fdm_deductions'      => $validated['fdmDeductions'],
            'final_score'         => $validated['finalScore'],
            'exceedance_count'    => $validated['exceedanceCount'],
            'critical_count'      => $validated['criticalCount'],
            'flight_duration_min' => $validated['flightDurationMin'] ?? 0,
            'exceedances'         => $validated['exceedances'] ?? [],
            'recorded_at'         => $validated['recordedAt'] ?? now()->toISOString(),
        ]);

        if ($pirep) {
            $pirep->score_percent = $validated['finalScore'];
            $pirep->save();
            Log::info('[HunFDM] PIREP ' . $pirep->id . ' score updated to ' . $validated['finalScore']);
        }

        return response()->json([
            'status'      => 'ok',
            'fdm_id'      => $report->id,
            'final_score' => $report->final_score,
        ], 201);
    }

    /**
     * GET /api/hunfdm/report/{pirepId}
     * Returns the FDM report for a given PIREP.
     */
    public function show(int $pirepId): JsonResponse
    {
        $report = HunFdmReport::where('pirep_id', $pirepId)->first();

        if (! $report) {
            return response()->json(['error' => 'FDM report not found'], 404);
        }

        return response()->json($report);
    }
}
