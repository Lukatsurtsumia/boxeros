import './bootstrap';

// Avatar cropper — the Cropper.js library is loaded ON DEMAND (dynamic import) the first
// time a fighter picks a photo, so it stays OUT of the main bundle and doesn't slow the
// initial page load. It's only ever needed on the profile page.
//
// NOTE: Do NOT import or start Alpine here. Livewire bundles and starts its own Alpine —
// a second instance throws "Alpine has already been initialized" and silently breaks every
// wire:click on the page. Registering a component on the existing Alpine via `alpine:init`
// (below) is safe.

document.addEventListener('alpine:init', () => {
    window.Alpine.data('avatarCropper', () => ({
        open: false,
        uploading: false,
        cropper: null,
        baseRatio: 1,

        // A file was chosen → load Cropper (once, on demand), open the modal and mount it.
        async openModal(event) {
            const file = event.target.files[0];
            if (!file) return;
            const url = URL.createObjectURL(file);
            this.open = true;

            // Split out of the main bundle — fetched only now, cached for next time.
            const { default: Cropper } = await import('cropperjs');
            await import('cropperjs/dist/cropper.css');

            this.$nextTick(() => {
                const img = this.$refs.image;
                img.src = url;
                if (this.cropper) this.cropper.destroy();

                this.cropper = new Cropper(img, {
                    aspectRatio: 1,          // square crop, matches the rounded avatar
                    viewMode: 1,
                    dragMode: 'move',        // drag the photo to reposition
                    autoCropArea: 1,
                    background: false,
                    guides: false,
                    center: false,
                    cropBoxMovable: false,
                    cropBoxResizable: false,
                    toggleDragModeOnDblclick: false,
                    minContainerHeight: 260,
                    ready: () => {
                        // Remember the "fitted" zoom so the slider maps 0 → fit, 1 → 3×.
                        const d = this.cropper.getImageData();
                        this.baseRatio = d.width / d.naturalWidth;
                        this.$refs.zoom.value = 0;
                    },
                });
            });

            event.target.value = ''; // let the same file be re-picked later
        },

        // Slider 0..1 → zoom from fit up to 3×.
        setZoom(value) {
            if (!this.cropper) return;
            this.cropper.zoomTo(this.baseRatio * (1 + parseFloat(value) * 2));
        },

        closeModal() {
            this.open = false;
            if (this.cropper) { this.cropper.destroy(); this.cropper = null; }
        },

        // Export the framed area as a small JPEG and hand it to Livewire's uploader.
        apply() {
            if (!this.cropper || this.uploading) return;
            this.uploading = true;

            this.cropper
                .getCroppedCanvas({ width: 512, height: 512, imageSmoothingQuality: 'high' })
                .toBlob((blob) => {
                    const file = new File([blob], 'avatar.jpg', { type: 'image/jpeg' });
                    this.$wire.upload(
                        'avatar',
                        file,
                        () => { this.uploading = false; this.closeModal(); }, // success
                        () => { this.uploading = false; },                    // error
                    );
                }, 'image/jpeg', 0.9);
        },
    }));
});
