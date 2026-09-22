# Production Industrial Weighbridge Application

A production-grade, fault-tolerant industrial Weighbridge desktop application built with Electron, embedded Laravel, SQLite, Spatie Roles & Permissions, Dynamic Form Builder, real-time Serial Port COM weight acquisition, and YOLO + Tesseract OCR camera plate detection.

## Tech Stack
- **Desktop Host**: Electron.js
- **Embedded Backend**: Laravel (running under embedded PHP server managed by `node-php-server` on `http://127.0.0.1:8000`)
- **Database**: SQLite stored dynamically in `app.getPath('userData')/database.sqlite`
- **Auth & Access Control**: Laravel Auth + Spatie Laravel-Permission (Admin, Operator, Supervisor)
- **Hardware Interfacing**: `serialport` and Readline parser in Electron Main Process, bridging via IPC
- **AI/LPR Engine**: ONNX Runtime Web (YOLO) + Tesseract.js

## Project Structure
```text
├── electron/
│   ├── main.js                  # App lifecycle & php-server spawner
│   ├── preload.js               # IPC bridge for hardware, serial & camera
│   ├── serial/
│   │   └── serialManager.js      # SerialPort logic & auto-reconnect
│   └── camera/
│       └── lprPipeline.js        # YOLO ONNX + Tesseract pipeline
├── laravel/                     # Embedded Laravel App
│   ├── app/
│   │   ├── Http/Controllers/    # Auth, Transaction, FormBuilder controllers
│   │   ├── Models/              # Transaction, FormField, Dynamic values
│   │   └── Services/            # Query & filtering engine
│   ├── database/
│   │   ├── migrations/          # SQLite schema & Spatie tables
│   │   └── seeders/             # Role & default admin user seeders
│   ├── resources/views/         # Blade templates for Dashboard, Forms, Reports
│   └── routes/web.php
├── package.json
└── README.md
```

## Running the Application
```bash
# Install Node dependencies
npm install

# Start Electron host & embedded Laravel server
npm start
```
