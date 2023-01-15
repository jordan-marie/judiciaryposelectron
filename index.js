const { app, BrowserWindow, ipcMain } = require("electron");
const path = require('path');
const url = require('url');

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
        width: 800, 
        show: false,
        webPreferences: {
            nodeIntegration: true,
            contextIsolation: false
        },
    })
	mainWindow.loadURL(url.format({
		pathname: path.join(__dirname, 'html_theme/flat-able-lite/dist/index.html'),
		protocol: 'file',
		slashes: true
	}));
	mainWindow.once("ready-to-show", () => { mainWindow.show() })

	ipcMain.on("mainWindowLoaded", function () {
		let result = knex.select("*").from("user");
		result.then(function(rows){
			mainWindow.webContents.send("resultSent", rows);
		})
	});
});



app.on("window-all-closed", () => { app.quit() });