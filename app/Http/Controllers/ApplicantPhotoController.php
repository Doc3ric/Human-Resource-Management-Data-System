<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\ActivityLog;
use App\Support\PhotoEnforcementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ApplicantPhotoController extends Controller
{
    public function __construct(private readonly PhotoEnforcementService $photoService)
    {
    }

    public function upload(Request $request, Applicant $applicant)
    {
        $validated = $request->validate([
            'photo_data' => 'required|string', // base64 encoded image from Cropper.js
        ]);

        $base64 = $validated['photo_data'];
        
        // Ensure it's a valid data URI
        if (!preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
            return response()->json(['error' => 'Invalid image data'], 422);
        }

        $imageType = strtolower($type[1]);
        if (!in_array($imageType, ['jpg', 'jpeg', 'png', 'webp'])) {
            return response()->json(['error' => 'Only JPG, PNG, and WEBP formats are allowed.'], 422);
        }

        $base64Data = substr($base64, strpos($base64, ',') + 1);
        $imageData = base64_decode($base64Data);

        if ($imageData === false) {
            return response()->json(['error' => 'Failed to decode image data'], 422);
        }

        // Validate size (< 5MB roughly, checking decoded size)
        if (strlen($imageData) > 5 * 1024 * 1024) {
            return response()->json(['error' => 'Image must be under 5MB.'], 422);
        }

        $filename = 'applicant_' . $applicant->id . '_' . Str::random(10) . '.' . $imageType;
        $path = 'photos/' . $filename;

        Storage::disk('public')->put($path, $imageData);
        $url = Storage::disk('public')->url($path);

        $this->photoService->recordManualUpload($applicant, $url);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Uploaded Applicant Photo',
            'description' => "Uploaded a 2x2 ID photo manually for applicant ID {$applicant->id}.",
        ]);

        return response()->json([
            'success' => true,
            'url' => $url,
            'message' => 'Photo uploaded and saved successfully.'
        ]);
    }

    public function import(Request $request, Applicant $applicant)
    {
        $record = $this->photoService->findMirrorCandidate($applicant);
        
        if (!$record || !$record->profile_picture) {
            return back()->withErrors(['photo' => 'No active 201-file with a photo was found for this applicant.']);
        }

        $this->photoService->importFrom201File($applicant, $record);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Imported 201-File Photo',
            'description' => "Imported profile photo from 201-file for applicant ID {$applicant->id}.",
        ]);

        return back()->with('success', 'Photo imported from 201-file. Please verify and confirm it matches the applicant.');
    }

    public function confirm(Request $request, Applicant $applicant)
    {
        $request->validate([
            'confirmation' => 'required|accepted',
        ]);

        $this->photoService->confirmImportedPhoto($applicant);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Confirmed Applicant Photo',
            'description' => "Confirmed imported 201-file photo correctly identifies applicant ID {$applicant->id}.",
        ]);

        return back()->with('success', 'Photo verified and confirmed successfully. Scoring is now unlocked.');
    }
}
