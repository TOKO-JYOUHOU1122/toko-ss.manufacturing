<template>

    <Head title="出庫依頼トップページ" />
    <UserLayout>
        <template #header>トップページ</template>

        <template #main>
            <v-container fluid class="pt-0">
                <v-row>
                    <v-col cols="">
                        <v-btn color="grey-darken-3" size="large" class="ml-2 mt-2" @click="ChangeDepartment()"
                            variant="outlined">
                            部門変更
                        </v-btn>
                        <v-btn color="grey-darken-3" size="large" class="ml-2 mt-2" @click="ChangeLine()"
                            variant="outlined">
                            ライン変更
                        </v-btn>
                    </v-col>
                </v-row>

                <v-row justify="center" class="text-center" no-gutters>
                    <v-col cols="12" md="12">
                        <div class="targetLine text-white" :class="targetLine.class">
                            <span>{{ targetLine.departmentCode }}{{ targetLine.lineName }}</span>
                        </div>
                        <v-spacer></v-spacer>
                    </v-col>
                </v-row>
                <v-row justify="center" class="text-center pt-10" no-gutters>
                    <v-col cols="12" md="12">
                        <v-btn color="primary" size="x-large" class="mx-5 my-4"
                            style="width: 400px; height: 80px; font-size: 25pt" @click="GotoVerification()">
                            出庫依頼
                        </v-btn>
                    </v-col>
                </v-row>
                <v-row justify="center" class="text-center" no-gutters>
                    <v-col cols="12" md="12">
                        <v-btn color="primary" size="x-large" class="mx-5 my-4"
                            style="width: 400px; height: 80px; font-size: 25pt" @click="GotoRequest()">
                            出庫依頼更新
                        </v-btn>
                    </v-col>
                </v-row>
                <v-row justify="center" class="text-center" no-gutters>
                    <v-col cols="12" md="12">
                        <v-btn color="primary" size="x-large" class="mx-5 my-4"
                            style="width: 400px; height: 80px; font-size: 25pt" @click="GotoHistory()">
                            出庫依頼確認
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
    departmentCodes: {
        type: Array,
    },
    lines: {
        type: Object,
    },
});
</script>

<script>
export default {
    name: 'IssueRequestHome',
    data: () => ({
        departmentCodeList: [],
        targetLines: [],
        targetLine: { departmentCode: '', lineSign: '', lineName: '', class: '' },
    }),
    mounted() {
        this.init()
    },
    methods: {
        init() {
            this.departmentCodeList = this.departmentCodes;
            this.getLine();
        },
        getLine() {
            this.departmentCode = localStorage.getItem('departmentCode') ? localStorage.getItem('departmentCode') : this.departmentCodes[0];
            let lineSign = localStorage.getItem('lineSign') ? localStorage.getItem('lineSign') : '';
            this.targetLines = this.lines.length > 0 ? this.lines.filter(line => line.departmentCode == this.departmentCode).map((line, index) => ({
                ...line,
                class: 'lineColor' + index
            })) : [];
            this.targetLine = this.targetLines.find(line => line.lineSign == lineSign) || { departmentCode: '', lineSign: '', lineName: '', class: '' };
        },
        GotoVerification() {
            this.$inertia.visit(route('issuerequest.verification'), {
                data: {
                    targetLine: this.targetLine
                }
            })
        },
        GotoRequest() {
            this.$inertia.visit(route('issuerequest.request'), {
                data: {
                    targetLine: this.targetLine
                }
            })
        },
        GotoHistory() {
            this.$inertia.visit(route('issuerequest.history'), {
                data: {
                    targetLine: this.targetLine
                }
            },
            {
preserveScroll: true,
  preserveState: true,   // 背景ページの状態を保つ
}
        )
        },
        ChangeDepartment() {
            let index = this.departmentCodeList.findIndex(dep => dep == this.departmentCode);
            index = (index == -1 || index + 1 == this.departmentCodeList.length) ? 0 : index + 1;

            this.departmentCode = this.departmentCodeList[index];
            localStorage.setItem('departmentCode', this.departmentCode);
            this.getLine();
            this.ChangeLine();
        },
        ChangeLine() {
            let index = this.targetLines.findIndex(line => line.lineSign == this.targetLine.lineSign);
            index = (index == -1 || index + 1 == this.targetLines.length) ? 0 : index + 1;

            this.targetLine = this.targetLines[index];
            this.targetLine.class = 'lineColor' + index;
            localStorage.setItem('lineSign', this.targetLine.lineSign);
        }
    },
}
</script>

<style scoped>
.targetLine {
    height: 100px;
    width: 400px;
    font-size: 45pt;
    margin: 0px auto;
}

.lineColor0 {
    background-color: #198754;
}

.lineColor1 {
    background-color: #dc3545;
}

.lineColor2 {
    background-color: #f79400;
}

.lineColor3 {
    background-color: #0d6efd;
}

.lineColor4 {
    background-color: #212529;
}

.lineColor5 {
    background-color: #b2a300;
}
</style>
