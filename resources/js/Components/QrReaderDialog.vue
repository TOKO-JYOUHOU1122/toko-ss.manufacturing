<template>
    <v-dialog v-model="dialog" max-width="400">
        <v-card>
            <v-card-title class="title">QRコードをスキャンしてください</v-card-title>
            <v-divider></v-divider>
            <v-card-text>
                <qrcode-stream :paused="paused" @detect="onDetect"></qrcode-stream>
            </v-card-text>
            <v-divider></v-divider>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="blue-darken-1" variant="text" @click="dialog = false">キャンセル</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
import { QrcodeStream } from 'vue-qrcode-reader';
import { fi } from 'vuetify/locale';

export default {
    name: 'QrReaderDialog',

    components: {
        QrcodeStream
    },

    data() {
        return {
            dialog: false,
            resolveFn: null,
            err_message: '',
            user: [],
        }
    },
    methods: {
        open() {
            this.dialog = true
            this.paused = false;

            return new Promise((resolve) => {
                this.resolveFn = resolve
            })
        },
        async onDetect(detectedCodes) {
            this.playBeep()
            this.dialog = false
            let qrData = detectedCodes[0].rawValue.trim();

            if (this.resolveFn) {
                this.resolveFn(qrData)
                this.resolveFn = null
            }
        },
        playBeep() {
            const ctx = new (window.AudioContext || window.webkitAudioContext)()
            const osc = ctx.createOscillator()
            const gainNode = ctx.createGain()

            osc.type = "square"
            osc.frequency.value = 1800

            // 音量を滑らかに制御
            gainNode.gain.setValueAtTime(0.8, ctx.currentTime)
            gainNode.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.5)

            osc.connect(gainNode)
            gainNode.connect(ctx.destination)

            osc.start()
            osc.stop(ctx.currentTime + 0.5)
        }
    }
}
</script>
