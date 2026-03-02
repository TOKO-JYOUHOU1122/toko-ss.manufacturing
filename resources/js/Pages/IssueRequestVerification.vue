<template>

    <Head title="出庫依頼" />
    <UserLayout>
        <template #header>出庫依頼</template>

        <template #main>
            <v-container fluid>
                <v-row class="mt-8">
                    <v-col>
                        <v-btn color="primary" size="large" height="60" width="150" class="ml-5 text-h5"
                            @click="goRequest()">
                            依頼更新へ
                        </v-btn>
                        <v-btn color="grey" size="large" class="ml-5 text-h5" height="60" width="150" @click="GoHome()">
                            ホームへ
                        </v-btn>
                    </v-col>
                </v-row>

                <v-row>
                    <v-col cols="3" class="mt-3">
                        <h2>出庫依頼</h2>
                    </v-col>
                    <v-col cols="3" class="mt-2">
                        <span class="text-h5">ライン:</span>
                        <span class="text-h4 font-weight-bold">{{ targetLine.lineName }}</span>
                    </v-col>
                </v-row>

                <v-row no-gutters>
                    <v-col cols="3" class="mt-2">
                        <v-text-field v-model="inputCode" ref="inputCodeRef" @change="getItem()" width="350"
                            style="font-size: 18pt; background-color: #FFFFCC; border: solid 3px;" autocomplete="off"
                            hide-details="auto" />
                    </v-col>
                </v-row>

                <v-row no-gutters class="pt-5">
                    <v-table class="ml-1 thin-border" style="max-width: 1000px;">
                        <tbody>
                            <tr>
                                <td class="bg-grey-lighten-1 text-h4 py-3" width="200">品目CD</td>
                                <td class="text-h4" width="700">{{ itemCode }}</td>
                            </tr>
                            <tr>
                                <td class="bg-grey-lighten-1 text-h4 py-3">品目名称</td>
                                <td class="text-h4">{{ itemName }}</td>
                            </tr>
                            <tr height="100">
                                <td class="bg-grey-lighten-1 text-h4 py-3">依頼数</td>
                                <td>
                                    <div v-if="targetLine.departmentCode == 'EW'" class="align-center d-flex">
                                        <v-text-field v-model="requestCount" type="text" inputmode="numeric"
                                            pattern="[0-9]*" :rules="[v => /^[0-9]+$/.test(v) || '自然数を入力してください']"
                                            density="comfortable" variant="underlined" hide-details="auto"
                                            style="max-width: 500px;" class="pr-7"/>
                                        <v-btn color="red" size="large" height="70" width="60" class="text-h4">
                                            ＋
                                        </v-btn>
                                        <v-btn color="red" size="large" height="70" width="60" class="text-h4 ml-2"
                                            @click="Minus">
                                            －
                                        </v-btn>
                                    </div>
                                    <div v-else class="text-h4">{{ requestCount }}</div>
                                </td>
                            </tr>
                            <tr>
                                <td class="bg-grey-lighten-1 text-h4 py-3">依頼残数</td>
                                <td v-if="!targetItem.remainingCount || targetItem.remainingCount == 0" class="text-h4">
                                    {{ targetItem.remainingCount }}
                                </td>
                                <td v-else class="text-h4 bg-yellow">
                                    {{ targetItem.remainingCount }}
                                </td>
                            </tr>
                        </tbody>
                    </v-table>
                </v-row>

                <v-row class="mt-8" justify="center">
                    <v-col cols="8">
                        <v-btn color="primary" size="large" height="70" width="170" class="text-h4" @click="Cancel">
                            取消
                        </v-btn>
                        <v-btn color="error" size="large" height="70" width="170" class="text-h4 ml-5" @click="Request">
                            依頼登録
                        </v-btn>
                    </v-col>
                </v-row>
            </v-container>
        </template>
    </UserLayout>
</template>

<script setup>
import UserLayout from '@/Layouts/UserLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    targetLine: {
        type: Object,
    },
});
</script>

<script>
export default {
    name: 'IssueRequestVerification',

    mounted() {
        this.initialize();
    },

    data: () => ({
        inputCode: "",
        requestCount: 0,
        targetItem: {},
        prevTargetItem: {},
        inputCodeRef: null
    }),

    methods: {
        initialize() {
            this.requestCount = 0;
            this.forcusInput();
        },

        GetItem() {
            let itemCode = this.inputCode.toUpperCase();
            if (itemCode === this.prevTargetItem.itemCode && this.targetLine.departmentCode === 'EW') {
                this.Plus();
                return;
            }

            this.ClearDisplay();

            axios.get(route('issuerequest.getItem'), {
                params: {
                    targetLine: this.targetLine,
                    itemCode: itemCode,
                }
            })
                .then(response => {
                    if (response.data.errMessage) {
                        alert('エラーが発生しました: ' + response.data.errMessage);
                        return;
                    }
                    if (response.data) {
                        this.targetItem = response.data.targetItem;
                        this.prevTargetItem = this.targetItem;
                    }
                })
                .catch(error => {
                    alert('エラーが発生しました: ' + error.message);
                });
        },

        Plus() {
            this.requestCount = Number(this.requestCount) + Number(this.roundingCount);
            this.forcusInput();
        },

        Minus() {
            this.requestCount = Number(this.requestCount) - Number(this.roundingCount);
            if (this.requestCount < 0) {
                this.requestCount = 0;
            }
            this.forcusInput();
        },

        Request() {
            if (this.itemName === '') return;
            if (this.requestCount === 0) return;
            if (this.shelfNumber === 'ZZZZ-ZZ-ZZ') {
                alert("棚番 ： ZZZZ-ZZ-ZZ の品目は登録できません。");
                return;
            }

            if (!window.confirm('依頼登録します。')) return;

            axios.post(route('issuerequest.request.regist'), {
                targetLine: this.targetLine,
                targetItem: this.targetItem,
                requestCount: this.requestCount,
            })
                .then(function (response) {
                    if (response.data.errMessage) {
                        alert('エラーが発生しました: ' + response.data.errMessage);
                        return;
                    }
                }.bind(this))
                .catch(function (error) {
                    alert('エラーが発生しました: ' + error.message);
                    return;
                }.bind(this));

            this.ClearDisplay();
        },

        goRequest() {
            this.$inertia.visit(route('issuerequest.request'), {
                data: {
                    targetLine: this.targetLine,
                }
            });
        },

        GoHome() {
            this.$inertia.visit(route('issuerequest.home'));
        },

        Cancel() {
            if (!window.confirm('取り消します。')) return;
            this.ClearDisplay();
        },

        ClearDisplay() {
            this.inputCode = "";
            this.requestCount = 0;
            this.prevTargetItem = {};
            this.targetItem = {};
            this.forcusInput();
        },

        forcusInput() {
            this.$nextTick(() => this.inputCodeRef && this.inputCodeRef.focus());
        }
    }
}
</script>

<style scoped>
:deep(.v-field__input) {
    padding: 0px !important;
    font-size: 2.5em;
    max-height: 2.5em !important;
}


.thin-border {
    border: 1px solid rgba(0, 0, 0, 0.12);
    overflow: hidden;
}
</style>
