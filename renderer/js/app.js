/**
 * Main Renderer UI Application Logic (jQuery + Vanilla JS)
 */
$(document).ready(function () {
  let cameraController = null;
  let ocrIntervalTimer = null;
  let currentWeightData = { weight: 0, unit: 'KG', isStable: true, raw: '' };

  // --- 1. System Clock & Theme Toggle ---
  function updateClock() {
    const now = new Date();
    $('#systemTimeText').text(now.toLocaleTimeString());
  }
  setInterval(updateClock, 1000);
  updateClock();

  // Dark / Light Theme Toggle
  $('#themeToggleBtn').on('click', function () {
    const currentTheme = $('html').attr('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    $('html').attr('data-theme', newTheme);

    if (newTheme === 'dark') {
      $('#themeIcon').text('🌙');
      $(this).html('<span id="themeIcon">🌙</span> Dark Mode');
    } else {
      $('#themeIcon').text('☀️');
      $(this).html('<span id="themeIcon">☀️</span> Light Mode');
    }
  });

  // Toggle Settings Panel Drawer
  $('#settingsToggleBtn').on('click', function () {
    $('#settingsPanel').slideToggle(200);
  });

  // --- 2. Camera Controller Initialization ---
  cameraController = new CameraController('videoFeed', 'overlayCanvas');

  // FPS Callback
  window.onFpsUpdate = function (fps) {
    $('#camFpsText').text(fps);
  };

  async function initCamera() {
    const cameraType = $('#cameraTypeSelect').val();
    if (cameraType === 'webcam') {
      $('#ipCamUrlGroup').hide();
      $('#camSourceLabel').text('Webcam');
      await cameraController.startWebcam();
    } else {
      $('#ipCamUrlGroup').show();
      const streamUrl = $('#ipCamUrlInput').val().trim();
      $('#camSourceLabel').text('IP Camera');
      await cameraController.startIpCamera(streamUrl);
    }

    $('#cameraStatusBadge').removeClass('disconnected').addClass('connected');
    $('#cameraStatusText').text('CAMERA ACTIVE');
    $('#statusBarCameraText').text('STREAMING (30 FPS)');
  }

  initCamera();

  $('#cameraTypeSelect').on('change', function () {
    initCamera();
  });

  $('#ipCamUrlInput').on('change', function () {
    if ($('#cameraTypeSelect').val() === 'ipcam') {
      initCamera();
    }
  });

  // --- 3. Serial Port Management ---
  async function loadSerialPorts() {
    try {
      const ports = await window.electronAPI.listSerialPorts();
      const $select = $('#comPortSelect');
      $select.empty().append('<option value="">-- Select COM Port --</option>');

      ports.forEach((p) => {
        $select.append(`<option value="${p.path}">${p.path} (${p.manufacturer})</option>`);
      });
    } catch (err) {
      console.error('Error loading COM ports:', err);
    }
  }

  loadSerialPorts();
  $('#refreshPortsBtn').on('click', loadSerialPorts);

  // Connect Serial
  $('#connectSerialBtn').on('click', async function () {
    const portPath = $('#comPortSelect').val();
    const isMock = $('#mockSerialCheckbox').is(':checked');

    if (!portPath && !isMock) {
      alert('Please select a COM port or enable Mock Serial Indicator!');
      return;
    }

    const config = {
      path: portPath,
      baudRate: parseInt($('#baudRateSelect').val(), 10),
      parity: $('#paritySelect').val(),
      dataBits: parseInt($('#dataBitsSelect').val(), 10),
      stopBits: parseInt($('#stopBitsSelect').val(), 10),
      delimiter: $('#delimiterSelect').val(),
      mock: isMock,
    };

    try {
      const result = await window.electronAPI.connectSerialPort(config);
      updateSerialStatus('CONNECTED', isMock ? 'MOCK PORT' : portPath);
    } catch (err) {
      alert('Failed to connect to Serial Port: ' + err.message);
      updateSerialStatus('DISCONNECTED', '');
    }
  });

  // Disconnect Serial
  $('#disconnectSerialBtn').on('click', async function () {
    await window.electronAPI.disconnectSerialPort();
    updateSerialStatus('DISCONNECTED', '');
  });

  // Listen to IPC Serial Events
  window.electronAPI.onSerialWeight((data) => {
    currentWeightData = data;

    // Format numeric display
    const formattedWeight = String(Math.round(data.weight || 0)).padStart(6, '0');
    $('#weightDisplayValue').text(formattedWeight);
    $('#weightUnitDisplay').text((data.unit || 'KG').toUpperCase());
    $('#weightStabilityText').text(data.isStable ? 'STABLE' : 'UNSTABLE');
    $('#rawSerialText').text(data.raw || 'N/A');
  });

  window.electronAPI.onSerialStatusChanged((data) => {
    updateSerialStatus(data.status, data.config ? data.config.path : '');
  });

  window.electronAPI.onSerialNoise((data) => {
    updateSerialStatus('NOISY/UNSTABLE', 'NOISE DETECTED');
  });

  function updateSerialStatus(status, portName) {
    const $badge = $('#statusBarSerialBadge');
    const $text = $('#statusBarSerialText');
    const $label = $('#indicatorPortLabel');

    $badge.removeClass('connected noisy disconnected');

    if (status === 'CONNECTED') {
      $badge.addClass('connected');
      $text.text('CONNECTED');
      $label.text(`COM Port: ${portName || 'ACTIVE'}`);
    } else if (status === 'NOISY/UNSTABLE') {
      $badge.addClass('noisy');
      $text.text('NOISY / UNSTABLE');
      $label.text(`COM Port: NOISE WARNING`);
    } else {
      $badge.addClass('disconnected');
      $text.text('DISCONNECTED');
      $label.text('COM Port: Disconnected');
    }
  }

  // --- 4. OCR & LPR Pipeline Integration ---
  $('#confidenceSlider').on('input', function () {
    const val = $(this).val();
    $('#confidenceVal').text(val);
    window.electronAPI.setOCRConfidenceThreshold(parseInt(val, 10));
  });

  async function triggerOcrScan() {
    if (!cameraController) return;

    // Capture thumbnail on preview canvas
    const cropCanvas = document.getElementById('plateCropCanvas');
    cameraController.drawCroppedRoiToCanvas(cropCanvas);

    const frameDataUrl = cameraController.captureFrameDataUrl();
    const currentThreshold = parseInt($('#confidenceSlider').val(), 10);

    $('#lprStatusText').text('PROCESSING...');

    try {
      const result = await window.electronAPI.processOCRFrame(frameDataUrl, {
        confidenceThreshold: currentThreshold,
      });

      if (result.status === 'PLATE_DETECTED' && result.plateText) {
        $('#plateInput').val(result.plateText);
        $('#lprStatusText').text('PLATE_DETECTED').css('color', 'var(--accent-green)');
        $('#lprConfidenceText').text(`${result.confidence}%`);
      } else {
        $('#lprStatusText').text('PLATE_NOT_DETECTED').css('color', 'var(--accent-red)');
        $('#lprConfidenceText').text(`${result.confidence || 0}%`);
      }
    } catch (err) {
      console.error('OCR Processing error:', err);
      $('#lprStatusText').text('PLATE_NOT_DETECTED').css('color', 'var(--accent-red)');
    }
  }

  $('#triggerOcrBtn').on('click', triggerOcrScan);

  // Auto scan OCR every 3 seconds if feed is live
  ocrIntervalTimer = setInterval(() => {
    triggerOcrScan();
  }, 3000);

  // --- 5. Save / REST API Submit Transaction ---
  $('#submitTransactionBtn').on('click', async function () {
    const endpointUrl = $('#apiUrlInput').val().trim();
    const plateNumber = $('#plateInput').val().trim();

    if (!endpointUrl) {
      alert('Please configure REST API Backend Endpoint URL in settings!');
      return;
    }

    const payloadData = {
      timestamp: new Date().toISOString(),
      licensePlate: plateNumber,
      weight: currentWeightData.weight,
      unit: currentWeightData.unit,
      isStable: currentWeightData.isStable,
      rawSerial: currentWeightData.raw,
      ocrStatus: $('#lprStatusText').text(),
      ocrConfidence: $('#lprConfidenceText').text(),
    };

    $(this).prop('disabled', true).text('⏳ Sending...');

    try {
      const res = await window.electronAPI.sendWeighmentData({
        endpointUrl: endpointUrl,
        data: payloadData,
      });

      if (res.success) {
        alert(`✅ Weighment Transaction Saved Successfully! (HTTP ${res.status})`);
        $('#statusBarApiBadge').removeClass('disconnected').addClass('connected');
        $('#statusBarApiText').text('READY / OK');
      } else {
        alert(`⚠️ API Submission Failed: ${res.error}`);
        $('#statusBarApiBadge').removeClass('connected').addClass('disconnected');
        $('#statusBarApiText').text('API ERROR');
      }
    } catch (err) {
      alert(`❌ Error sending weighment data: ${err.message}`);
    } finally {
      $(this).prop('disabled', false).text('💾 Save Weighment');
    }
  });
});
