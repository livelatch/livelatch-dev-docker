<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ImpersonationService;
use Illuminate\Http\Request;

/**
 * Admin → Impersonation Mitigation (a LatchID sub-product). View the protected
 * name directory, attempt signals, similar live accounts, and bulk-add names.
 */
class ImpersonationController extends Controller
{
    public function index()
    {
        $directory = ImpersonationService::directory();
        $similar = ImpersonationService::similarAccountCounts(array_column($directory, 'handle'));

        return view('studio.admin.impersonation', [
            'directory' => $directory,
            'similar'   => $similar,
        ]);
    }

    public function bulkAdd(Request $request)
    {
        $data = $request->validate([
            'names' => ['required', 'string', 'max:100000'],
        ]);

        $count = ImpersonationService::bulkAdd($data['names']);

        return back()->with('success', "Submitted {$count} name(s) to the directory.");
    }
}
