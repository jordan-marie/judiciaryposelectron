const electron = require("electron");
const ipc = electron.ipcRenderer;
const { contextBridge, ipcRenderer } = require("electron");

let displayInfo = () => {
    ipc.send("mainWindowLoaded")
    /*ipc.on("resultSent", function(evt, result){
        let resultEl = document.getElementById("result");
        console.log(result);
        for(var i = 0; i < result.length;i++){
            resultEl.innerHTML += "First Name: " + result[i].name.toString() + " - Last name: " +result[i].surname.toString()+ "<br/>";
        }
    });*/

    ipc.on("resultVersion", function(evt, result){
        let resultEl = document.getElementById("result-version");
        resultEl.innerHTML = result;
    });
}

let getSettings = () => {
    ipc.send("settingsWindowLoaded")
    ipc.on("get:settings", function(evt, result){
        let i = 0;
        while (i < result.length) {
            //console.log(result[i]);
            document.getElementById(result[i].name).value = result[i].value;
            i++;
        }
    });
}

let bridge = {
    updateMessage: displayInfo,
    settings: getSettings
};

contextBridge.exposeInMainWorld("bridge", bridge);

//important code to access ipcRenderer on external html or js (at rendered level)
contextBridge.exposeInMainWorld("ipcRenderer", {
    send: (channel, data) => ipcRenderer.send(channel, data),
    on: (channel, func) => ipcRenderer.on(channel, (event, ...args) => func(...args))
});