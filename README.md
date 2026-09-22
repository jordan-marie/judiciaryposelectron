# Fault-Tolerant Industrial Weighbridge Application (Electron + Node.js)

A production-grade, fault-tolerant industrial Weighbridge desktop application built with Electron, Vanilla JavaScript, jQuery, Node.js native hardware interfacing (`serialport`), and an ONNX/Tesseract.js OCR engine for automated License Plate Recognition (LPR).

---

## Architecture & Project Structure

```text
├── main/
│   ├── index.js             # Electron main process entry point & window manager
│   ├── serial/
│   │   └── serialManager.js # SerialPort wrapper with auto-reconnect & string sanitization
│   ├── camera/
│   │   └── ocrPipeline.js   # YOLO ONNX detection + Tesseract.js OCR engine
│   └── ipc/
│       └── handlers.js      # Main <-> Renderer IPC channels & REST API client
├── renderer/
│   ├── index.html           # Main industrial display dashboard UI
│   ├── css/
│   │   └── app.css          # Industrial dashboard styling (Dark/Light mode)
│   ├── js/
│   │   ├── app.js           # Main UI logic (jQuery / Vanilla JS)
│   │   └── cameraController.js # Video stream management & ROI bounding box overlay
│   └── models/              # YOLO ONNX weights directory placeholder
├── test/
│   └── run-tests.js         # Integration and unit test suite
├── package.json
└── README.md
```

---

## Core Features

### 1. Robust Serial Port Engine (Weight Indicator)
* **ComPort Management**: Dynamic COM port listing, configurable Baud rates (9600, 19200, 38400, 115200), Parity, Data bits, Stop bits, and Delimiters (`\r\n`, `\n`, `\r`).
* **Continuous Background Reading**: Uses `@serialport/parser-readline` for continuous streaming without blocking UI thread.
* **Fault Tolerance & Sanitization**: Strips ASCII control characters (STX/ETX, `\x02`, `\x03`, headers `ST,GS,`) and extracts clean floating-point weight numbers. Includes exponential backoff auto-reconnect when hardware disconnects.
* **Status Indicators**: `CONNECTED` (Green), `NOISY/UNSTABLE` (Yellow), `DISCONNECTED` (Red).
* **Simulation Mode**: Built-in mock serial indicator for testing without physical weight indicators.

### 2. Camera & LPR Module
* **Camera Streams**: Supports HTML5 Webcams (`getUserMedia`) and IP Cameras (RTSP/HTTP stream URLs).
* **Hybrid OCR Engine**: YOLO ONNX model bounding box extraction + Tesseract.js character recognition.
* **Graceful Fallbacks**: Flags stream status as `PLATE_NOT_DETECTED` when confidence is below threshold (< 70%). Supports manual plate number override on screen.
* **Tuning Controls**: Interactive confidence slider and ROI bounding box area configuration.

### 3. UI & Real-Time Display
* **High-Visibility Digital Meter**: Glowing digital display with responsive typography.
* **Status Bar**: Real-time hardware status indicators for COM Port, Camera Feed FPS, and REST API connection state.
* **Theme Switching**: Dark and Light industrial layout themes with theme preference state.
* **REST API Integration**: Exponential backoff retry policies for sending weighment transactions to backend servers.

---

## Setup & Running

### Prerequisites
* Node.js v18+ & npm

### Installation
```bash
npm install
```

### Running Tests
```bash
npm test
```

### Starting Application
```bash
npm start
```
