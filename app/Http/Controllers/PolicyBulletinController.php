<?php

namespace App\Http\Controllers;

use App\Models\PolicyBulletin;
use Illuminate\Http\Request;

class PolicyBulletinController extends Controller
{
    public function index()
    {
        $bulletins = PolicyBulletin::orderBy('date_issued', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        $metrics = [
            'total' => PolicyBulletin::count(),
            'unread' => PolicyBulletin::where('is_acknowledged', false)->count(),
            'high_applicability' => PolicyBulletin::where('applicability_score', '>=', 10)->count(),
        ];

        return view('policy-bulletins.index', compact('bulletins', 'metrics'));
    }

    public function acknowledge(PolicyBulletin $bulletin)
    {
        $bulletin->update(['is_acknowledged' => true]);
        
        // Log action
        activity()
            ->performedOn($bulletin)
            ->causedBy(auth()->user())
            ->log('Acknowledged policy bulletin');

        return back()->with('success', 'Policy bulletin marked as acknowledged.');
    }
}
