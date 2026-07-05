@props(['inputName' => 'signature_image'])

{{-- Module 10-M4 — reusable e-signature capture (CS Form No. 11 s.2025). --}}
<div x-data="signaturePad()" x-init="init()">
    <canvas x-ref="canvas" width="400" height="150" style="border:1px solid #d1d5db;border-radius:8px;touch-action:none;cursor:crosshair;"></canvas>
    <input type="hidden" name="{{ $inputName }}" x-ref="input">
    <div style="margin-top:6px;">
        <button type="button" class="btn btn-sm btn-outline-secondary" @click="clear()">Clear Signature</button>
    </div>
</div>

<script>
function signaturePad() {
    return {
        drawing: false,
        ctx: null,
        init() {
            const canvas = this.$refs.canvas;
            this.ctx = canvas.getContext('2d');
            this.ctx.strokeStyle = '#111827';
            this.ctx.lineWidth = 2;

            const pos = (e) => {
                const rect = canvas.getBoundingClientRect();
                const point = e.touches ? e.touches[0] : e;
                return { x: point.clientX - rect.left, y: point.clientY - rect.top };
            };

            const start = (e) => { this.drawing = true; const p = pos(e); this.ctx.beginPath(); this.ctx.moveTo(p.x, p.y); };
            const move = (e) => {
                if (!this.drawing) return;
                const p = pos(e);
                this.ctx.lineTo(p.x, p.y);
                this.ctx.stroke();
                this.$refs.input.value = canvas.toDataURL('image/png');
            };
            const end = () => { this.drawing = false; };

            canvas.addEventListener('mousedown', start);
            canvas.addEventListener('mousemove', move);
            canvas.addEventListener('mouseup', end);
            canvas.addEventListener('touchstart', start);
            canvas.addEventListener('touchmove', move);
            canvas.addEventListener('touchend', end);
        },
        clear() {
            this.ctx.clearRect(0, 0, this.$refs.canvas.width, this.$refs.canvas.height);
            this.$refs.input.value = '';
        },
    };
}
</script>
