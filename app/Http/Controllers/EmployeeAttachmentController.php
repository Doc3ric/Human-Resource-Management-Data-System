<?php

namespace App\Http\Controllers;

use App\Models\PlantillaRecord;
use App\Models\JobOrder;
use App\Models\CasualEmployee;
use App\Models\EmployeeAttachment;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EmployeeAttachmentController extends Controller
{
    // ── Shared file upload helper ─────────────────────────────────────────────

    private function uploadFile(Request $request): array
    {
        $request->validate([
            'document_type' => 'required|string|max:255',
            'attachment'    => 'required|file|max:10240', // max 10MB
        ]);

        $file = $request->file('attachment');

        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension    = $file->getClientOriginalExtension();
        $safeFileName = Str::slug($originalName) . '-' . time() . '.' . $extension;

        $path = $file->storeAs('attachments', $safeFileName, 'public');

        return [
            'path'     => $path,
            'name'     => $file->getClientOriginalName(),
            'type'     => $extension,
            'doc_type' => $request->document_type,
            'uploader' => Auth::user()->name ?? 'System',
        ];
    }

    // ── PlantillaRecord Attachments ───────────────────────────────────────────

    /**
     * Store a newly created attachment for a PlantillaRecord.
     */
    public function store(Request $request, PlantillaRecord $plantilla)
    {
        $data = $this->uploadFile($request);

        $plantilla->attachments()->create([
            'file_name'    => $data['name'],
            'file_path'    => $data['path'],
            'file_type'    => $data['type'],
            'document_type'=> $data['doc_type'],
            'uploaded_by'  => $data['uploader'],
        ]);

        if (Auth::check()) {
            ActivityLog::create([
                'user_id'     => Auth::id(),
                'action'      => 'Uploaded File',
                'description' => 'Uploaded ' . $data['doc_type'] . ' for ' . $plantilla->full_name,
            ]);
        }

        return back()->with('success', 'Attachment uploaded successfully.');
    }

    // ── JobOrder Attachments ──────────────────────────────────────────────────

    /**
     * Store a newly created attachment for a JobOrder.
     */
    public function storeForJobOrder(Request $request, JobOrder $jobOrder)
    {
        $data = $this->uploadFile($request);

        $jobOrder->attachments()->create([
            'file_name'    => $data['name'],
            'file_path'    => $data['path'],
            'file_type'    => $data['type'],
            'document_type'=> $data['doc_type'],
            'uploaded_by'  => $data['uploader'],
        ]);

        if (Auth::check()) {
            ActivityLog::create([
                'user_id'     => Auth::id(),
                'action'      => 'Uploaded File',
                'description' => 'Uploaded ' . $data['doc_type'] . ' for JO: ' . $jobOrder->full_name,
            ]);
        }

        return back()->with('success', 'Attachment uploaded successfully.');
    }

    // ── CasualEmployee Attachments ────────────────────────────────────────────

    /**
     * Store a newly created attachment for a CasualEmployee.
     */
    public function storeForCasual(Request $request, CasualEmployee $casual)
    {
        $data = $this->uploadFile($request);

        $casual->attachments()->create([
            'file_name'    => $data['name'],
            'file_path'    => $data['path'],
            'file_type'    => $data['type'],
            'document_type'=> $data['doc_type'],
            'uploaded_by'  => $data['uploader'],
        ]);

        if (Auth::check()) {
            ActivityLog::create([
                'user_id'     => Auth::id(),
                'action'      => 'Uploaded File',
                'description' => 'Uploaded ' . $data['doc_type'] . ' for Casual: ' . $casual->full_name,
            ]);
        }

        return back()->with('success', 'Attachment uploaded successfully.');
    }

    // ── Shared Download & Delete ──────────────────────────────────────────────

    /**
     * Download the specified attachment.
     */
    public function download(EmployeeAttachment $attachment)
    {
        if (!Storage::disk('public')->exists($attachment->file_path)) {
            return back()->with('error', 'File not found on the server.');
        }

        return Storage::disk('public')->download($attachment->file_path, $attachment->file_name);
    }

    /**
     * View (stream inline) the specified attachment — opens in browser tab.
     * Viewable types (PDF, images) open directly; others fall back to download.
     */
    public function view(EmployeeAttachment $attachment)
    {
        if (!Storage::disk('public')->exists($attachment->file_path)) {
            return back()->with('error', 'File not found on the server.');
        }

        $ext = strtolower(pathinfo($attachment->file_path, PATHINFO_EXTENSION));

        $inlineMimeTypes = [
            'pdf'  => 'application/pdf',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
        ];

        // If it's a viewable type, stream inline so the browser opens it
        if (isset($inlineMimeTypes[$ext])) {
            $filePath = Storage::disk('public')->path($attachment->file_path);
            return response()->file($filePath, [
                'Content-Type'        => $inlineMimeTypes[$ext],
                'Content-Disposition' => 'inline; filename="' . $attachment->file_name . '"',
            ]);
        }

        // For non-viewable types (docx, xlsx, etc.) — force download
        return Storage::disk('public')->download($attachment->file_path, $attachment->file_name);
    }

    /**
     * Remove the specified attachment from storage and DB.
     */
    public function destroy(EmployeeAttachment $attachment)
    {
        // Determine owner for activity log
        if ($attachment->job_order_id && $attachment->jobOrder) {
            $ownerName = 'JO: ' . $attachment->jobOrder->full_name;
        } elseif ($attachment->casual_employee_id && $attachment->casualEmployee) {
            $ownerName = 'Casual: ' . $attachment->casualEmployee->full_name;
        } elseif ($attachment->plantilla_record_id && $attachment->plantillaRecord) {
            $ownerName = $attachment->plantillaRecord->full_name;
        } else {
            $ownerName = 'Unknown Record';
        }

        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $docType = $attachment->document_type;
        $attachment->delete();

        if (Auth::check()) {
            ActivityLog::create([
                'user_id'     => Auth::id(),
                'action'      => 'Deleted File',
                'description' => 'Deleted ' . $docType . ' for ' . $ownerName,
            ]);
        }

        return back()->with('success', 'Attachment deleted successfully.');
    }
}
