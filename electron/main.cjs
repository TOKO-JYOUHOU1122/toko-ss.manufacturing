// electron/main.cjs
const { app, BrowserWindow, ipcMain, dialog } = require('electron')
const path = require('node:path')
require('dotenv').config()

let win = null;

function createWindow() {
    win = new BrowserWindow({
        width: 1400,
        height: 900,
        webPreferences: {
            contextIsolation: true,
            nodeIntegration: false,
            preload: path.join(__dirname, 'preload.cjs'),
        },
    })

    const LARAVEL_URL = process.env.APP_URL || 'http://127.0.0.1:8000'

    if (process.env.NODE_ENV !== 'production') {
        // ★ 開発時は Laravel（Inertia）を開く
        win.loadURL(LARAVEL_URL)
        win.webContents.openDevTools()
    } else {
        // ★ 本番は dist を読み込む（または本番もURLを開くなら loadURL に置き換え）
        win.loadFile(path.join(__dirname, '../dist/index.html'))
    }
}

app.whenReady().then(createWindow)
app.on('window-all-closed', () => app.quit())


/** 画像選択ダイアログ */
ipcMain.handle('dialog:openFile', async (event, options = {}) => {
    const result = await dialog.showOpenDialog(options);
    if (result.canceled || result.filePaths.length === 0) return null;

    return result;
});

