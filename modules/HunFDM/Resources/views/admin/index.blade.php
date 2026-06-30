@extends('admin.app')
@section('title', 'FDM Reports')
@section('content')
<div class="container-fluid">
    <div class="row header">
        <div class="col-12">
            <h1>Flight Data Monitoring Reports</h1>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>PIREP</th>
                                <th>Aircraft</th>
                                <th>Profile</th>
                                <th>Landing Score</th>
                                <th>Deductions</th>
                                <th>Final Score</th>
                                <th>Exceedances</th>
                                <th>Critical</th>
                                <th>Recorded</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($reports as $r)
                            <tr>
                                <td>{{ $r->id }}</td>
                                <td>
                                    @if($r->pirep)
                                        <a href="{{ route('admin.pireps.show', $r->pirep_id) }}">
                                            {{ $r->pirep->ident ?? $r->pirep_id }}
                                        </a>
                                    @else
                                        &mdash;
                                    @endif
                                </td>
                                <td>{{ $r->aircraft_icao }}</td>
                                <td><span class="badge bg-secondary">{{ $r->flight_profile }}</span></td>
                                <td>{{ $r->landing_score }}</td>
                                <td class="{{ $r->fdm_deductions > 0 ? 'text-danger' : '' }}">
                                    -{{ $r->fdm_deductions }}
                                </td>
                                <td>
                                    <span class="badge
                                        {{ $r->final_score >= 80 ? 'bg-success' :
                                           ($r->final_score >= 60 ? 'bg-warning' :
                                           ($r->final_score >= 40 ? 'bg-orange' : 'bg-danger')) }}">
                                        {{ $r->final_score }}
                                    </span>
                                </td>
                                <td>{{ $r->exceedance_count }}</td>
                                <td>
                                    @if($r->critical_count > 0)
                                        <span class="badge bg-danger">{{ $r->critical_count }}</span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                                <td>{{ $r->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.hunfdm.show', $r->id) }}"
                                       class="btn btn-sm btn-info">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="11" class="text-center">No FDM reports yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $reports->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
