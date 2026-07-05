@props(['targetUrl' => '#', 'inputName' => 'document'])

<div x-data="documentCapture()" class="document-capture-widget p-3 border rounded shadow-sm bg-white" style="max-width: 500px;">
    <h6 class="fw-bold mb-3"><i class="bi bi-camera"></i> Document Capture (IDCC)</h6>
    
    <div class="btn-group w-100 mb-3" role="group">
        <button type="button" class="btn btn-outline-primary btn-sm" :class="{ 'active': mode === 'upload' }" @click="mode = 'upload'">
            <i class="bi bi-upload"></i> Upload
        </button>
        <button type="button" class="btn btn-outline-primary btn-sm" :class="{ 'active': mode === 'webcam' }" @click="startWebcam">
            <i class="bi bi-webcam"></i> Webcam
        </button>
        <button type="button" class="btn btn-outline-primary btn-sm" :class="{ 'active': mode === 'scanner' }" @click="mode = 'scanner'">
            <i class="bi bi-scanner"></i> Scanner (TWAIN)
        </button>
    </div>

    <!-- Upload Mode -->
    <div x-show="mode === 'upload'">
        <input type="file" name="{{ $inputName }}" class="form-control" accept="image/*,application/pdf" @change="handleFileUpload">
    </div>

    <!-- Webcam Mode -->
    <div x-show="mode === 'webcam'" class="text-center">
        <div x-show="!capturedImage" class="position-relative bg-dark rounded mb-2" style="height: 240px; overflow: hidden;">
            <video x-ref="videoElement" autoplay playsinline style="width: 100%; height: 100%; object-fit: cover;"></video>
            <div x-show="!streamActive" class="position-absolute top-50 start-50 translate-middle text-white">
                Camera starting...
            </div>
        </div>
        <div x-show="capturedImage" class="mb-2">
            <img :src="capturedImage" class="img-thumbnail" style="max-height: 240px;">
        </div>

        <div class="d-flex justify-content-center gap-2">
            <button x-show="!capturedImage" type="button" class="btn btn-success btn-sm" @click="capturePhoto">
                <i class="bi bi-circle-fill text-danger"></i> Capture
            </button>
            <button x-show="capturedImage" type="button" class="btn btn-warning btn-sm" @click="retakePhoto">
                <i class="bi bi-arrow-counterclockwise"></i> Retake
            </button>
            <!-- Hidden input to pass base64 to server if not using direct AJAX -->
            <input type="hidden" name="{{ $inputName }}_base64" :value="capturedImage">
        </div>
        <canvas x-ref="canvasElement" style="display: none;"></canvas>
    </div>

    <!-- Scanner (TWAIN) Mode -->
    <div x-show="mode === 'scanner'" class="text-center p-4 bg-light rounded border">
        <i class="bi bi-printer" style="font-size: 2rem; color: #6c757d;"></i>
        <p class="mt-2 mb-1 fw-bold text-muted">Awaiting TWAIN Client Bridge</p>
        <small class="text-muted d-block mb-3">Install the HRDMS Scanner Client to capture directly from local flatbed/ADF scanners.</small>
        <button type="button" class="btn btn-sm btn-outline-secondary" disabled>Poll Scanner</button>
    </div>
</div>

<script>
function documentCapture() {
    return {
        mode: 'upload',
        stream: null,
        streamActive: false,
        capturedImage: null,

        init() {
            this.$watch('mode', value => {
                if (value !== 'webcam') this.stopWebcam();
                if (value === 'webcam') this.startWebcam();
            });
        },

        async startWebcam() {
            this.mode = 'webcam';
            this.capturedImage = null;
            if (this.streamActive) return;
            try {
                this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                this.$refs.videoElement.srcObject = this.stream;
                this.streamActive = true;
            } catch (err) {
                console.error("Camera access denied or unavailable.", err);
                alert("Unable to access camera. Please check permissions.");
            }
        },

        stopWebcam() {
            if (this.stream) {
                this.stream.getTracks().forEach(track => track.stop());
            }
            this.streamActive = false;
        },

        capturePhoto() {
            if (!this.streamActive) return;
            const video = this.$refs.videoElement;
            const canvas = this.$refs.canvasElement;
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
            this.capturedImage = canvas.toDataURL('image/jpeg', 0.85);
            this.stopWebcam();
        },

        retakePhoto() {
            this.capturedImage = null;
            this.startWebcam();
        },

        handleFileUpload(e) {
            // Optional: Handle pre-upload file hashing here to prevent duplicates
        }
    }
}
</script>
