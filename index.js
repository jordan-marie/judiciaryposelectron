const { app, BrowserWindow, ipcMain } = require("electron");
const path = require('path');
const url = require('url');
const { autoUpdater, AppUpdater } = require("electron-updater");
let curWindow;

//Basic flags
autoUpdater.autoDownload = false;
autoUpdater.autoInstallOnAppQuit = true;

var knex = require("knex")({
	client: "sqlite3",
	connection: {
		filename: path.join(__dirname, 'database/database.sqlite')
	},
    useNullAsDefault: true
});

app.on("ready", () => {
	let mainWindow = new BrowserWindow({ 
        height: 800, 
        width: 2000, 
        show: false,
        webPreferences: {
            nodeIntegration: true,
            contextIsolation: true,
			preload: path.join(__dirname, "preload.js")
        },
    });

	mainWindow.loadURL(url.format({
		pathname: path.join(__dirname, 'html_theme/flat-able-lite/dist/index.html'),
		protocol: 'file',
		slashes: true
	}));
	mainWindow.once("ready-to-show", () => { mainWindow.show(); });

	autoUpdater.checkForUpdates();

	ipcMain.on("mainWindowLoaded", function () {
		let result = knex.select("*").from("user");
		result.then(function(rows){
			mainWindow.webContents.send("resultSent", rows);
		});
	});

	ipcMain.on("settingsWindowLoaded", function () {
		let getSettings = knex.select("*").from("settings");
		getSettings.then(function(rows){
			mainWindow.webContents.send("get:settings", rows);
		});
	});

	/*New Update Available*/
	autoUpdater.on("update-available", (info) => {
		mainWindow.webContents.send("resultVersion", 'Update Available');
		autoUpdater.downloadUpdate();
	});

	autoUpdater.on("update-not-available", (info) => {
		mainWindow.webContents.send("resultVersion", 'NO Update Available');
	});

	/*Download Completion Message*/
	autoUpdater.on("update-downloaded", (info) => {
		mainWindow.webContents.send("resultVersion", 'Downloading updates...');
	});

	autoUpdater.on("error", (info) => {
		mainWindow.webContents.send("resultVersion", 'Something Goes wrong');
	});

	//save api data
	ipcMain.on("api:save", (e, result) => {
		console.log(result.name);
		knex('settings').where({ name: result.name }).update({
			value: result.value
		}, ['name', 'value']).then(() => {
			return 1;
		});
	});
});

//Global exception handler
process.on("uncaughtException", function (err) {
console.log(err);
});

app.on("window-all-closed", function () {
if (process.platform != "darwin") app.quit();
});