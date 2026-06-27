<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\HandleChangeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Admin → User Requests. Review creator-submitted custom name / URL change
 * requests (the ones not auto-applied from a linked identity). Approving
 * mutates the live handle + display name and aliases the old handle.
 */
class UserRequestController extends Controller
{
    public function index()
    {
        return view('studio.admin.user-requests', [
            'requests' => HandleChangeService::pending(),
        ]);
    }

    public function decide(Request $request)
    {
        $data = $request->validate([
            'id'     => ['required', 'integer'],
            'action' => ['required', 'in:approve,reject'],
        ]);

        $reviewer = (string) (Auth::user()->email ?? ('user:' . Auth::id()));
        $result = HandleChangeService::decide((int) $data['id'], $data['action'] === 'approve', $reviewer);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($result, ($result['ok'] ?? false) ? 200 : 422);
        }

        return ($result['ok'] ?? false)
            ? back()->with('success', 'Request ' . ($data['action'] === 'approve' ? 'approved' : 'rejected') . '.')
            : back()->with('error', $result['error'] ?? 'Could not process that request.');
    }
}
