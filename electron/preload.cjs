// electron/preload.cjs
const { contextBridge, ipcRenderer } = require('electron')

contextBridge.exposeInMainWorld('electron', {
  isElectron: true,

  // ファイル選択ダイアログ（openFile）
  openFileDialog: (options = {}) => ipcRenderer.invoke('dialog:openFile', options),

  // ディレクトリ選択（必要なら）
  openDirectoryDialog: (options = {}) => ipcRenderer.invoke('dialog:openDirectory', options),

  // 保存ダイアログ
  saveFileDialog: (options = {}) => ipcRenderer.invoke('dialog:saveFile', options),

  // Web の File に近い配列を返すピッカー（accept/multiple対応）
  openFilePicker: (accept = {}, multiple = false, excludeAcceptAllOption = false) =>
    ipcRenderer.invoke('fs:openFilePicker', { accept, multiple, excludeAcceptAllOption }),

  // テキストファイルを読む（安全に）
  readTextFile: (filePath, encoding) => ipcRenderer.invoke('file:readText', filePath, encoding),

  // Blob を保存
  saveBlob: async (filePath, blob) => {
    const ab = await blob.arrayBuffer()
    const buf = Buffer.from(ab)
    return ipcRenderer.invoke('fs:writeFile', filePath, buf)
  },
})
