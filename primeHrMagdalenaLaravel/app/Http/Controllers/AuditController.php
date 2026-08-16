<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use OwenIt\Auditing\Models\Audit;

class AuditController extends Controller
{
    public function index()
    {
        $audits = Audit::latest()->paginate(15);

        $auditUsers = Audit::distinct()
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->map(fn ($id) => User::find($id))
            ->filter()
            ->sortBy('username')
            ->values();

        return view('admin.audit.index', compact(
            'audits',
            'auditUsers',
        ));
    }

    public function create()
    {
    }

    public function store(Request $request)
    {
    }

    public function show($id)
    {
    }

    public function edit($id)
    {
    }

    public function update(Request $request, $id)
    {
    }

    public function destroy($id)
    {
    }
}
