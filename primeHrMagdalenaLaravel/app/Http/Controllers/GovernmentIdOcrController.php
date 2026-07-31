<?php

namespace App\Http\Controllers;

use App\Services\GovernmentIdOcrService;
use Illuminate\Http\Request;

class GovernmentIdOcrController extends Controller
{
    public function __construct(private GovernmentIdOcrService $service)
    {
    }

    public function extract(Request $request)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'id_type' => ['required', 'in:gsis,philhealth,pagibig,tin,license'],
        ]);

        $result = $this->service->extract($request->file('file'), $validated['id_type']);

        return response()->json($result);
    }
}
