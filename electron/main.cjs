const { app, BrowserWindow, ipcMain, dialog } = require('electron')
const path = require('node:path')
const fs = require('node:fs')

if (!app.isPackaged) {
    require('dotenv').config()
}

let win = null

function getAppUrl() {
    const devUrl = process.env.APP_URL || 'http://127.0.0.1:8000'
    if (!app.isPackaged) return devUrl

    const exeDir = path.dirname(app.getPath('exe'))
    const configPath = path.join(exeDir, 'resources', 'config.json')

    if (fs.existsSync(configPath)) {
        try {
            const config = JSON.parse(fs.readFileSync(configPath, 'utf8'))
            if (config.appUrl) return config.appUrl
        } catch (e) {
            console.error('config.json parse error:', e)
        }
    }

    return devUrl
}

function createWindow() {
    win = new BrowserWindow({
        width: 1400,
        height: 900,
        webPreferences: {
            nodeIntegration: false,
            contextIsolation: true,
            preload: path.join(__dirname, 'preload.cjs'),
        },
    })

    const url = getAppUrl()
    win.loadURL(url).catch(err => {
        dialog.showErrorBox('起動エラー', 'サーバーに接続できません')
    })

    if (!app.isPackaged) {
        win.webContents.openDevTools()
    }
}

app.whenReady().then(() => {
    app.on('certificate-error', (event, webContents, url, error, certificate, callback) => {
        event.preventDefault()
        callback(true)
    })

    createWindow()
})

/** 画像選択ダイアログ */
ipcMain.handle('dialog:openFile', async (event, options = {}) => {
    const result = await dialog.showOpenDialog(options);
    if (result.canceled || result.filePaths.length === 0) return null;

    const filePath = result.filePaths[0];
    const buffer = fs.readFileSync(filePath);
    const fileName = path.basename(filePath);

    return {
        fileName,
        buffer,   // Buffer をそのまま返す
    };

});

