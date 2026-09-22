const { SerialPort } = require('serialport');
const { ReadlineParser } = require('@serialport/parser-readline');

class SerialManager {
    constructor(ipcBroadcastCallback) {
        this.ipcBroadcast = ipcBroadcastCallback;
        this.port = null;
        this.parser = null;
        this.status = 'DISCONNECTED';
        this.reconnectTimeout = null;
        this.reconnectDelay = 2000;
        this.lastReadTime = null;
        this.healthCheckInterval = null;
    }

    async autoConnect(preferredPath = null, baudRate = 9600) {
        this.clearReconnectTimer();

        try {
            const ports = await SerialPort.list();
            let targetPath = preferredPath;

            if (!targetPath && ports.length > 0) {
                targetPath = ports[0].path;
            }

            if (!targetPath) {
                this.updateStatus('DISCONNECTED');
                this.scheduleReconnect(preferredPath, baudRate);
                return;
            }

            this.port = new SerialPort({
                path: targetPath,
                baudRate: baudRate,
                autoOpen: false,
            });

            this.parser = this.port.pipe(new ReadlineParser({ delimiter: '\r\n' }));

            this.port.open((err) => {
                if (err) {
                    console.error('Serial port open error:', err.message);
                    this.updateStatus('DISCONNECTED');
                    this.scheduleReconnect(preferredPath, baudRate);
                    return;
                }

                this.updateStatus('CONNECTED');
                this.lastReadTime = Date.now();
                this.startHealthCheck();
            });

            this.parser.on('data', (line) => {
                this.lastReadTime = Date.now();
                this.updateStatus('CONNECTED');
                const cleanWeight = this.parseWeightString(line);
                if (cleanWeight !== null && this.ipcBroadcast) {
                    this.ipcBroadcast('serial:weight', {
                        raw: line,
                        weight: cleanWeight,
                        status: this.status,
                    });
                }
            });

            this.port.on('close', () => {
                this.updateStatus('DISCONNECTED');
                this.scheduleReconnect(preferredPath, baudRate);
            });

            this.port.on('error', (err) => {
                console.error('Serial error:', err.message);
                this.updateStatus('UNSTABLE');
            });

        } catch (e) {
            console.error('Serial autoConnect failed:', e.message);
            this.updateStatus('DISCONNECTED');
            this.scheduleReconnect(preferredPath, baudRate);
        }
    }

    parseWeightString(data) {
        if (!data) return null;
        // Clean non-numeric except decimal point and minus sign
        const match = data.toString().match(/-?\d+(\.\d+)?/);
        return match ? parseFloat(match[0]) : null;
    }

    updateStatus(newStatus) {
        if (this.status !== newStatus) {
            this.status = newStatus;
            if (this.ipcBroadcast) {
                this.ipcBroadcast('serial:status', this.status);
            }
        }
    }

    startHealthCheck() {
        if (this.healthCheckInterval) clearInterval(this.healthCheckInterval);
        this.healthCheckInterval = setInterval(() => {
            if (this.status === 'CONNECTED' && this.lastReadTime) {
                const elapsed = Date.now() - this.lastReadTime;
                if (elapsed > 5000) {
                    this.updateStatus('UNSTABLE');
                }
            }
        }, 3000);
    }

    scheduleReconnect(preferredPath, baudRate) {
        this.clearReconnectTimer();
        this.reconnectTimeout = setTimeout(() => {
            console.log('Attempting serial reconnect...');
            this.autoConnect(preferredPath, baudRate);
        }, this.reconnectDelay);
    }

    clearReconnectTimer() {
        if (this.reconnectTimeout) {
            clearTimeout(this.reconnectTimeout);
            this.reconnectTimeout = null;
        }
        if (this.healthCheckInterval) {
            clearInterval(this.healthCheckInterval);
            this.healthCheckInterval = null;
        }
    }

    close() {
        this.clearReconnectTimer();
        if (this.port && this.port.isOpen) {
            this.port.close();
        }
        this.updateStatus('DISCONNECTED');
    }
}

module.exports = SerialManager;
