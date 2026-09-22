/**
 * Camera Controller Class for managing video streams, ROI overlay, and frame captures
 */
class CameraController {
  constructor(videoElementId, overlayCanvasId) {
    this.video = document.getElementById(videoElementId);
    this.overlayCanvas = document.getElementById(overlayCanvasId);
    this.ctx = this.overlayCanvas ? this.overlayCanvas.getContext('2d') : null;
    this.stream = null;
    this.fps = 0;
    this.frameCount = 0;
    this.lastFpsCalcTime = Date.now();
    this.isStreaming = false;

    // ROI Bounding Box ratio relative to video (default centered region)
    this.roi = {
      xRatio: 0.2,
      yRatio: 0.3,
      widthRatio: 0.6,
      heightRatio: 0.4,
    };

    this.initOverlay();
  }

  /**
   * Start Webcam Stream using getUserMedia
   */
  async startWebcam() {
    this.stopStream();
    try {
      this.stream = await navigator.mediaDevices.getUserMedia({
        video: { width: { ideal: 1280 }, height: { ideal: 720 } },
        audio: false,
      });

      this.video.srcObject = this.stream;
      await this.video.play();
      this.isStreaming = true;
      this.startOverlayLoop();

      return { success: true, mode: 'webcam' };
    } catch (err) {
      console.warn('Webcam stream unavailable, initializing fallback/mock canvas stream:', err.message);
      this.startMockStream();
      return { success: true, mode: 'mock', warning: err.message };
    }
  }

  /**
   * Start IP Camera Stream using HTTP / Motion JPEG URL
   */
  async startIpCamera(url) {
    this.stopStream();
    if (!url) {
      throw new Error('IP Camera Stream URL is required');
    }

    try {
      this.video.srcObject = null;
      this.video.src = url;
      await this.video.play();
      this.isStreaming = true;
      this.startOverlayLoop();
      return { success: true, mode: 'ipcam', url };
    } catch (err) {
      console.warn('IP Camera stream failed:', err.message);
      this.startMockStream();
      return { success: true, mode: 'mock', warning: err.message };
    }
  }

  /**
   * Fallback mock camera stream generator for environments without physical webcams
   */
  startMockStream() {
    this.stopStream();
    const mockCanvas = document.createElement('canvas');
    mockCanvas.width = 640;
    mockCanvas.height = 360;
    const mCtx = mockCanvas.getContext('2d');

    let counter = 0;
    this.mockTimer = setInterval(() => {
      counter++;
      // Background gradient
      const grad = mCtx.createLinearGradient(0, 0, 640, 360);
      grad.addColorStop(0, '#1e293b');
      grad.addColorStop(1, '#0f172a');
      mCtx.fillStyle = grad;
      mCtx.fillRect(0, 0, 640, 360);

      // Simulated truck / license plate frame
      mCtx.fillStyle = '#334155';
      mCtx.fillRect(120, 80, 400, 200);

      // Plate box
      mCtx.fillStyle = '#f8fafc';
      mCtx.fillRect(220, 180, 200, 60);
      mCtx.strokeStyle = '#000000';
      mCtx.lineWidth = 4;
      mCtx.strokeRect(220, 180, 200, 60);

      mCtx.fillStyle = '#000000';
      mCtx.font = 'bold 24px monospace';
      mCtx.textAlign = 'center';
      mCtx.fillText(`ABC-${1000 + (counter % 900)}`, 320, 218);
    }, 100);

    const mockStream = mockCanvas.captureStream(30);
    this.video.srcObject = mockStream;
    this.video.play();
    this.isStreaming = true;
    this.startOverlayLoop();
  }

  /**
   * Stop video stream & animation loops
   */
  stopStream() {
    this.isStreaming = false;
    if (this.animFrameId) {
      cancelAnimationFrame(this.animFrameId);
      this.animFrameId = null;
    }
    if (this.mockTimer) {
      clearInterval(this.mockTimer);
      this.mockTimer = null;
    }
    if (this.stream) {
      this.stream.getTracks().forEach((track) => track.stop());
      this.stream = null;
    }
    if (this.video) {
      this.video.srcObject = null;
      this.video.src = '';
    }
  }

  /**
   * Initialize canvas overlay sizing
   */
  initOverlay() {
    if (!this.overlayCanvas || !this.video) return;

    const resizeObserver = new ResizeObserver(() => {
      this.overlayCanvas.width = this.video.clientWidth || 640;
      this.overlayCanvas.height = this.video.clientHeight || 360;
    });

    resizeObserver.observe(this.video);
  }

  /**
   * Continuous loop drawing ROI bounding box and updating FPS
   */
  startOverlayLoop() {
    const draw = () => {
      if (!this.isStreaming) return;

      this.frameCount++;
      const now = Date.now();
      if (now - this.lastFpsCalcTime >= 1000) {
        this.fps = this.frameCount;
        this.frameCount = 0;
        this.lastFpsCalcTime = now;
        if (window.onFpsUpdate) window.onFpsUpdate(this.fps);
      }

      if (this.ctx && this.overlayCanvas) {
        const w = this.overlayCanvas.width;
        const h = this.overlayCanvas.height;

        this.ctx.clearRect(0, 0, w, h);

        // Calculate ROI bounding box coordinates
        const rx = w * this.roi.xRatio;
        const ry = h * this.roi.yRatio;
        const rw = w * this.roi.widthRatio;
        const rh = h * this.roi.heightRatio;

        // Draw ROI overlay bounding box
        this.ctx.strokeStyle = '#38bdf8'; // Accent blue
        this.ctx.lineWidth = 2;
        this.ctx.setLineDash([6, 6]);
        this.ctx.strokeRect(rx, ry, rw, rh);
        this.ctx.setLineDash([]);

        // Label for ROI
        this.ctx.fillStyle = '#38bdf8';
        this.ctx.font = '12px sans-serif';
        this.ctx.fillText('ROI Bounding Area', rx + 6, ry + 16);
      }

      this.animFrameId = requestAnimationFrame(draw);
    };

    draw();
  }

  /**
   * Capture base64 snapshot frame for OCR input
   * @returns {string} base64 data URL
   */
  captureFrameDataUrl() {
    const captureCanvas = document.createElement('canvas');
    const vw = this.video.videoWidth || 640;
    const vh = this.video.videoHeight || 360;

    captureCanvas.width = vw;
    captureCanvas.height = vh;

    const ctx = captureCanvas.getContext('2d');
    ctx.drawImage(this.video, 0, 0, vw, vh);

    return captureCanvas.toDataURL('image/jpeg', 0.85);
  }

  /**
   * Draw cropped ROI preview onto a destination canvas element
   */
  drawCroppedRoiToCanvas(destCanvas) {
    if (!destCanvas || !this.video) return;

    const vw = this.video.videoWidth || 640;
    const vh = this.video.videoHeight || 360;

    const sx = vw * this.roi.xRatio;
    const sy = vh * this.roi.yRatio;
    const sw = vw * this.roi.widthRatio;
    const sh = vh * this.roi.heightRatio;

    const dCtx = destCanvas.getContext('2d');
    dCtx.drawImage(this.video, sx, sy, sw, sh, 0, 0, destCanvas.width, destCanvas.height);
  }
}

window.CameraController = CameraController;
