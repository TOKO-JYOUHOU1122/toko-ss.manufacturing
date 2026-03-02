<template>
    <v-dialog v-model="dialog" max-width="1100" persistent>
        <v-card>
            <v-toolbar color="seisan_primary">
                <v-toolbar-title class="text-white">編集</v-toolbar-title>
            </v-toolbar>
            <v-card-text>
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-7 mt-7">
                            <h2>出庫依頼更新 数量変更</h2>
                        </div>
                    </div>
                    <div class="row">
                        <div>
                            <v-table class="ml-2" density="comfortable">
                                <tbody>
                                    <tr>
                                        <td style="width:130px; background-color: #CCCCCC; font-size: 25px;">品目CD</td>
                                        <td style="width:440px; font-size: 30px;">{{ itemCode }}</td>
                                        <td style="width:150px"></td>
                                    </tr>
                                    <tr>
                                        <td style="background-color: #CCCCCC; font-size: 25px;">品目名称</td>
                                        <td style="font-size: 30px;">{{ itemName }}</td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td style="background-color: #CCCCCC; font-size: 25px;">依頼数</td>
                                        <td style="vertical-align: middle;">
                                            <v-text-field
                                                v-model.number="requestCount"
                                                type="number"
                                                density="comfortable"
                                                :rules="[v => !isNaN(v) || '数値を入力してください']"
                                                hide-details="auto"
                                                style="font-size:2em;"
                                            />
                                        </td>
                                        <td>
                                            <v-btn color="error" size="large" style="height: 60px; width: 60px; font-size:25px;" @click="Plus">＋</v-btn>
                                            <v-btn color="error" size="large" style="height: 60px; width: 60px; font-size:25px;" @click="Minus">－</v-btn>
                                        </td>
                                    </tr>
                                </tbody>
                            </v-table>
                        </div>
                    </div>
                    <div class="mt-4">
                        <v-btn color="primary" size="large" class="mr-2" style="margin-left: 150px; height: 60px; width: 160px; font-size:25px;" @click="Cancel">取消</v-btn>
                        <v-btn color="error" size="large" style="margin-left: 20px; height: 60px; width: 160px; font-size:25px;" @click="Update">更新</v-btn>
                    </div>
                </div>
            </v-card-text>
        </v-card>
    </v-dialog>
</template>

<script setup>
import { ref } from 'vue'

const dialog = ref(false)
const edittedItem = ref({})
const itemCode = ref('')
const itemName = ref('')
const requestCount = ref(0)
const roundingCount = ref(0)
let resolve, reject

function open(item) {
    edittedItem.value = item
    itemCode.value = item.itemCode
    itemName.value = item.itemName
    requestCount.value = item.requestCount
    roundingCount.value = item.roundingCount
    dialog.value = true
    return new Promise((res, rej) => {
        resolve = res
        reject = rej
    })
}

function Plus() {
    requestCount.value = Number(requestCount.value) + Number(roundingCount.value)
}

function Minus() {
    requestCount.value = Number(requestCount.value) - Number(roundingCount.value)
    if (requestCount.value < 0) {
        requestCount.value = 0
    }
}

function Cancel() {
    if (requestCount.value == edittedItem.value.requestCount) {
        dialog.value = false
        return
    }
    if (!window.confirm('取り消します。')) return
    resolve(edittedItem.value.requestCount)
    dialog.value = false
}

function Update() {
    if (!window.confirm('依頼数を更新します。')) return
    const url = process.env.MIX_APP_PATH + '/EditRequest'
    axios.put(url, {
        id: edittedItem.value.id,
        requestCount: requestCount.value
    })
    resolve(requestCount.value)
    dialog.value = false
}
</script>

<style scoped>
.v-text-field {
    padding: 0px !important;
}
.v-text-field input {
    padding: 0px !important;
    font-size: 2em;
    max-height: 2.5em !important;
}
</style>
