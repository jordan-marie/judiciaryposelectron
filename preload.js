const { contextBridge, ipcRenderer } = require('electron');

contextBridge.exposeInMainWorld('electronAPI', {
  // Serial Port APIs
  listSerialPorts: () => ipcRenderer.invoke('serial:list'),
  connectSerialPort: (options) => ipcRenderer.invoke('serial:connect', options),
  disconnectSerialPort: () => ipcRenderer.invoke('serial:disconnect'),
  getSerialStatus: () => ipcRenderer.invoke('serial:status'),

  // Serial Event Listeners
  onSerialWeight: (callback) => {
    const handler = (event, data) => callback(data);
    ipcRenderer.on('serial:weight-data', handler);
    return () => ipcRenderer.removeListener('serial:weight-data', handler);
  },
  onSerialStatusChanged: (callback) => {
    const handler = (event, data) => callback(data);
    ipcRenderer.on('serial:status-changed', handler);
    return () => ipcRenderer.removeListener('serial:status-changed', handler);
  },
  onSerialNoise: (callback) => {
    const handler = (event, data) => callback(data);
    ipcRenderer.on('serial:noise', handler);
    return () => ipcRenderer.removeListener('serial:noise', handler);
  },

  // OCR Pipeline APIs
  processOCRFrame: (imageInput, options) => ipcRenderer.invoke('ocr:process', { imageInput, options }),
  setOCRConfidenceThreshold: (threshold) => ipcRenderer.invoke('ocr:set-threshold', threshold),

  // REST API Endpoint Trigger
  sendWeighmentData: (payload) => ipcRenderer.invoke('api:send-weighment', payload),
});
