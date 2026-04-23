// resources/js/barcode-scanner.js
document.addEventListener('alpine:init', () => {
    Alpine.data('barcodeScanner', (config = {}) => ({
        // === Config (dari caller) ===
        onScan: config.onScan || null, // callback(barcode) — WAJIB
        onError: config.onError || null, // callback(error) — opsional
        elementId: config.elementId || 'barcode-reader',

        // === State ===
        html5QrCode: null,
        scanning: false,
        transitioning: false,
        cameraError: '',
        scanBuffer: '',
        scanTimeout: null,
        lastScanTime: 0,
        scanSuccess: false,
        currentCameraIndex: -1,
        availableCameras: [],
        cameraLabel: '',

        // === Lifecycle ===
        init() {
            this._setupHardwareListener();
        },

        async destroy() {
            await this.stopCamera();
        },

        // === Hardware Scanner (USB/Bluetooth) ===
        _setupHardwareListener() {
            this._keyHandler = (e) => {
                const activeTag = document.activeElement?.tagName?.toLowerCase();
                // PENTING: Di kasir ada input search, jadi cek apakah input punya
                // attribute data-barcode-passthrough. Kalau tidak ada, skip.
                if (['input', 'textarea', 'select'].includes(activeTag)) {
                    if (!document.activeElement.hasAttribute('data-barcode-passthrough')) {
                        return;
                    }
                }

                if (e.key === 'Enter' && this.scanBuffer.length >= 4) {
                    e.preventDefault();
                    this._handleResult(this.scanBuffer);
                    this.scanBuffer = '';
                    clearTimeout(this.scanTimeout);
                } else if (e.key.length === 1) {
                    this.scanBuffer += e.key;
                    clearTimeout(this.scanTimeout);
                    this.scanTimeout = setTimeout(() => {
                        this.scanBuffer = '';
                    }, 100);
                }
            };
            document.addEventListener('keydown', this._keyHandler);
        },

        // === Camera Scanner (OPTIMIZED) ===
        async startCamera() {
            if (this.transitioning || this.scanning) return;
            
            this.cameraError = '';
            this.transitioning = true;
            this.scanning = true; // Set ini dulu agar x-show di blade aktif
            await this.$nextTick();

            try {
                if (!this.html5QrCode) {
                    this.html5QrCode = new Html5Qrcode(this.elementId);
                }
                
                const config = {
                    fps: 15,
                    videoConstraints: {
                        width: { min: 640, ideal: 1280 },
                        height: { min: 480, ideal: 720 },
                        aspectRatio: 1.7778
                    },
                    qrbox: null, 
                    disableFlip: false,
                    experimentalFeatures: {
                        useBarCodeDetectorIfSupported: true 
                    },
                    formatsToSupport: [
                        Html5QrcodeSupportedFormats.EAN_13,
                        Html5QrcodeSupportedFormats.CODE_128,
                        Html5QrcodeSupportedFormats.UPC_A,
                        Html5QrcodeSupportedFormats.UPC_E,
                        Html5QrcodeSupportedFormats.EAN_8,
                        Html5QrcodeSupportedFormats.CODE_39,
                        Html5QrcodeSupportedFormats.QR_CODE,
                        Html5QrcodeSupportedFormats.DATA_MATRIX,
                    ],
                };

                const onSuccess = async (decodedText) => {
                    // Debounce — cegah double scan
                    const now = Date.now();
                    if (now - this.lastScanTime < 1500) return;
                    this.lastScanTime = now;
                    
                    // Visual feedback
                    this.scanSuccess = true;
                    this._playBeep();
                    
                    // Stop camera first to avoid transition conflicts
                    await this.stopCamera();
                    
                    setTimeout(() => {
                        this.scanSuccess = false;
                        this._handleResult(decodedText);
                    }, 300);
                };

                try {
                    this.availableCameras = await Html5Qrcode.getCameras();
                    
                    if (this.availableCameras && this.availableCameras.length > 0) {
                        // Jika baru mulai pertama kali, coba cari kamera belakang
                        if (this.currentCameraIndex === -1) {
                            const backIndex = this.availableCameras.findIndex(cam => 
                                cam.label.toLowerCase().includes('back') || 
                                cam.label.toLowerCase().includes('rear') ||
                                cam.label.toLowerCase().includes('belakang') ||
                                cam.label.toLowerCase().includes('environment')
                            );
                            this.currentCameraIndex = backIndex !== -1 ? backIndex : (this.availableCameras.length - 1);
                        }

                        const camera = this.availableCameras[this.currentCameraIndex];
                        this.cameraLabel = camera.label || `Kamera ${this.currentCameraIndex + 1}`;
                        
                        await this.html5QrCode.start(
                            camera.id,
                            config,
                            onSuccess
                        );
                    } else {
                        // Fallback jika list kamera kosong
                        this.cameraLabel = 'Default Camera';
                        await this.html5QrCode.start(
                            { facingMode: "environment" },
                            config,
                            onSuccess
                        );
                    }
                    
                    this._applyAdvancedConstraints();
                } catch (e) {
                    console.warn("Camera selection failed, trying simple facingMode...", e);
                    
                    try {
                        await this.html5QrCode.clear();
                        await new Promise(r => setTimeout(r, 300));
                    } catch (clearErr) { /* ignore */ }

                    // Fallback terakhir
                    await this.html5QrCode.start(
                        { facingMode: "environment" },
                        config,
                        onSuccess
                    );
                }
            } catch (err) {
                console.error('Camera Error:', err);
                this.scanning = false;
                this.cameraError = 'Kamera tidak dapat diakses. Pastikan izin kamera diberikan.';
                if (this.onError) this.onError(err.message || err);
            } finally {
                this.transitioning = false;
            }
        },

        // Apply zoom + focus setelah camera start
        async _applyAdvancedConstraints() {
            try {
                const videoElement = document.querySelector(`#${this.elementId} video`);
                if (!videoElement) return;
                const track = videoElement.srcObject?.getVideoTracks()?.[0];
                if (!track) return;
                const capabilities = track.getCapabilities?.();
                if (!capabilities) return;

                const advancedConstraints = {};

                // Auto-zoom kalau device support
                if (capabilities.zoom) {
                    const maxZoom = Math.min(capabilities.zoom.max, 2.5);
                    const idealZoom = Math.max(capabilities.zoom.min, 1.5);
                    advancedConstraints.zoom = Math.min(idealZoom, maxZoom);
                }

                // Continuous auto-focus
                if (capabilities.focusMode?.includes('continuous')) {
                    advancedConstraints.focusMode = 'continuous';
                }

                if (Object.keys(advancedConstraints).length > 0) {
                    await track.applyConstraints({ advanced: [advancedConstraints] });
                }
            } catch (e) {
                // Tidak semua device support — fail silently
                console.warn('Advanced camera constraints not supported:', e);
            }
        },

        async stopCamera() {
            if (this.transitioning && !this.scanning) {
                // If we're starting, wait a bit or try again
                let attempts = 0;
                while (this.transitioning && attempts < 10) {
                    await new Promise(r => setTimeout(r, 100));
                    attempts++;
                }
            }

            if (!this.html5QrCode || !this.scanning) {
                this.scanning = false;
                return;
            }

            this.transitioning = true;
            try {
                if (this.html5QrCode.getState() !== 1) { // 1 = is not scanning
                    await this.html5QrCode.stop();
                }
                await this.html5QrCode.clear();
            } catch (err) {
                console.error('Stop Error:', err);
            } finally {
                this.scanning = false;
                this.transitioning = false;
            }
        },

        async switchCamera() {
            if (this.availableCameras.length < 2) {
                // Jika list cuma 1 tapi user klik ganti, mungkin listnya perlu di-refresh
                this.availableCameras = await Html5Qrcode.getCameras();
                if (this.availableCameras.length < 2) return;
            }
            
            this.currentCameraIndex = (this.currentCameraIndex + 1) % this.availableCameras.length;
            
            this.transitioning = true;
            try {
                await this.stopCamera();
                // Tunggu sebentar agar hardware kamera lepas
                await new Promise(r => setTimeout(r, 500));
                await this.startCamera();
            } finally {
                this.transitioning = false;
            }
        },

        // === Shared Result Handler ===
        _handleResult(barcode) {
            barcode = barcode.trim();
            if (!barcode || barcode.length < 4) return;

            // Haptic feedback
            if (navigator.vibrate) navigator.vibrate([50, 30, 50]);

            // Dispatch ke caller via callback
            if (this.onScan) {
                this.onScan(barcode);
            }

            // Dispatch custom event juga (untuk Livewire listener)
            this.$dispatch('barcode-scanned', { barcode });
        },

        _playBeep() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = ctx.createOscillator();
                const gain = ctx.createGain();
                oscillator.connect(gain);
                gain.connect(ctx.destination);
                oscillator.frequency.value = 1800;
                oscillator.type = 'sine';
                gain.gain.value = 0.1; // Volume rendah
                oscillator.start();
                oscillator.stop(ctx.currentTime + 0.08); // 80ms beep
            } catch (e) { /* ignore */ }
        },

        // === Public: Reset State ===
        reset() {
            this.cameraError = '';
            this.scanBuffer = '';
            this.lastScanTime = 0;
            this.scanSuccess = false;
        }
    }));
});
