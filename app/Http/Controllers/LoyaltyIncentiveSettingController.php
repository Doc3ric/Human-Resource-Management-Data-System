<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LoyaltyIncentiveSettingController extends Controller
{
    private const DISK   = 'public';
    private const FOLDER = 'loyalty-backgrounds';
    private const META   = 'loyalty-backgrounds/meta.json'; // stores {active, backgrounds:[{id,name,file}]}

    // ── Read meta ────────────────────────────────────────────────────────────

    private function getMeta(): array
    {
        if (Storage::disk(self::DISK)->exists(self::META)) {
            return json_decode(Storage::disk(self::DISK)->get(self::META), true) ?? [];
        }
        return ['active' => null, 'backgrounds' => []];
    }

    private function saveMeta(array $meta): void
    {
        Storage::disk(self::DISK)->put(self::META, json_encode($meta, JSON_PRETTY_PRINT));
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function index()
    {
        $meta = $this->getMeta();
        return view('step-increment.loyalty-bg-settings', [
            'backgrounds' => $meta['backgrounds'] ?? [],
            'active'      => $meta['active'] ?? null,
        ]);
    }

    // ── Upload ────────────────────────────────────────────────────────────────

    public function upload(Request $request)
    {
        $request->validate([
            'background_image' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:5120', // 5 MB
            'background_name'  => 'required|string|max:80',
        ]);

        $file   = $request->file('background_image');
        $id     = Str::uuid()->toString();
        $ext    = $file->getClientOriginalExtension();
        $stored = $file->storeAs(self::FOLDER, "{$id}.{$ext}", self::DISK);

        $meta = $this->getMeta();
        $meta['backgrounds'][] = [
            'id'   => $id,
            'name' => trim($request->input('background_name')),
            'file' => "{$id}.{$ext}",
        ];

        // If this is the first one, set it active automatically
        if (empty($meta['active'])) {
            $meta['active'] = $id;
        }

        $this->saveMeta($meta);

        return back()->with('success', 'Background "' . trim($request->input('background_name')) . '" uploaded successfully!');
    }

    // ── Set Active ────────────────────────────────────────────────────────────

    public function setActive(Request $request, string $id)
    {
        $meta = $this->getMeta();
        $exists = collect($meta['backgrounds'])->firstWhere('id', $id);

        if (!$exists) {
            return back()->with('error', 'Background not found.');
        }

        $meta['active'] = $id;
        $this->saveMeta($meta);

        return back()->with('success', 'Background set as active template!');
    }

    // ── Set None (no background) ──────────────────────────────────────────────

    public function setNone()
    {
        $meta = $this->getMeta();
        $meta['active'] = null;
        $this->saveMeta($meta);

        return back()->with('success', 'Loyalty Incentive will be generated without a background image.');
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    public function destroy(string $id)
    {
        $meta = $this->getMeta();
        $bg   = collect($meta['backgrounds'])->firstWhere('id', $id);

        if (!$bg) {
            return back()->with('error', 'Background not found.');
        }

        // Delete the file
        Storage::disk(self::DISK)->delete(self::FOLDER . '/' . $bg['file']);

        // Remove from list
        $meta['backgrounds'] = array_values(
            array_filter($meta['backgrounds'], fn($b) => $b['id'] !== $id)
        );

        // If we deleted the active one, reset active
        if ($meta['active'] === $id) {
            $first = $meta['backgrounds'][0] ?? null;
            $meta['active'] = $first ? $first['id'] : null;
        }

        $this->saveMeta($meta);

        return back()->with('success', 'Background deleted.');
    }

    // ── Static helper: get active background absolute path for DomPDF ─────────

    public static function getActiveBackgroundPath(): ?string
    {
        $disk   = 'public';
        $folder = 'loyalty-backgrounds';
        $meta   = 'loyalty-backgrounds/meta.json';

        if (!Storage::disk($disk)->exists($meta)) {
            return null;
        }

        $data = json_decode(Storage::disk($disk)->get($meta), true);
        $activeId = $data['active'] ?? null;

        if (!$activeId) return null;

        $bg = collect($data['backgrounds'] ?? [])->firstWhere('id', $activeId);
        if (!$bg) return null;

        $path = storage_path('app/public/' . $folder . '/' . $bg['file']);
        return file_exists($path) ? $path : null;
    }
}
