<template>
    <v-dialog v-model="drawer" max-width="400">
        <v-card>
            <v-card-title class="title">QRコードをスキャンしてください</v-card-title>
            <v-divider></v-divider>
            <v-card-text>
                <qrcode-stream ref="qrstream" class="qrstream" :track="repaint" :camera="facingMode"
                    :paused="paused" @detect="onDetect">
                </qrcode-stream>
            </v-card-text>
            <v-divider></v-divider>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue-darken-1" variant="text" @click="drawer = false">キャンセル</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>

    <v-card>
        <v-container class="pa-0 ml-4">
            <v-row v-if="isQrRead">
                <v-col cols="2">
                    <v-btn color="primary" @click="drawer = true">QR読取</v-btn>
                </v-col>
                <v-spacer></v-spacer>
            </v-row>
            <v-row v-else>
                <v-col cols="3">
                    <v-text-field class="pt-0 pr-1" label="型番" hide-details @change="ConvUpperCaseKataban"
                        v-model="inputkataban"></v-text-field>
                </v-col>
                <v-col cols="3">
                    <v-text-field class="pt-0" label="品名" hide-details v-model="inputhinmokumeisyou"></v-text-field>
                </v-col>
                <v-col cols="1">
                    <v-btn icon elevation="1" color="primary" @click="GetTanaban">
                        <v-icon>mdi-magnify</v-icon>
                    </v-btn>
                </v-col>
                <v-spacer></v-spacer>
                <v-col cols="2">
                    <v-btn color="indigo" variant="tonal" @click="drawer = true; isQrRead = true">QR読取</v-btn>
                </v-col>
            </v-row>
        </v-container>
    </v-card>

    <v-card class="ma-3">
        <v-form>
            <v-container v-if="isQrRead" class="mx-1 py-1" fluid>
                <!-- ...省略: v-row/v-colで置き換え ... -->
            </v-container>
            <v-container v-else fluid>
                <table class="table table-bordered" style="table-layout:fixed;" id="list">
                    <thead>
                        <tr>
                            <th class="text-center bg-info" style="width:20%;">型番</th>
                            <th class="text-center bg-info" style="width:25%;">品目名称</th>
                            <th class="text-center bg-info" style="width:15%;">色種</th>
                            <th class="text-center bg-info" style="width:15%;">定尺</th>
                            <th class="text-center bg-info" style="width:25%;">棚番</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(rows, index) in hinmokuList" :key="index">
                            <td>{{ rows['kataban'] }}</td>
                            <td>{{ rows['hinmokumeisyou'] }}</td>
                            <td>{{ rows['irosyu'] }}</td>
                            <td>{{ rows['teisyaku'] }}</td>
                            <td>{{ rows['tanaban'] }}</td>
                        </tr>
                    </tbody>
                </table>
            </v-container>
        </v-form>
    </v-card>
</template>

<style scoped>
.qrstream {
	margin: 10px 10px 10px 10px;
}

.table>>>td {
	font-size: 24px;
	font-weight: bold;
	height: 30px;
}

.processingTable>>>td {
	font-size: 12px;
}
</style>

<script>
export default {

	name: 'ItemReferenceCmponent',

	props: {
	},

	watch: {
		drawer(val) {
			this.paused = !val;
		}
	},

	data: () => ({
		gradient: '',
		paused: false,
		facingMode: true,
		repaint: false,
		drawer: false,
		isQrRead: true,
		qrData: [],
		footer: 'foo-----footer',
		items: [],
		processingInfo: [],
		kataban: "",
		inputkataban: "",
		hinmokumeisyou: "",
		inputhinmokumeisyou: "",
		hinmokuList: [],
		headers: [
			{ text: '型番', key: 'kataban', value: 'kataban' },
			{ text: '品目名称', key: 'hinmokumeisyou,', value: 'hinmokumeisyou' },
			{ text: '色種', key: 'irosyu', value: 'irosyu' },
			{ text: '定尺', key: 'teisyaku', value: 'teisyaku' },
			{ text: '棚番', key: 'tanaban', value: 'tanaban' },
		],
		items: [
			{
				kataban: 1,
				hinmokumeisyou: 'John',
				irosyu: 'Doe',
				teisyaku: 'Doe',
				tanaban: 'Doe',
			}
		]
	}),

	mounted() {
		if (process.env.MIX_FOOTER) { this.footer = process.env.MIX_FOOTER }
	},

	methods: {
		async startFrontCamera() {
			this.camera = 'front'
		},
		async onDetect(promise) {
			try {
				const {
					imageData,    // raw image data of image/frame
					content,      // decoded String
					location      // QR code coordinates
				} = await promise;
				this.paused = true;

				let qrData = content.split(',');
				if (qrData.length != 3) {
					window.alert('QRコードの内容が不正です。');
					return;
				}

				this.qrData.managementID = qrData[0];
				this.qrData.processingCode = qrData[1];
				this.qrData.processingValue = qrData[2];

				var url = process.env.MIX_APP_PATH + '/ajax/getreferenceitem';
				axios.get(url, {
					params: {
						managementID: this.qrData.managementID
					}
				},)
					.then((response) => {
						if (!response.data.data) {
							window.alert('部材情報が見つかりませんでした。');
							return;
						}

						this.items = response.data.data;
						this.processingInfo = response.data.processingInfo;
						console.log(this.processingInfo);
					})
					.catch((error) => {
						window.alert('部材情報の取得に失敗しました。');
					})
				this.drawer = false;
			} catch (error) {
			}
		},
		GetTanaban() {
			if (this.inputkataban == '' && this.inputhinmokumeisyou == '') {
				alert("型番・品目名称のいずれかの検索条件を入力してください。")
				return;
			}

			var url = process.env.MIX_APP_PATH + '/Gettanaban';
			this.kataban = this.inputkataban.toUpperCase();
			this.inputkataban = "";
			this.hinmokumeisyou = this.inputhinmokumeisyou;
			this.inputhinmokumeisyou = "";

			axios.get(url, {
				params: {
					kataban: this.kataban,
					hinmokumeisyou: this.hinmokumeisyou,
				}
			})
				.then(function (response) {
					if (!response.data.list) return;

					this.hinmokuList = response.data.list;
				}.bind(this))
				.catch(function (error) {
					alert(error)
				}.bind(this));

		},
		ConvUpperCaseKataban() {
			this.inputkataban = this.inputkataban.toUpperCase();
		},
	},
}
</script>
