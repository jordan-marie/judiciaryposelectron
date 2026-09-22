const { contextBridge, ipcRenderer } = require('electron');

contextBridge.exposeInMainWorld('electronAPI', {
    onWeightData: (callback) => {
        ipcRenderer.on('serial:weight', (event, value) => callback(value));
    },
    onSerialStatus: (callback) => {
        ipcRenderer.on('serial:status', (event, value) => callback(value));
    },
    scanLicensePlate: (imageData) => {
        return ipcRenderer.invoke('camera:scan-plate', imageData);
    },
    getSerialStatus: () => {
        return ipcRenderer.invoke('serial:get-status');
    }
});
