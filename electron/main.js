const { app, BrowserWindow, ipcMain } = require('electron');
const path = require('path');
const fs = require('fs');
const { execSync } = require('child_process');
const phpServer = require('node-php-server');

const SerialManager = require('./serial/serialManager');
const LPRPipeline = require('./camera/lprPipeline');

let mainWindow = null;
let serialManager = null;
let lprPipeline = null;
const SERVER_PORT = 8000;
const SERVER_HOST = '127.0.0.1';

function initializeDatabaseAndMigrations() {
    const userDataPath = app.getPath('userData');
    const dbPath = path.join(userDataPath, 'database.sqlite');
    const isNewDb = !fs.existsSync(dbPath);

    if (isNewDb) {
        fs.writeFileSync(dbPath, '');
        console.log('Created new SQLite database at:', dbPath);
    }

    const laravelEnvPath = path.join(__dirname, '../laravel/.env');
    let envContent = '';
    if (fs.existsSync(laravelEnvPath)) {
        envContent = fs.readFileSync(laravelEnvPath, 'utf8');
    } else {
        const envExamplePath = path.join(__dirname, '../laravel/.env.example');
        if (fs.existsSync(envExamplePath)) {
            envContent = fs.readFileSync(envExamplePath, 'utf8');
        }
    }

    // Force SQLite DB path in .env to userData directory
    envContent = envContent.replace(/^DB_CONNECTION=.*$/m, 'DB_CONNECTION=sqlite');
    if (envContent.includes('DB_DATABASE=')) {
        envContent = envContent.replace(/^DB_DATABASE=.*$/m, `DB_DATABASE="${dbPath}"`);
    } else {
        envContent += `\nDB_DATABASE="${dbPath}"\n`;
    }
    fs.writeFileSync(laravelEnvPath, envContent);

    // Run migrations on app startup
    try {
        console.log('Running artisan migrate --force...');
        const laravelPath = path.join(__dirname, '../laravel');
        execSync(`php artisan migrate --force`, { cwd: laravelPath, stdio: 'inherit' });
        if (isNewDb) {
            console.log('Seeding initial roles & default admin...');
            execSync(`php artisan db:seed --force`, { cwd: laravelPath, stdio: 'inherit' });
        }
    } catch (e) {
        console.error('Error executing artisan migrate:', e.message);
    }
}

function startLaravelServer(callback) {
    const laravelPublicPath = path.join(__dirname, '../laravel/public');

    phpServer.createServer({
        port: SERVER_PORT,
        hostname: SERVER_HOST,
        base: laravelPublicPath,
        router: path.join(__dirname, '../laravel/server.php'),
        bin: 'php'
    });

    console.log(`Embedded Laravel server running at http://${SERVER_HOST}:${SERVER_PORT}`);
    setTimeout(callback, 1500);
}

function createWindow() {
    mainWindow = new BrowserWindow({
        width: 1400,
        height: 900,
        minWidth: 1024,
        minHeight: 720,
        title: 'Industrial Weighbridge Application',
        webPreferences: {
            preload: path.join(__dirname, 'preload.js'),
            nodeIntegration: false,
            contextIsolation: true,
        },
    });

    mainWindow.loadURL(`http://${SERVER_HOST}:${SERVER_PORT}`);

    mainWindow.on('closed', () => {
        mainWindow = null;
    });
}

app.whenReady().then(() => {
    initializeDatabaseAndMigrations();

    startLaravelServer(() => {
        createWindow();

        // Initialize SerialPort weight reader
        serialManager = new SerialManager((event, data) => {
            if (mainWindow && !mainWindow.isDestroyed()) {
                mainWindow.webContents.send(event, data);
            }
        });
        serialManager.autoConnect();

        // Initialize LPR pipeline
        lprPipeline = new LPRPipeline();

        // IPC Handlers
        ipcMain.handle('camera:scan-plate', async (event, imageData) => {
            if (lprPipeline) {
                return await lprPipeline.processImage(imageData);
            }
            return { status: 'PLATE_NOT_DETECTED', plate: null, confidence: 0 };
        });

        ipcMain.handle('serial:get-status', () => {
            return serialManager ? serialManager.status : 'DISCONNECTED';
        });
    });
});

app.on('window-all-closed', () => {
    if (serialManager) serialManager.close();
    if (lprPipeline) lprPipeline.destroy();
    phpServer.close();
    if (process.platform !== 'darwin') {
        app.quit();
    }
});
