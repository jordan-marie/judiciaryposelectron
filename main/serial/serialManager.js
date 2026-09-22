const { SerialPort } = require('serialport');
const { ReadlineParser } = require('@serialport/parser-readline');
const EventEmitter = require('events');

class SerialManager extends EventEmitter {
  constructor() {
    super();
    this.port = null;
    this.parser = null;
    this.status = 'DISCONNECTED'; // CONNECTED, NOISY/UNSTABLE, DISCONNECTED
    this.config = {
      path: '',
      baudRate: 9600,
      dataBits: 8,
      stopBits: 1,
      parity: 'none',
      delimiter: '\r\n',
      mock: false,
    };

    this.reconnectTimer = null;
    this.reconnectAttempts = 0;
    this.maxBackoffMs = 30000;
    this.initialBackoffMs = 1000;
    this.mockInterval = null;
    this.lastValidWeightTime = Date.now();
    this.noiseTimeout = null;
  }

  /**
   * String sanitizer to strip control characters and parse floating-point weight numbers
   * Handles strings like "\x02ST,GS,+012450kg\x03", "\x0212345.5 kg\x03", etc.
   * @param {string|Buffer} rawData
   * @returns {{ raw: string, weight: number|null, unit: string, isStable: boolean, valid: boolean }}
   */
  sanitizeAndParse(rawData) {
    if (rawData === null || rawData === undefined) {
      return { raw: '', weight: null, unit: 'kg', isStable: false, valid: false };
    }

    const str = rawData.toString();
    // Clean control characters (ASCII 0-31 except common formatting if needed, and ASCII 127)
    const cleaned = str.replace(/[\x00-\x1F\x7F]/g, '').trim();

    // Check for stability flags if common indicator formats are used (ST = stable, US = unstable)
    const isStable = !str.includes('US') && !cleaned.toLowerCase().includes('unstable');

    // Extract numbers with optional sign and decimal point
    // Matches patterns like +012450, -120.5, 12450
    const match = cleaned.match(/([+-]?\d+(?:\.\d+)?)/);

    if (match) {
      const weightNum = parseFloat(match[1]);
      if (!isNaN(weightNum)) {
        // Detect unit if present
        let unit = 'kg';
        if (/lbs?/i.test(cleaned)) unit = 'lb';
        else if (/t/i.test(cleaned) && !/st/i.test(cleaned)) unit = 't';

        return {
          raw: str,
          cleaned: cleaned,
          weight: weightNum,
          unit: unit,
          isStable: isStable,
          valid: true,
        };
      }
    }

    return {
      raw: str,
      cleaned: cleaned,
      weight: null,
      unit: 'kg',
      isStable: false,
      valid: false,
    };
  }

  /**
   * List all available COM / Serial ports
   */
  async listPorts() {
    try {
      const ports = await SerialPort.list();
      return ports.map(p => ({
        path: p.path,
        manufacturer: p.manufacturer || 'Unknown',
        serialNumber: p.serialNumber,
        pnpId: p.pnpId,
      }));
    } catch (err) {
      this.emit('error', `Failed to list ports: ${err.message}`);
      return [];
    }
  }

  /**
   * Connect to specified COM port or start mock mode
   * @param {Object} options
   */
  async connect(options = {}) {
    this.disconnect();

    this.config = { ...this.config, ...options };

    if (this.config.mock) {
      this.startMockMode();
      return { success: true, message: 'Mock serial mode started' };
    }

    if (!this.config.path) {
      this.updateStatus('DISCONNECTED');
      throw new Error('Port path is required when not in mock mode');
    }

    return new Promise((resolve, reject) => {
      try {
        // Parse delimiter string into actual character if escaped
        let delim = this.config.delimiter || '\r\n';
        delim = delim.replace(/\\r/g, '\r').replace(/\\n/g, '\n');

        this.port = new SerialPort({
          path: this.config.path,
          baudRate: parseInt(this.config.baudRate, 10) || 9600,
          dataBits: parseInt(this.config.dataBits, 10) || 8,
          stopBits: parseInt(this.config.stopBits, 10) || 1,
          parity: this.config.parity || 'none',
          autoOpen: false,
        });

        this.parser = this.port.pipe(new ReadlineParser({ delimiter: delim }));

        this.port.open((err) => {
          if (err) {
            this.updateStatus('DISCONNECTED');
            this.scheduleReconnect();
            return reject(err);
          }

          this.reconnectAttempts = 0;
          this.updateStatus('CONNECTED');

          this.parser.on('data', (data) => this.handleData(data));

          this.port.on('error', (err) => {
            this.emit('error', err.message);
            this.updateStatus('NOISY/UNSTABLE');
            this.scheduleReconnect();
          });

          this.port.on('close', () => {
            if (this.status !== 'DISCONNECTED') {
              this.updateStatus('DISCONNECTED');
              this.scheduleReconnect();
            }
          });

          resolve({ success: true, message: `Connected to ${this.config.path}` });
        });
      } catch (error) {
        this.updateStatus('DISCONNECTED');
        this.scheduleReconnect();
        reject(error);
      }
    });
  }

