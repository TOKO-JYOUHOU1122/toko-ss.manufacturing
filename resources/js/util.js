const electron = window.electron;

export function downloadCsv($route, $param) {
    axios.post(route($route), $param, {
        responseType: 'blob',
    })
        .then(function (response) {
            const disposition = response.headers['content-disposition'];
            const blob = new Blob([response.data], { type: 'text/csv' });

            let filename = `users_${new Date().toISOString()}.csv`;

            if (disposition) {
                const matchStar = disposition.match(/filename\*\s*=\s*UTF-8''([^;]+)/i);
                const matchBasic = disposition.match(/filename\s*=\s*"?([^"]+)"?/i);

                if (matchStar) {
                    try {
                        filename = decodeURIComponent(matchStar[1]);
                    } catch {
                        filename = matchStar[1];
                    }
                } else if (matchBasic) {
                    filename = matchBasic[1];
                }
            }

            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(url);
        }.bind(this))
        .catch(function (err) {
            alert(err);
        }.bind(this));
}

export async function openFilePicker($title = 'ファイルを選択', $filters = []) {
    const result = await electron.openFileDialog({
        title: $title,
        buttonLabel: '選択',
        properties: ['openFile'],
        filters: $filters
    });

    if (!result || result.canceled) return null;

    return result;
}

export function fileGenarater($file, $type) {
    const { fileName, buffer } = $file;
    return new File(
        [new Uint8Array(buffer)],
        fileName,
        { type: $type }
    );
}

export async function uploadCsv($route, $file, $param) {
    const form = new FormData()
    form.append('file', $file)
    form.append('encoding', 'shift-jis')
    form.append('delimiter', ',')
    for (const key in $param) {
        form.append(key, $param[key])
    }

    axios.post(route($route), form, {
        headers: {
            'Content-Type': 'multipart/form-data',
        },
    })
        .then(function (response) {
            if (response.data.errMessage) {
                console.error(response.data.errMessage);
                alert('インポートに失敗しました。\n' + response.data.errMessage);
            }
        }.bind(this))
        .catch(function (err) {
            console.log(err);
            alert('インポートに失敗しました。\n' + err);
        }.bind(this))
}

export let lastDirHandle = 'desktop'; // 初期ディレクトリ
