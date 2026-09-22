const { ipcMain } = require('electron');
const axios = require('axios');
const serialManager = require('../serial/serialManager');
const ocrPipeline = require('../camera/ocrPipeline');

/**
 * Axios instance with retry policy
 */
const apiHttpClient = axios.create({
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
  },
});

/**
 * Execute HTTP request with exponential backoff retry logic
 */
async function executeWithRetry(requestFn, retries = 3, delayMs = 1000) {
  try {
    return await requestFn();
  } catch (error) {
    if (retries <= 1) {
      throw error;
    }
    console.warn(`API request failed (${error.message}). Retrying in ${delayMs}ms...`);
    await new Promise((resolve) => setTimeout(resolve, delayMs));
    return executeWithRetry(requestFn, retries - 1, delayMs * 2);
  }
}

function registerIpcHandlers(mainWindow) {
  // --- Serial Port Handlers ---
  ipcMain.handle('serial:list', async () => {
    return await serialManager.listPorts();
  });

  ipcMain.handle('serial:connect', async (event, options) => {
    return await serialManager.connect(options);
  });

  ipcMain.handle('serial:disconnect', async () => {
    return serialManager.disconnect();
  });

  ipcMain.handle('serial:status', async () => {
    return serialManager.getStatus();
  });

  // Forward Serial Manager events to Renderer
  serialManager.on('weight', (data) => {
    if (mainWindow && !mainWindow.isDestroyed()) {
      mainWindow.webContents.send('serial:weight-data', data);
    }
  });

  serialManager.on('status', (data) => {
    if (mainWindow && !mainWindow.isDestroyed()) {
      mainWindow.webContents.send('serial:status-changed', data);
    }
  });

  serialManager.on('noise', (data) => {
    if (mainWindow && !mainWindow.isDestroyed()) {
      mainWindow.webContents.send('serial:noise', data);
    }
  });

  // --- OCR Pipeline Handlers ---
  ipcMain.handle('ocr:process', async (event, { imageInput, options }) => {
    return await ocrPipeline.processFrame(imageInput, options);
  });

  ipcMain.handle('ocr:set-threshold', async (event, threshold) => {
    ocrPipeline.setConfidenceThreshold(threshold);
    return { success: true, threshold };
  });

  // --- REST API Integration Handler ---
  ipcMain.handle('api:send-weighment', async (event, payload) => {
    const { endpointUrl, apiKey, data } = payload;
    if (!endpointUrl) {
      return { success: false, error: 'API endpoint URL is required' };
    }

    try {
      const response = await executeWithRetry(() =>
        apiHttpClient.post(endpointUrl, data, {
          headers: apiKey ? { Authorization: `Bearer ${apiKey}` } : {},
        })
      );
      return {
        success: true,
        status: response.status,
        data: response.data,
      };
    } catch (err) {
      console.error('API Send Error:', err.message);
      return {
        success: false,
        error: err.response ? `HTTP ${err.response.status}: ${JSON.stringify(err.response.data)}` : err.message,
      };
    }
  });
}

module.exports = {
  registerIpcHandlers,
};