  /**
   * Handle incoming raw serial line
   */
  handleData(data) {
    const parsed = this.sanitizeAndParse(data);
    if (parsed.valid) {
      this.lastValidWeightTime = Date.now();
      if (this.status === 'NOISY/UNSTABLE') {
        this.updateStatus('CONNECTED');
      }
      this.emit('weight', parsed);
    } else {
      // Noise / garbage received
      this.updateStatus('NOISY/UNSTABLE');
      this.emit('noise', { raw: data, message: 'Unparseable noise or control character' });

      // Auto-recover to CONNECTED after 3 seconds if clean data resumes
      clearTimeout(this.noiseTimeout);
      this.noiseTimeout = setTimeout(() => {
        if (this.port && this.port.isOpen) {
          this.updateStatus('CONNECTED');
        }
      }, 3000);
    }
  }

  /**
   * Schedule exponential backoff reconnect
   */
  scheduleReconnect() {
    if (this.config.mock) return;
    if (this.reconnectTimer) clearTimeout(this.reconnectTimer);

    const backoffMs = Math.min(
      this.initialBackoffMs * Math.pow(2, this.reconnectAttempts),
      this.maxBackoffMs
    );
    this.reconnectAttempts++;

    this.emit('log', `Reconnecting in ${backoffMs / 1000}s (Attempt ${this.reconnectAttempts})...`);

    this.reconnectTimer = setTimeout(() => {
      if (this.config.path) {
        this.connect().catch(() => {});
      }
    }, backoffMs);
  }

  /**
   * Update internal status and emit change event
   */
  updateStatus(newStatus) {
    if (this.status !== newStatus) {
      this.status = newStatus;
      this.emit('status', { status: this.status, config: this.config });
    }
  }

  /**
   * Start mock serial data emission for test/demo mode
   */
  startMockMode() {
    this.stopMockMode();
    this.updateStatus('CONNECTED');

    let mockWeight = 12450;
    let step = 10;

    this.mockInterval = setInterval(() => {
      // Simulate slight variation in weight
      mockWeight += (Math.random() - 0.5) * step;
      if (mockWeight < 0) mockWeight = 0;

      const rawString = `\x02ST,GS,+${Math.round(mockWeight).toString().padStart(6, '0')}kg\x03\r\n`;
      const parsed = this.sanitizeAndParse(rawString);

      this.emit('weight', parsed);
    }, 500);
  }

  stopMockMode() {
    if (this.mockInterval) {
      clearInterval(this.mockInterval);
      this.mockInterval = null;
    }
  }

  /**
   * Disconnect port and stop timers
   */
  disconnect() {
    if (this.reconnectTimer) {
      clearTimeout(this.reconnectTimer);
      this.reconnectTimer = null;
    }

    this.stopMockMode();

    if (this.port && this.port.isOpen) {
      try {
        this.port.close();
      } catch (err) {
        // Ignore close error
      }
    }

    this.port = null;
    this.parser = null;
    this.updateStatus('DISCONNECTED');
    return { success: true };
  }

  getStatus() {
    return {
      status: this.status,
      config: this.config,
    };
  }
}

module.exports = new SerialManager();
