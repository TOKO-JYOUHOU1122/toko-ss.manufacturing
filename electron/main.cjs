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

    // 本番環境（ビルド後）でも外部の config.json からURLを上書きできるようにする
    let productionUrl = 'http://127.0.0.1:8000'; // デフォルトフォールバック
    if (process.env.APP_URL) {
        productionUrl = process.env.APP_URL;
    }

    try {
        // exeと同じ階層に config.json があれば読み込む
        const fs = require('node:fs');
        const exeDir = path.dirname(app.getPath('exe'));
        const configPath = path.join(exeDir, 'config.json');
        if (fs.existsSync(configPath)) {
            const configData = JSON.parse(fs.readFileSync(configPath, 'utf8'));
            if (configData.url) {
                productionUrl = configData.url;
            }
        }
    } catch (e) {
        console.error('Config load error:', e);
    }

    const LARAVEL_URL = process.env.NODE_ENV !== 'production' ? (process.env.APP_URL || 'http://127.0.0.1:8000') : productionUrl;

    // ☆ 開発/本番問わずサーバーURLを開く（Inertia対応のため）
    win.loadURL(LARAVEL_URL);
    
    if (process.env.NODE_ENV !== 'production') {
        win.webContents.openDevTools();
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

