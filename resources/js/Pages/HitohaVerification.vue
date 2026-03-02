<template>

    <Head title="部材照合" />
    <UserLayout>
        <template #header>部材照合</template>

        <template #main>
            <v-container fluid class="pa-1 h-100">
                <v-row dense style="height: 80px;">
                    <v-col cols="5" class="d-flex justify-center">
                        <label class="text-h3 text-center w-100">{{ serial }}</label>
                    </v-col>
                    <v-col cols="5" class="d-flex justify-center">
                        <label class="text-h3 text-center w-100">{{ position }}</label>
                    </v-col>
                    <v-col cols="2" class="d-flex flex-column justify-center">
                        <v-row dense>
                            <v-col cols="12" class="d-flex align-center">
                                <v-btn v-if="verify_item.length > 0" color="red" height="50" class="w-100 text-h5" @click="reset()">最初からやり直す</v-btn>
                                <v-btn v-else color="red" height="50" class="w-100 text-h5" @click="close()">閉じる</v-btn>
                            </v-col>
                        </v-row>
                        <v-row dense>
                            <v-col cols="12" class="d-flex align-center">
                                <v-btn class="w-100 text-h7" height="0" color="red" :disabled="true">不良発生</v-btn>
                            </v-col>
                        </v-row>
                    </v-col>
                </v-row>
                <v-row dense class="pt-2" style="height: calc(100% - 100px);">
                    <v-col cols="10">
                        <v-data-table class="mt-1 text-h5 table-cell border" density="compact" hover :headers="headers"
                            :items="verify_item" :hide-default-footer="true" :items-per-page="-1"
                            no-data-text="【部材照合】各部材のバーコードを読み込み、部材照合を実施して下さい。">
                            <template #item.verify_datetime="{ item }">
                                <span v-if="item.verify_datetime">〇</span>
                                <span v-else>-</span>
                            </template>
                        </v-data-table>
                    </v-col>
                    <v-col cols="2">
                        <v-row dense style="height: calc(100% - 150px);" />
                        <v-row dense>
                            <v-col cols="12">
                                <v-btn class="text-h5 w-100" height="60" color="secondary"
                                    @click="showQrReader()">QR読込</v-btn>
                            </v-col>
                        </v-row>
                        <v-row dense>
                            <v-col cols="12">
                                <div class="d-flex flex-column align-center">
                                    <span>作業者</span>
                                    <v-btn class="text-h5 w-100" height="60" color="primary"
                                        @click="showUserSelector()">
                                        {{ user.user_name || '未選択' }}
                                    </v-btn>
                                </div>
                            </v-col>
                        </v-row>
                    </v-col>
                </v-row>
            </v-container>
            <v-text-field v-model="qrInput" ref="scanField" @keydown.enter.prevent="handleScan()"
                class="invisible-field" tabindex="-1" />
            <QrReader ref="qrReader" />
            <UserSelector ref="userSelector" :department_code="department_code" :line_sign="line_sign" />
            <v-dialog v-model="resultDrawer" min-height="250" persistent>
                <v-card class="pa-0 align-center">
                    <v-card-text class="h-100 d-flex align-center justify-center text-h6">
                        <v-icon v-if="isSendSuccessed" left size="34" class="pr-5"
                            color="green">mdi-check-circle</v-icon>
                        <v-icon v-else left size="34" class="pr-5" color="red">mdi-alert</v-icon>
                        {{ sendMessage }}
                    </v-card-text>
                    <v-card-actions>
                        <v-btn class="mx-2" color="primary" variant="outlined" @click="dialogClose()"
                            width="120">閉じる</v-btn>
                        <v-btn v-if="!isSendSuccessed" class="mx-2" color="primary" variant="outlined"
                            @click="sendResults()" width="120">再送信する</v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </template>
    </UserLayout>
</template>

<script setup>
import UserLayout from '@/Layouts/UserLayout.vue';
import { Head } from '@inertiajs/vue3';
import QrReader from '../Components/QrReaderDialog.vue'
import UserSelector from '../Components/UserSelectorDialog.vue'

defineProps({
    department_code: {
        type: String,
    },
    line_sign: {
        type: String,
    },
    process_name: {
        type: String,
    },
});
</script>

