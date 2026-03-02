<template>
    <v-app>
        <v-container fluid class="px-2 py-0">
            <v-row class="align-center" style="height: 6%;">
                <v-spacer></v-spacer>
                <v-col cols="2" class="mx-1">
                    <v-select
                        v-model="department"
                        :items="departmentList"
                        label="部門"
                        class="ma-0"
                        variant="outlined"
                        hide-details
                        @update:modelValue="setDepartment"
                    ></v-select>
                </v-col>
                <v-col cols="2" class="mx-1">
                    <v-select
                        v-model="line"
                        :items="lines"
                        label="ライン"
                        class="ma-0"
                        variant="outlined"
                        hide-details
                        item-title="title"
                        item-value="line"
                    ></v-select>
                </v-col>
            </v-row>

            <v-row style="height: 14%;">
                <v-col cols="4" class="card-contents text-value textp-5">
                    <v-spacer></v-spacer>
                    {{ productDate }}
                </v-col>
                <v-col cols="4" class="card-contents text-attention textp-5">
                    <v-spacer></v-spacer>
                    {{ line.title }}ﾗｲﾝ
                </v-col>
                <v-col cols="4" class="card-contents text-value textp-5">
                    <v-spacer></v-spacer>
                    {{ dailyQuantity }}
                </v-col>
            </v-row>

            <v-row style="height: 40%;">
                <v-col cols="4" class="card-contents">
                    <p class="title-label textp-5">計画数</p>
                    <p class="text-value textp-2">{{ scheduledQuantity }}</p>
                </v-col>
                <v-col cols="4" class="card-contents">
                    <p class="title-label textp-5">実績数</p>
                    <p class="text-value textp-2">{{ actualQuantity }}</p>
                </v-col>
                <v-col cols="4" class="card-contents">
                    <p class="text-value textp-5" style="border: solid 1px #ffffff">No.{{ precedenceOrder }}</p>
                    <p class="title-label textp-5">終了時間</p>
                    <p class="text-attention textp-3">{{ finishTime }}</p>
                </v-col>
            </v-row>

            <v-row style="height: 40%;">
                <v-col cols="2" class="card-contents py-0">
                    <p class="title-label textp-4 mt-2" style="padding: 0px !important">進</p>
                    <p class="title-label textp-4" style="padding: 0px !important">捗</p>
                    <p class="title-label textp-4 mt-n2" style="padding: 0px !important">度</p>
                </v-col>
                <v-col cols="8" class="card-contents">
                    <v-spacer></v-spacer>
                    <p id="progressValue" :class="progressClass + ' textp-1'">{{ progress }}</p>
                </v-col>
                <v-col cols="2" class="card-contents">
                    <v-spacer></v-spacer>
                    <p class="title-label textp-4">分</p>
                </v-col>
            </v-row>
        </v-container>
    </v-app>
</template>

<script>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import axios from 'axios';

export default {
    name: "ProgressDisplayComponent",
    setup() {
        const departmentList = ["EC", "EX", "EW", "EN"];
        const department = ref("");
        const lines = ref([]);
        const line = ref({ title: '', line: '' });
        const lineSign = ref("");
        const progressStatus = reactive({});
        const productDate = ref(null);
        const dailyQuantity = ref(null);
        const scheduledQuantity = ref(null);
        const actualQuantity = ref(null);
        const precedenceOrder = ref(null);
        const finishTime = ref(null);
        const progress = ref(null);
        const isFullScreen = ref(false);

        const progressClass = computed(() => {
            return progress.value >= 0 ? 'text-value' : 'progress-value';
        });

        function initialize() {
            department.value = localStorage.getItem("department") || "";
            lineSign.value = localStorage.getItem("lineSign") || "";

            if (!department.value) return;

            getLine(department.value);
            getProgressStatus();

            setInterval(() => {
                getProgressStatus();
            }, 60 * 1000);
        }

        function getProgressStatus() {
            if (!lineSign.value) return;

            let url = process.env.MIX_APP_PATH + "/ajax/getProgressStatus";
            axios
                .get(url, {
                    params: {
                        lineSign: lineSign.value,
                    },
                })
                .then((response) => {
                    if (response.data) {
                        Object.assign(progressStatus, response.data);
                    }
                })
                .catch(() => {});
        }

        function getLine(dept) {
            let url = process.env.MIX_APP_PATH + "/ajax/getline";
            axios
                .get(url, {
                    params: {
                        cls: dept,
                    },
                })
                .then((response) => {
                    if (response.data.data) {
                        lines.value = response.data.data;
                        for (const item of lines.value) {
                            if (item.line == lineSign.value) {
                                line.value = item;
                            }
                        }
                    }
                })
                .catch(() => {});
        }

        function setDepartment(event) {
            localStorage.setItem("department", event);
            getLine(event);
        }

        function setLineSign() {
            localStorage.setItem("lineSign", lineSign.value);
        }

        function switchScreenSize() {
            isFullScreen.value = !isFullScreen.value;
            setScreenSize();
        }

        function setScreenSize() {
            if (isFullScreen.value) {
                document.body.requestFullscreen();
            } else {
                document.exitFullscreen();
            }
        }

        watch(line, (item) => {
            lineSign.value = item.line;
            setLineSign();
        });

        watch(progressStatus, () => {
            productDate.value = progressStatus.productDate;
            dailyQuantity.value = progressStatus.dailyQuantity;
            scheduledQuantity.value = progressStatus.scheduledQuantity;
            actualQuantity.value = progressStatus.actualQuantity;
            precedenceOrder.value = progressStatus.precedenceOrder;
            finishTime.value = progressStatus.finishTime;
            progress.value = Math.round(progressStatus.progress);
        });

        onMounted(() => {
            initialize();
        });

        return {
            departmentList,
            department,
            lines,
            line,
            productDate,
            dailyQuantity,
            scheduledQuantity,
            actualQuantity,
            precedenceOrder,
            finishTime,
            progress,
            progressClass,
            setDepartment,
            switchScreenSize,
        };
    },
};
</script>

<style scoped>
/* 既存のCSSはそのまま利用可能 */
.textp-5 { font-size: 60px; margin-bottom: 0px; font-weight: bold; }
.textp-4 { font-size: 80px; margin-bottom: 0px; padding: 0px; font-weight: bold; }
.textp-3 { font-size: 100px; margin-bottom: 0px; padding: 0px; font-weight: bold; }
.textp-2 { font-size: 200px; margin-bottom: 0px; font-weight: bold; }
.textp-1 { font-size: 300px; margin-bottom: 0px; font-weight: bold; }
/* ...（省略）... */
</style>
