<?php

namespace App\Http\Controllers;

use App\Models\UserTablePreference;
use Illuminate\Http\Request;

/**
 * Enhancement Spec Sec. 1 — column_visibility persisted per user, per tab.
 * column_widths (user-resizable columns, applied globally) shares the same
 * per-user/per-tab row as a sibling field.
 */
class TablePreferenceController extends Controller
{
    public function show(Request $request, string $tabKey)
    {
        $pref = UserTablePreference::where('user_id', $request->user()->id)
            ->where('tab_key', $tabKey)
            ->first();

        return response()->json([
            'hidden' => $pref->hidden_columns ?? [],
            'widths' => $pref->column_widths ?? [],
        ]);
    }

    public function store(Request $request, string $tabKey)
    {
        $validated = $request->validate([
            'hidden' => 'array',
            'hidden.*' => 'string',
            'widths' => 'array',
            'widths.*' => 'integer|min:20|max:2000',
        ]);

        // Only touch the field(s) actually sent — a resize-only request must
        // not clobber a previously-saved visibility set, and vice versa.
        $updates = [];
        if ($request->has('hidden')) {
            $updates['hidden_columns'] = $validated['hidden'] ?? [];
        }
        if ($request->has('widths')) {
            $updates['column_widths'] = $validated['widths'] ?? [];
        }
        if ($updates) {
            UserTablePreference::updateOrCreate(
                ['user_id' => $request->user()->id, 'tab_key' => $tabKey],
                $updates
            );
        }

        return response()->json(['ok' => true]);
    }
}
