<?php

namespace Modules\HunFDM\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Modules\HunFDM\Models\HunFdmReport;

class AdminFdmController extends Controller
{
    public function index()
    {
        $reports = HunFdmReport::with('pirep')
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('hunfdm::admin.index', compact('reports'));
    }

    public function show(int $id)
    {
        $report = HunFdmReport::with('pirep.user')->findOrFail($id);
        return view('hunfdm::admin.show', compact('report'));
    }
}
