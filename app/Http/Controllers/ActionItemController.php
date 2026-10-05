<?php

namespace App\Http\Controllers;

use App\Models\ActionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActionItemController extends Controller
{
    public function complete(ActionItem $actionItem)
    {
        // Only the assigned user (student) can mark it as completed
        if (Auth::id() !== $actionItem->assigned_to) {
            abort(403);
        }

        $actionItem->update([
            'status' => 'completed'
        ]);

        return back()->with('success', 'Correction task marked as completed. Pending verification.');
    }

    public function verify(ActionItem $actionItem)
    {
        // Only Supervisor or Coordinator can verify
        if (!Auth::user()->hasAnyRole(['Supervisor', 'Program Coordinator', 'Director', 'Admin'])) {
            abort(403);
        }

        $actionItem->update([
            'status' => 'verified'
        ]);

        return back()->with('success', 'Correction task verified successfully.');
    }
}
