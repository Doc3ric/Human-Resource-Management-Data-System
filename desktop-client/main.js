const { app, BrowserWindow, ipcMain, Menu, MenuItem, dialog, shell } = require('electron');
const path = require('path');
const fs = require('fs');

let mainWindow;
let splashWindow;
let currentServerUrl = 'http://localhost:8000';

function getConfigPath() {
  return path.join(app.getPath('userData'), 'user-config.json');
}

function loadSavedUrl() {
  const userConfig = getConfigPath();
  if (fs.existsSync(userConfig)) {
    try {
      const config = JSON.parse(fs.readFileSync(userConfig, 'utf8'));
      if (config.serverUrl) return config.serverUrl;
    } catch (e) {
      console.error("Could not load user config:", e);
    }
  }

  // Fallback to packed config if user config doesn't exist
  let configPath = path.join(process.resourcesPath, 'config.json');
  if (!fs.existsSync(configPath)) {
    configPath = path.join(__dirname, 'config.json');
  }
  try {
    if (fs.existsSync(configPath)) {
      const config = JSON.parse(fs.readFileSync(configPath, 'utf8'));
      if (config.serverUrl) return config.serverUrl;
    }
  } catch (error) {
    console.error("Could not load config.json:", error);
  }

  return 'http://localhost:8000';
}

function createWindow() {
  currentServerUrl = loadSavedUrl();

  splashWindow = new BrowserWindow({
    width: 500,
    height: 380,
    transparent: true,
    frame: false,
    alwaysOnTop: true,
    icon: path.join(__dirname, 'icon.ico')
  });

  splashWindow.loadFile(path.join(__dirname, 'splash.html'));

  mainWindow = new BrowserWindow({
    width: 1280,
    height: 800,
    title: "HDMS- Human Resource Data Management System",
    autoHideMenuBar: true,
    show: false,
    icon: path.join(__dirname, 'icon.ico'),
    webPreferences: {
      nodeIntegration: false,
      contextIsolation: true,
      preload: path.join(__dirname, 'preload.js')
    }
  });

  mainWindow.maximize();
  mainWindow.setMenu(null);

  // Restore quality-of-life shortcuts since the menu is disabled
  mainWindow.webContents.on('before-input-event', (event, input) => {
    if (input.type === 'keyDown') {
      const isCtrl = input.control;
      const isAlt = input.alt;
      const key = input.key.toLowerCase();

      // Refresh: F5 or Ctrl+R
      if (key === 'f5' || (isCtrl && key === 'r')) {
        mainWindow.reload();
        event.preventDefault();
      }

      // Print: Ctrl+P
      if (isCtrl && key === 'p') {
        mainWindow.webContents.print();
        event.preventDefault();
      }

      // Zoom In: Ctrl++ or Ctrl+=
      if (isCtrl && (key === '+' || key === '=')) {
        let level = mainWindow.webContents.getZoomLevel();
        mainWindow.webContents.setZoomLevel(level + 0.5);
        event.preventDefault();
      }

      // Zoom Out: Ctrl+-
      if (isCtrl && key === '-') {
        let level = mainWindow.webContents.getZoomLevel();
        mainWindow.webContents.setZoomLevel(level - 0.5);
        event.preventDefault();
      }

      // Zoom Reset: Ctrl+0
      if (isCtrl && key === '0') {
        mainWindow.webContents.setZoomLevel(0);
        event.preventDefault();
      }

      // Navigation Back: Alt+Left Arrow
      if (isAlt && key === 'arrowleft') {
        if (mainWindow.webContents.canGoBack()) {
          mainWindow.webContents.goBack();
        }
        event.preventDefault();
      }

      // Navigation Forward: Alt+Right Arrow
      if (isAlt && key === 'arrowright') {
        if (mainWindow.webContents.canGoForward()) {
          mainWindow.webContents.goForward();
        }
        event.preventDefault();
      }
    }
  });

  // Ctrl + Scroll Wheel zoom
  mainWindow.webContents.on('zoom-changed', (event, zoomDirection) => {
    let level = mainWindow.webContents.getZoomLevel();
    if (zoomDirection === 'in') {
      mainWindow.webContents.setZoomLevel(level + 0.5);
    } else if (zoomDirection === 'out') {
      mainWindow.webContents.setZoomLevel(level - 0.5);
    }
  });

  // Add Basic Right-Click Context Menu (Copy/Paste)
  mainWindow.webContents.on('context-menu', (event, params) => {
    const menu = new Menu();

    if (params.isEditable) {
      menu.append(new MenuItem({ role: 'undo' }));
      menu.append(new MenuItem({ role: 'redo' }));
      menu.append(new MenuItem({ type: 'separator' }));
      menu.append(new MenuItem({ role: 'cut' }));
      menu.append(new MenuItem({ role: 'copy' }));
      menu.append(new MenuItem({ role: 'paste' }));
      menu.append(new MenuItem({ type: 'separator' }));
      menu.append(new MenuItem({ role: 'selectAll' }));
    } else if (params.selectionText && params.selectionText.trim().length > 0) {
      menu.append(new MenuItem({ role: 'copy' }));
      menu.append(new MenuItem({ type: 'separator' }));
      menu.append(new MenuItem({ role: 'selectAll' }));
    } else {
      menu.append(new MenuItem({ label: 'Reload Page', click: () => mainWindow.reload() }));
      menu.append(new MenuItem({ label: 'Print...', click: () => mainWindow.webContents.print() }));
    }

    menu.popup(mainWindow, params.x, params.y);
  });

  // Handle File Downloads (Excel/PDF Exports)
  mainWindow.webContents.session.on('will-download', (event, item, webContents) => {
    // This ensures a Save As dialog appears
    item.setSaveDialogOptions({
      defaultPath: path.join(app.getPath('downloads'), item.getFilename())
    });

    item.once('done', (event, state) => {
      if (state === 'completed') {
        // Ask user what to do with the downloaded file
        const result = dialog.showMessageBoxSync(mainWindow, {
          type: 'info',
          title: 'Download Complete',
          message: `Successfully saved: ${item.getFilename()}`,
          buttons: ['Open File', 'Open Folder', 'Close'],
          defaultId: 0
        });

        if (result === 0) {
          shell.openPath(item.getSavePath());
        } else if (result === 1) {
          shell.showItemInFolder(item.getSavePath());
        }
      } else if (state === 'interrupted') {
        dialog.showMessageBoxSync(mainWindow, {
          type: 'error',
          title: 'Download Failed',
          message: `The download was interrupted.`,
          buttons: ['OK']
        });
      }
    });
  });

  mainWindow.loadURL(currentServerUrl).catch(err => {
    console.error('Failed to load URL:', err);
    mainWindow.loadFile(path.join(__dirname, 'fallback.html'));
  });

  mainWindow.once('ready-to-show', () => {
    if (splashWindow) {
      splashWindow.close();
      splashWindow = null;
    }
    mainWindow.show();
  });

  // Fallback in case the server is down or the IP is wrong
  mainWindow.webContents.on('did-fail-load', (event, errorCode, errorDescription, validatedURL, isMainFrame) => {
    if (!isMainFrame) return; // Only catch main frame failures
    if (errorCode === -3) return; // Ignore aborted loads

    mainWindow.loadFile(path.join(__dirname, 'fallback.html'));
  });

  mainWindow.webContents.on('did-fail-provisional-load', (event, errorCode, errorDescription, validatedURL, isMainFrame) => {
    if (!isMainFrame) return;
    if (errorCode === -3) return;

    mainWindow.loadFile(path.join(__dirname, 'fallback.html'));
  });

  mainWindow.on('closed', function () {
    mainWindow = null;
  });
}

