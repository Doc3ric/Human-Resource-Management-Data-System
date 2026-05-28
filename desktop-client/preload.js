const { contextBridge, ipcRenderer } = require('electron');

contextBridge.exposeInMainWorld('api', {
    saveConfig: (url) => ipcRenderer.send('save-config', url),
    requestCurrentUrl: () => ipcRenderer.invoke('get-current-url'),
    clearCache: () => ipcRenderer.send('clear-cache')
});
