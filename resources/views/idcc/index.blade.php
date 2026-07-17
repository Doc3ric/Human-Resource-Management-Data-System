<x-dashboard-app>
<style>
.idcc-hero {
    background: linear-gradient(135deg,#1e3a5f 0%,#1a5276 55%,#0f4c75 100%);
    border-radius:14px; padding:28px 32px; margin-bottom:24px; color:#fff;
}
.idcc-hero h1 { font-size:22px; font-weight:800; margin:0 0 4px; }
.idcc-hero p { font-size:13px; opacity:.75; margin:0; }
.idcc-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:18px; margin-bottom:18px; }
.idcc-folder { display:inline-flex; flex-direction:column; gap:2px; padding:10px 16px; border-radius:10px; background:#f9fafb; border:1px solid #e5e7eb; margin:0 8px 8px 0; font-size:12px; }
.idcc-folder b { font-size:16px; }
.idcc-table { width:100%; border-collapse:collapse; font-size:13px; }
.idcc-table th { text-align:left; padding:8px 10px; font-size:11px; text-transform:uppercase; color:#9ca3af; border-bottom:2px solid #e5e7eb; }
.idcc-table td { padding:8px 10px; border-bottom:1px solid #f3f4f6; }
.idcc-tag { font-size:10px; font-weight:700; padding:2px 8px; border-radius:99px; }
.tag-spi { background:#fef2f2; color:#dc2626; }
.tag-raccs { background:#450a0a; color:#fff; }
.tag-ok { background:#f0fdf4; color:#16a34a; }
.capture-box { border:2px dashed #d1d5db; border-radius:12px; padding:20px; text-align:center; }
.capture-preview { display:flex; gap:8px; flex-wrap:wrap; margin-top:12px; }
.capture-preview img { width:90px; height:90px; object-fit:cover; border-radius:8px; border:1px solid #e5e7eb; }
.dup-modal { position:fixed; inset:0; background:rgba(15,23,42,.55); display:none; align-items:center; justify-content:center; z-index:1000; }
.dup-modal.active { display:flex; }
.dup-card { background:#fff; border-radius:14px; padding:24px; max-width:480px; width:calc(100% - 32px); }
.flash-success { background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; border-radius:10px; padding:11px 16px; margin-bottom:14px; font-size:13px; font-weight:600; display:flex; align-items:center; gap:8px; }
.flash-error { background:#fef2f2; border:1px solid #fecaca; color:#991b1b; border-radius:10px; padding:11px 16px; margin-bottom:14px; font-size:13px; font-weight:600; display:flex; align-items:center; gap:8px; }
.idcc-bulk-toolbar { display:flex; align-items:center; gap:8px; margin-bottom:12px; padding:8px 12px; background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; font-size:12.5px; }
.idcc-bulk-toolbar .count { font-weight:700; color:#374151; margin-right:6px; }
</style>

@if(session('success'))
    <div class="flash-success"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="flash-error"><i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}</div>
@endif

<div class="idcc-hero">
    <h1><i class="bi bi-file-earmark-lock2-fill"></i> IDCC — Document Capture &amp; Classification</h1>
    <p>Every document — uploaded, scanned, or photographed — enters here first, gets classified, and is privacy-triaged before filing.</p>
</div>

<div class="idcc-card" style="margin-bottom: 18px; max-height: 500px; overflow-y: auto;" x-data="idccCapture()">
    <div style="display: flex; gap: 32px; align-items: flex-start;">
        
        <!-- Left: Upload/Capture (Compact & Centered) -->
        <div style="flex: 0 0 280px; display: flex; flex-direction: column; align-items: center;">
            <h3 style="font-size:15px;font-weight:700;margin:0 0 16px 0; align-self: flex-start;">Quick Upload</h3>
            
            <div style="background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 12px; padding: 24px; text-align: center; width: 100%;">
                <i class="bi bi-cloud-arrow-up" style="font-size: 28px; color: #94a3b8; margin-bottom: 8px; display: block;"></i>
                <input type="file" id="idcc-file-input" multiple accept=".pdf,.jpg,.jpeg,.png,.tiff,.docx,.xlsx,.txt"
                    @change="onFilesSelected($event.target.files)" style="font-size: 11px; max-width: 100%; margin-bottom: 12px;">
                <div style="font-size: 11px; color: #94a3b8; margin-bottom: 12px; font-weight: 700; text-transform: uppercase;">OR</div>
                <button type="button" class="btn btn-sm btn-outline-primary" @click="startWebcam()" x-show="!webcamActive" style="width: 100%;">
                    <i class="bi bi-camera-fill"></i> Use Camera
                </button>
            </div>

            <!-- Webcam UI Inline when active -->
            <template x-if="webcamActive">
                <div style="margin-top:16px; background:#f9fafb; padding:12px; border-radius:8px; border:1px solid #e5e7eb; width: 100%; text-align: center;">
                    <video x-ref="video" autoplay playsinline style="max-height:150px; width: 100%; border-radius:8px; object-fit: cover;"></video>
                    <canvas x-ref="canvas" style="display:none;"></canvas>
                    <div style="margin-top:8px;display:flex;gap:8px;justify-content:center;">
                        <button type="button" class="btn btn-sm btn-primary" @click="snapshot()">
                            <i class="bi bi-camera"></i> Capture
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary" @click="stopWebcam()">Done</button>
                    </div>
                </div>
            </template>

            <div class="capture-preview" x-show="captures.length > 0" style="margin-top:16px; background:#f9fafb; padding:12px; border-radius:8px; border:1px solid #e5e7eb; width: 100%;">
                <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom: 12px; justify-content: center;">
                    <template x-for="(cap, i) in captures" :key="i">
                        <div style="position:relative;">
                            <img :src="cap.previewUrl" style="width:50px; height:50px; object-fit:cover; border-radius:6px; border:1px solid #d1d5db;">
                            <button type="button" @click="captures.splice(i, 1)"
                                style="position:absolute;top:-6px;right:-6px;background:#dc2626;color:#fff;border:none;border-radius:50%;width:18px;height:18px;font-size:12px;line-height:1;display:flex;align-items:center;justify-content:center;cursor:pointer;">&times;</button>
                        </div>
                    </template>
                </div>
                <button type="button" class="btn btn-sm btn-success" @click="submitAll()" :disabled="submitting" style="width: 100%;">
                    <i class="bi bi-upload"></i> <span x-text="submitting ? 'Uploading...' : 'Submit ' + captures.length + ' item(s)'"></span>
                </button>
                <div x-show="messages.length" style="margin-top:12px;text-align:left;">
                    <template x-for="(m, i) in messages" :key="i">
                        <div :style="'padding:6px 10px;border-radius:6px;margin-bottom:4px;font-size:11.5px;' + (m.ok ? 'background:#f0fdf4;color:#166534;' : 'background:#fef2f2;color:#991b1b;')" x-text="m.text"></div>
                    </template>
                </div>
            </div>

            <!-- Duplicate blocked notice -->
            <div class="dup-modal" :class="{ active: duplicateQueue.length > 0 }">
                <div class="dup-card" x-show="duplicateQueue.length > 0">
                    <h4 style="font-weight:700;margin-bottom:8px;">This file already exists — upload rejected</h4>
                    <p style="font-size:13px;color:#4b5563;" x-text="currentDuplicateMessage()"></p>
                    <p style="font-size:12px;color:#9ca3af;margin-top:6px;">Duplicate files are not allowed. Go to the existing document below if you need to view or attach it.</p>
                    <div style="display:flex;flex-direction:column;gap:8px;margin-top:14px;">
                        <button type="button" class="btn btn-sm btn-primary" @click="dismissDuplicate()">OK, skip this file</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Category Folders -->
        <div style="flex: 1;">
            <h3 style="font-size:15px;font-weight:700;margin:0 0 16px 0;">Category Folders &amp; Stats</h3>
            @php
                $groupedFolders = $folders->groupBy('doc_type_code');
            @endphp
            <div style="display:flex; flex-wrap:wrap; gap: 16px;">
                @foreach($groupedFolders as $category => $items)
                    <div>
                        <h4 style="font-size:12px;font-weight:700;margin-bottom:6px;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;"><i class="bi bi-folder2-open"></i> {{ $category ?: 'Uncategorized' }}</h4>
                        <div style="display:flex;flex-wrap:wrap;">
                        @foreach($items as $f)
                            <a href="{{ request()->fullUrlWithQuery(['doc_type_code' => $category, 'search_keyword' => $f->search_keyword]) }}" class="idcc-folder" style="text-decoration:none;color:inherit;padding:6px 12px;font-size:11px; margin-bottom: 4px;">
                                <span>{{ $f->search_keyword ?: '(No Keyword)' }} <span style="opacity:0.6;font-size:9px;">(Tier {{ $f->privacy_tier }})</span></span>
                                <b style="font-size:13px;">{{ $f->total }} docs</b>
                            </a>
                        @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            @if(request()->filled('doc_type_code') || request()->filled('search_keyword'))
                <a href="{{ route('idcc.index') }}" class="btn btn-sm btn-outline-secondary" style="margin-top:8px;">Clear Folder Filter</a>
            @endif
        </div>

    </div>
</div>

<div class="idcc-card" x-data="idccDocumentActions({{ $documents->count() }})">
    <form method="GET" style="margin-bottom:14px;">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search document text..."
            style="border:1px solid #d1d5db;border-radius:8px;padding:8px 12px;font-size:13px;min-width:260px;">
        <button class="btn btn-sm btn-primary">Search</button>
    </form>

    <div class="idcc-bulk-toolbar">
        <span class="count"><span x-text="selected.length"></span> selected</span>
        <button type="button" class="btn btn-sm btn-outline-secondary" :disabled="selected.length !== 1" @click="previewSelected()">
            <i class="bi bi-eye"></i> Preview
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" :disabled="selected.length === 0" @click="printSelected()">
            <i class="bi bi-printer"></i> Print
        </button>
        @can('edit Document Ingestion & Capture')
            <button type="button" class="btn btn-sm btn-outline-secondary" :disabled="selected.length === 0" @click="rotateSelected(270)">
                <i class="bi bi-arrow-counterclockwise"></i> Rotate Left
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" :disabled="selected.length === 0" @click="rotateSelected(90)">
                <i class="bi bi-arrow-clockwise"></i> Rotate Right
            </button>
        @endcan
        @can('delete Document Ingestion & Capture')
            <button type="button" class="btn btn-sm btn-outline-danger" :disabled="selected.length === 0" @click="deleteSelected()">
                <i class="bi bi-trash"></i> Delete
            </button>
        @endcan
    </div>

    <table class="idcc-table">
        <thead>
            <tr>
                <th><input type="checkbox" @change="toggleAll($event.target.checked)" :checked="selected.length > 0 && selected.length === totalRows"></th>
                <th>File</th><th>Keyword</th><th>Type</th><th>Importance</th><th>Status</th><th>Flags</th><th>Ingested</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($documents as $doc)
                @php $needsReprocess = $doc->status !== 'classified' || $doc->doc_type_code === 'OTHER'; @endphp
                <tr>
                    <td><input type="checkbox" value="{{ $doc->id }}" @change="toggleOne({{ $doc->id }}, $event.target.checked)" :checked="selected.includes({{ $doc->id }})"></td>
                    <td><a href="{{ route('idcc.show', $doc) }}">{{ $doc->original_filename }}</a></td>
                    <td>
                        <form method="POST" action="{{ route('idcc.update-keyword', $doc) }}" style="display:flex;gap:4px;">
                            @csrf @method('PATCH')
                            <input type="text" name="search_keyword" value="{{ $doc->search_keyword }}" style="border:1px solid #d1d5db;border-radius:4px;font-size:11px;padding:2px 6px;width:120px;" title="Search Keyword">
                            <button type="submit" style="background:none;border:none;color:#2563eb;cursor:pointer;"><i class="bi bi-check2-square"></i></button>
                        </form>
                    </td>
                    <td>{{ $doc->doc_type_code }}</td>
                    <td>{{ $doc->importance_class }}</td>
                    <td>{{ $doc->status }}</td>
                    <td>
                        @if($doc->is_raccs) <span class="idcc-tag tag-raccs">RACCS</span> @endif
                        @if($doc->is_spi) <span class="idcc-tag tag-spi">SPI</span> @endif
                        @if(!$doc->is_spi && !$doc->is_raccs) <span class="idcc-tag tag-ok">Standard</span> @endif
                    </td>
                    <td>{{ $doc->created_at->diffForHumans() }}</td>
                    <td>
                        @if($needsReprocess)
                            <form method="POST" action="{{ route('idcc.reprocess', $doc) }}" style="display:inline;"
                                onsubmit="return confirm('Re-run OCR and classification on this document using the extraction tools currently installed?');">
                                @csrf
                                <button type="submit" class="btn btn-sm" title="Re-run OCR + classification with whatever extraction tools are currently installed">
                                    Reprocess
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" style="text-align:center;color:#9ca3af;">No documents yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $documents->links() }}
</div>

<script>
function idccCapture() {
    return {
        captures: [],
        webcamActive: false,
        stream: null,
        submitting: false,
        messages: [],
        duplicateQueue: [],

        onFilesSelected(fileList) {
            Array.from(fileList).forEach(file => {
                this.captures.push({ file, previewUrl: URL.createObjectURL(file) });
            });
        },

        async startWebcam() {
            try {
                this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                this.webcamActive = true;
                this.$nextTick(() => { this.$refs.video.srcObject = this.stream; });
            } catch (e) {
                this.messages.push({ ok: false, text: 'Could not access camera: ' + e.message });
            }
        },

        stopWebcam() {
            if (this.stream) this.stream.getTracks().forEach(t => t.stop());
            this.webcamActive = false;
        },

        snapshot() {
            const video = this.$refs.video, canvas = this.$refs.canvas;
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            canvas.toBlob(blob => {
                const file = new File([blob], 'capture-' + Date.now() + '.jpg', { type: 'image/jpeg' });
                this.captures.push({ file, previewUrl: URL.createObjectURL(blob), captureSource: 'WEBCAM' });
            }, 'image/jpeg', 0.92);
        },

        async submitAll() {
            this.submitting = true;
            this.messages = [];
            for (const cap of this.captures) {
                await this.submitOne(cap);
            }
            this.submitting = false;
            this.captures = [];
        },

        async submitOne(cap) {
            const fd = new FormData();
            fd.append('file', cap.file);
            fd.append('capture_source', cap.captureSource || 'UPLOAD');

            const res = await fetch('{{ route('idcc.store') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                body: fd,
            });

            if (res.status === 409) {
                const data = await res.json();
                this.duplicateQueue.push({ cap, existing: data.existing });
                return;
            }

            if (res.ok) {
                this.messages.push({ ok: true, text: `"${cap.file.name}" ingested successfully.` });
            } else {
                this.messages.push({ ok: false, text: `"${cap.file.name}" failed to ingest.` });
            }
        },

        currentDuplicateMessage() {
            if (!this.duplicateQueue.length) return '';
            const existing = this.duplicateQueue[0].existing;
            if (!existing.visible) return existing.message;
            return `An identical file already exists (ingested ${existing.ingested_at || 'previously'} by ${existing.ingested_by || 'someone'}, attached to: ${existing.attachment_field || 'a record'}).`;
        },

        dismissDuplicate() {
            // The file was already rejected server-side (409) — nothing to resubmit,
            // just drop it from the queue so the user can move on.
            this.duplicateQueue.shift();
        },
    };
}

function idccDocumentActions(totalRows) {
    return {
        selected: [],
        totalRows,

        toggleOne(id, checked) {
            if (checked) {
                if (!this.selected.includes(id)) this.selected.push(id);
            } else {
                this.selected = this.selected.filter(x => x !== id);
            }
        },

        toggleAll(checked) {
            this.selected = checked
                ? Array.from(document.querySelectorAll('.idcc-table tbody input[type=checkbox]')).map(el => parseInt(el.value))
                : [];
        },

        previewSelected() {
            if (this.selected.length !== 1) return;
            window.open('{{ url('/idcc') }}/' + this.selected[0] + '/preview', '_blank');
        },

        printSelected() {
            if (!this.selected.length) return;
            const query = this.selected.map(id => 'ids[]=' + id).join('&');
            window.open('{{ route('idcc.print') }}?' + query, '_blank');
        },

        async rotateSelected(degrees) {
            if (!this.selected.length) return;
            await this.postBulk('{{ route('idcc.bulk-rotate') }}', { ids: this.selected, degrees });
        },

        async deleteSelected() {
            if (!this.selected.length) return;
            const confirmation = prompt('This will soft-delete ' + this.selected.length + ' document(s). Type DELETE to confirm:');
            if (confirmation !== 'DELETE') return;
            await this.postBulk('{{ route('idcc.bulk-delete') }}', { ids: this.selected, confirmation });
        },

        async postBulk(url, payload) {
            const fd = new FormData();
            payload.ids.forEach(id => fd.append('ids[]', id));
            Object.keys(payload).filter(k => k !== 'ids').forEach(k => fd.append(k, payload[k]));

            await fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: fd,
            });

            window.location.reload();
        },
    };
}
</script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</x-dashboard-app>
