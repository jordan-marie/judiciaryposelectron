const { app, BrowserWindow } = require('electron');
const path = require('path');
const { registerIpcHandlers } = require('./ipc/handlers');
const ocrPipeline = require('./camera/ocrPipeline');

let mainWindow = null;

function createWindow() {
  mainWindow = new BrowserWindow({
    width: 1440,
    height: 900,
    minWidth: 1024,
    minHeight: 720,
    title: 'Industrial Weighbridge Application',
    webPreferences: {
      preload: path.join(__dirname, '../preload.js'),
      nodeIntegration: false,
      contextIsolation: true,
      sandbox: false,
    },
    backgroundColor: '#111827', // Industrial Dark Theme Default background
  });

  mainWindow.loadFile(path.join(__dirname, '../renderer/index.html'));

  // Register IPC Communication Channels
  registerIpcHandlers(mainWindow);

  // Initialize OCR Pipeline in background
  ocrPipeline.init().catch((err) => {
    console.warn('OCR Pipeline lazy init error:', err);
  });

  mainWindow.on('closed', () => {
    mainWindow = null;
  });
}

app.whenReady().then(() => {
  createWindow();

  app.on('activate', () => {
    if (BrowserWindow.getAllWindows().length === 0) {
      createWindow();
    }
  });
});

app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') {
    app.quit();
  }
});

// Global exception handler
process.on('uncaughtException', (err) => {
  console.error('Uncaught Exception in Main Process:', err);
});