ipcMain.on('save-config', (event, newUrl) => {
  currentServerUrl = newUrl;
  const userConfigPath = getConfigPath();

  try {
    fs.writeFileSync(userConfigPath, JSON.stringify({ serverUrl: newUrl }, null, 2));
  } catch (error) {
    console.error("Failed to save config:", error);
  }

  if (mainWindow) {
    mainWindow.loadURL(currentServerUrl).catch(err => {
      console.error('Failed to load new URL:', err);
      mainWindow.loadFile(path.join(__dirname, 'fallback.html'));
    });
  }
});

ipcMain.on('clear-cache', async (event) => {
  if (mainWindow) {
    try {
      await mainWindow.webContents.session.clearCache();
      await mainWindow.webContents.session.clearStorageData();

      mainWindow.loadURL(currentServerUrl).catch(err => {
        mainWindow.loadFile(path.join(__dirname, 'fallback.html'));
      });
    } catch (error) {
      console.error("Failed to clear cache:", error);
    }
  }
});

ipcMain.handle('get-current-url', () => {
  return currentServerUrl;
});

app.on('ready', createWindow);

app.on('window-all-closed', function () {
  if (process.platform !== 'darwin') {
    app.quit();
  }
});

app.on('activate', function () {
  if (mainWindow === null) {
    createWindow();
  }
});