<script>
export default {
    name: 'HitohaVerification',

    props: {
    },

    data: () => ({
        headers: [
            { title: '照合済', key: 'verify_datetime', align: 'center', sortable: false, },
            { title: '部材管理ID', key: 'id', align: 'center', sortable: false, },
            { title: '部材名', key: 'item_name', align: 'center', sortable: false, }
        ],
        verify_item: [],
        serial: '-',
        position: '-',
        user: { id: '9999', user_name: '' },
        qrInput: '',
        qrReader: null,
        userSelector: null,
        resultDrawer: false,
        isSendSuccessed: false,
        sendMessage: '',
    }),

    mounted() {
        this.focusField()
        this.addFocusListeners()
        this.qrReader = this.$refs.qrReader;
        this.userSelector = this.$refs.userSelector;

        let session_user = JSON.parse(sessionStorage.getItem("user"))
        if (session_user) {
            this.user = session_user;
        } else {
            this.showUserSelector();
        }
    },
    beforeUnmount() {
        this.removeFocusListeners()
    },

    methods: {
        getVerifyData(managementID) {
            axios.get(route('hitoha.fetchItem'), {
                params: {
                    managementID: managementID,
                    department_code: this.department_code,
                    processName: this.process_name,
                }
            },)
                .then((response) => {
                    if (!response.data.data) {
                        window.alert('部材情報が見つかりませんでした。');
                        return;
                    }
                    if (response.data.err_message) {
                        window.alert(response.data.err_message);
                        return;
                    }

                    this.verify_item = response.data.data;
                    this.serial = response.data.serial || '-';
                    this.position = response.data.position || '-';
                    const allVerified = this.verify_item.every(item => item.verify_datetime !== null);
                    if (allVerified) {
                        this.sendMessage = 'この部材は既に全て照合済みです。';
                        this.isSendSuccessed = true;
                        this.resultDrawer = true;
                        return;
                    }
                    this.verifyItems(managementID);
                })
                .catch((error) => {
                    window.alert('部材情報の取得に失敗しました。\n' + error);
                })
        },
        verifyItems(managementID) {
            const found = this.verify_item.find(item => item.id == managementID);
            if (found && !found.verify_datetime) {
                found.verify_datetime = new Date().toLocaleString('ja-JP');
            } else if (!found) {
                window.alert('管理IDが一致する部材がありません。\n管理ID:' + managementID);
                return;
            }

            const allVerified = this.verify_item.every(item => item.verify_datetime !== null);
            if (!allVerified) return;

            this.sendResults();
        },

        sendResults() {
            this.isSendSuccessed = false;

            axios.post(route('hitoha.registItem'), {
                items: this.verify_item,
                department_code: this.department_code,
                processName: this.process_name,
            }).then(response => {
                if (response.data.errorMessage) {
                    this.sendMessage = 'エラーが発生しました: ' + response.data.errorMessage;
                    return;
                }
                this.sendMessage = '点検結果を送信しました。';
                this.isSendSuccessed = true;
            }).catch(error => {
                this.sendMessage = 'エラーが発生しました: ' + error;
                return;
            }).finally(() => {
                this.resultDrawer = true;
            });

            return;
        },

        reset() {
            this.verify_item = [];
            this.serial = '-';
            this.position = '-';
            this.qrInput = '';
            this.isSendSuccessed = false;
            this.focusField();
        },

        close() {
            window.close();
        },

        dialogClose() {
            this.resultDrawer = false;
            if (this.isSendSuccessed) this.reset();
        },

        handleScan() {
            if (this.qrInput === "") return;

            if (!this.qrInput.includes('-')) {
                window.alert('読み込んだQRコードは対象の書式ではありません。' + this.qrInput);
                return;
            }

            if (!this.verify_item || this.verify_item.length === 0) {
                this.getVerifyData(this.qrInput);
            } else {
                this.verifyItems(this.qrInput);
            }

            this.qrInput = '';
        },

        async showQrReader() {
            const result = await this.qrReader.open();
            if (!result) return;

            this.qrInput = result;
            this.handleScan();
        },

        async showUserSelector() {
            const result = await this.userSelector.open();
            if (!result) return;

            this.user = result;
            sessionStorage.setItem("user", JSON.stringify(this.user));
        },

        focusField() {
            if (this.$refs.scanField) {
                this.$refs.scanField.focus()
            }
        },
        addFocusListeners() {
            document.addEventListener("click", this.focusField)
            document.addEventListener("keydown", this.focusField)
        },
        removeFocusListeners() {
            document.removeEventListener("click", this.focusField)
            document.removeEventListener("keydown", this.focusField)
        },
    },
    watch: {
        verify_item: {
            deep: true
        },
    }
}
</script>

<style scoped>
.invisible-field {
    position: absolute;
    opacity: 0;
    pointer-events: none;
    height: 0;
    width: 0;
}

.v-data-table {
    min-height: 100%;
}
</style>
