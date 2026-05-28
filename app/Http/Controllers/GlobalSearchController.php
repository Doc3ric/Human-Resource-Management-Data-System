<?php

namespace App\Http\Controllers;

use App\Models\PlantillaRecord;
use App\Models\CasualEmployee;
use App\Models\JobOrder;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q');
        
        if (!$query) {
            return view('search.results', ['results' => collect()]);
        }

        $plantilla = PlantillaRecord::where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('item', 'like', "%{$query}%");
            })
            // Exclude synced copies from JO/Casual imports — they show via their own table
            ->whereNotIn('employment_status', ['JO', 'Casual'])
            ->get()->map(fn($item) => [
                'type' => 'Plantilla',
                'name' => "{$item->first_name} " . ($item->middle_name ?? $item->middle_initial) . " {$item->last_name} {$item->name_extension}",
                'details' => $item->position_title ?? 'N/A',
                'url' => route('employees.profile', ['type' => 'plantilla', 'id' => $item->id]),
                'avatar' => substr($item->first_name, 0, 1) . substr($item->last_name, 0, 1),
                'profile_picture' => $item->profile_picture
            ]);

        $casual = CasualEmployee::where('first_name', 'like', "%{$query}%")
            ->orWhere('last_name', 'like', "%{$query}%")
            ->get()->map(fn($item) => [
                'type' => 'Casual',
                'name' => "{$item->first_name} " . ($item->middle_name ?? $item->middle_initial) . " {$item->last_name} {$item->name_extension}",
                'details' => $item->position_title ?? 'N/A',
                'url' => route('employees.profile', ['type' => 'casual', 'id' => $item->id]),
                'avatar' => substr($item->first_name, 0, 1) . substr($item->last_name, 0, 1),
                'profile_picture' => $item->profile_picture
            ]);

        $jobOrders = JobOrder::where('first_name', 'like', "%{$query}%")
            ->orWhere('last_name', 'like', "%{$query}%")
            ->get()->map(fn($item) => [
                'type' => 'Job Order',
                'name' => "{$item->first_name} " . ($item->middle_name ?? $item->middle_initial) . " {$item->last_name} {$item->name_extension}",
                'details' => $item->nature_of_work ?? 'N/A',
                'url' => route('employees.profile', ['type' => 'job_orders', 'id' => $item->id]),
                'avatar' => substr($item->first_name, 0, 1) . substr($item->last_name, 0, 1),
                'profile_picture' => $item->profile_picture
            ]);

        $results = $plantilla->concat($casual)->concat($jobOrders);

        return view('search.results', compact('results', 'query'));
    }
}
