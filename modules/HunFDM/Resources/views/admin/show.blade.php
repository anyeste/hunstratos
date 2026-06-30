@extends('admin.app')
@section('title', 'FDM Report Detail')
@section('content')
<div class="container-fluid">
    <div class="row header">
        <div class="col-12">
            <h1>FDM Report #{{ $report->id }}</h1>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Score Breakdown</div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr><th>Aircraft</th><td>{{ $report->aircraft_icao }}</td></tr>
                        <tr><th>Profile</th><td>{{ $report->flight_profile }}</td></tr>
                        <tr><th>Duration</th><td>{{ $report->flight_duration_min }} min</td></tr>
                        <tr><th>Landing Score</th><td>{{ $report->landing_score }}</td></tr>
                        <tr><th>FDM Deductions</th><td class="text-danger">-{{ $report->fdm_deductions }}</td></tr>
                        <tr>
                            <th>Final Score</th>
                            <td>
                                <strong class="
                                    {{ $report->final_score >= 80 ? 'text-success' :
                                       ($report->final_score >= 60 ? 'text-warning' : 'text-danger') }}">
                                    {{ $report->final_score }}
                                </strong>
                            </td>
                        </tr>
                        <tr><th>Exceedances</th><td>{{ $report->exceedance_count }}</td></tr>
                        <tr><th>Critical</th><td>{{ $report->critical_count }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Exceedance Log</div>
                <div class="card-body table-responsive p-0">
                    @if(count($report->exceedances ?? []) > 0)
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Phase</th>
                                <th>Event</th>
                                <th>Value</th>
                                <th>Deduction</th>
                                <th>Critical</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($report->exceedances as $e)
                            <tr class="{{ $e['critical'] ? 'table-danger' : '' }}">
                                <td class="font-mono text-xs">
                                    {{ date('H:i:s', intval($e['timestamp'] / 1000)) }}
                                </td>
                                <td><span class="badge bg-secondary">{{ $e['phase'] }}</span></td>
                                <td>{{ $e['label'] }}</td>
                                <td class="font-mono">{{ round($e['value'], 2) }}</td>
                                <td class="text-danger">-{{ $e['deduction'] }}</td>
                                <td>
                                    @if($e['critical'])
                                        <span class="badge bg-danger">YES</span>
                                    @else
                                        <span class="text-muted">&mdash;</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    @else
                        <p class="p-3 text-muted">No exceedances recorded.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
