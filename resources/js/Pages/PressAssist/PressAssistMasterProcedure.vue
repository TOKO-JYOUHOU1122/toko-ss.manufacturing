<template>

    <Head title="プレスアシスト加工手順" />
    <UserLayout>
        <template #header>プレスアシスト加工手順</template>

        <template #main>
            <v-container fluid class="pt-3">
                <v-row dense class="pt-3 px-2">
                    <v-col cols="12" class="d-flex justify-end">
                        <v-alert type="info" variant="plain" v-if="!work_number" density="compact">
                            作業番号を選択してください
                        </v-alert>
                        <v-select v-model="work_number" label="作業番号" :items="work_numbers" variant="underlined"
                            density="compact" hide-details="auto" clearable max-width="200" class="px-3"></v-select>
                    </v-col>
                </v-row>
                <v-divider class="my-2"></v-divider>
                <div v-for="(item, index) in editable_items" :key="index">
                    <v-row no-gutters class="py-3" :class="item.削除区分 ? 'bg-lighten-4' : ''">
                        <v-col cols="12" class="d-flex align-end">
                            <v-icon color="primary" @click="reorder(item.作業順, item.作業順 - 1)"
                                size="40">mdi-arrow-up-thick</v-icon>
                            <v-icon class="pl-2 pr-5" color="primary" @click="reorder(item.作業順, item.作業順 + 1)"
                                size="40">mdi-arrow-down-thick</v-icon>
                            <HoverTooltip :text="'同時に加工したい手順については\n作業順を同一にしてください。'">
                                <template #activator="{ props }">
                                    <v-text-field v-model="item.作業順" label="作業順" variant="underlined"
                                        hide-details="auto" max-width="100" class="px-3" v-bind="props"></v-text-field>
                                </template>
                            </HoverTooltip>
                            <v-select v-model="item.管理番号" label="管理番号" :items="equipment_numbers" variant="underlined"
                                hide-details="auto" clearable max-width="150" class="px-3"></v-select>
                            <v-select v-model="item.段位置" label="段位置" :items="targetPositions(item.管理番号)"
                                variant="underlined" hide-details="auto" clearable max-width="200"
                                class="px-3"></v-select>
                            <v-text-field v-model="item.型図パス" label="型図パス" variant="underlined" hide-details="auto"
                                class="pl-3 pr-0 text-disabled-input" readonly max-width="800"></v-text-field>
                            <v-icon size="x-large" color="primary" @click="selectImage(item)"
                                class="mr-3">mdi-folder-open</v-icon>
                            <HoverTooltip
                                :text="'数字の桁数が画像の分割数で、\n1が表示する場所です。\n例1) 01 画像の右1/2を表示\n例2) 1000 画像の左端1/4のみ表示\n例3) 110 画像の左から2/3を表示'">
                                <template #activator="{ props }">
                                    <v-text-field v-model="item.画像位置" label="画像位置" v-bind="props" variant="underlined"
                                        max-width="100" hide-details="auto"></v-text-field>
                                </template>
                            </HoverTooltip>
                            <v-checkbox v-model="item.反転フラグ" label="加工前に反転" hide-details="auto" class="pl-3 px-5"
                                density="compact" />
                            <v-icon v-if="item.削除区分" color="secondary" size="40"
                                @click="item.削除区分 = false">mdi-delete-restore</v-icon>
                            <v-icon v-else color="red" size="40" @click="item.削除区分 = true">mdi-delete</v-icon>
                        </v-col>
                    </v-row>
                </div>
                <v-row v-if="work_number" no-gutters class="pt-3">
                    <v-col cols="12" class="d-flex justify-center">
                        <v-btn variant="outlined" color="primary" class="ma-2" @click="insertProcedure()">手順追加</v-btn>
                    </v-col>
                </v-row>

                <ConfirmDialog v-model="dialogDelete" :title="'手順削除'" :message="'表示されている作業番号を削除します'" btn1Text="キャンセル"
                    btn2Text="削除" @btn1Click="dialogDelete = false;" @btn2Click="deleteProcedures()" />
                <ConfirmDialog v-model="dialogInsert" :title="'手順追加'" btn1Text="キャンセル" btn2Text="追加"
                    @btn1Click="dialogInsert = false;" @btn2Click="insertProcedure()">
                    <span>以下の作業番号を追加します</span>
                    <v-text-field v-model="insert_work_number" label="作業番号" :items="work_numbers" variant="underlined"
                        clearable max-width="300" class="px-3" :rules="rulesInsert"></v-text-field>
                </ConfirmDialog>
                <LoadingModal ref="mloading" />
            </v-container>
        </template>
        <template #footer>
            <v-row dense>
                <v-col cols="12" class=" d-flex justify-end">
                    <v-btn color="secondary" class="mx-2" width="150" variant="elevated"
                        @click="previewProcedures()">プレビュー</v-btn>
                    <v-btn color="red" class="mx-2" width="150" variant="elevated"
                        @click="dialogDelete = !dialogDelete">削除</v-btn>
                    <v-spacer></v-spacer>
                    <v-btn v-if="work_number" color="primary" class="mx-2" width="150" variant="elevated"
                        @click="dialogCopy = !dialogCopy">このデータを複製</v-btn>
                    <v-btn v-else color="primary" class="mx-2" width="150" variant="elevated"
                        @click="dialogInsert = !dialogInsert">新規作成</v-btn>
                </v-col>
            </v-row>
        </template>
    </UserLayout>
</template>

<script setup>
import UserLayout from '@/Layouts/PressAssist/PressAssistLayout.vue';
import { Head } from '@inertiajs/vue3';
import ConfirmDialog from '../../Components/ConfirmDialog.vue';
import LoadingModal from '../../Components/ModalLoadingComponent.vue';
import HoverTooltip from '@/components/HoverTooltip.vue';
import { openFilePicker } from '../../util';

defineProps({
    work_numbers: {
        type: Array,
    },
    positions: {
        type: Array,
    },
    procedures: {
        type: Array,
    },
});
</script>

<script>

export default {
    name: 'PressAssistMasterProcedure',
    data: () => ({
        editable_items: [],
        defaultItem: {
            ID: null,
            管理番号: null,
            段位置: null,
            作業順: null,
            型図パス: null,
            画像位置: '0',
            反転フラグ: false,
            削除区分: false,
        },
        equipment_numbers: [],
        work_number: null,
        insert_work_number: null,

        loading: false,
        dialogDelete: false,
        dialogInsert: false,
        dialogCopy: false,
        rulesInsert: [value => (value || !this.work_numbers.includes(this.insert_work_number)) || '既存の作業番号は選択できません']
    }),
    mounted() {
        this.init()
    },

    computed: {
        formTitle() {
            return this.editedIndex === -1 ? 'マスタ作成' : 'マスタ編集';
        },
    },

    methods: {
        init: function () {
            this.editable_items = this.procedures;
            if (this.editable_items.length > 0) this.work_number = this.editable_items[0].作業番号;

            const groups = this.positions.reduce((acc, item) => {
                for (const key of ['管理番号']) {
                    const keyValue = item[key];
                    (acc[keyValue] ??= []).push(item);
                }
                return acc;
            }, {});
            this.equipment_numbers = [...new Set(this.positions.map(pos => pos.管理番号))];
        },

        targetPositions(equipment_number) {
            return this.positions.filter(pos => pos.管理番号 === equipment_number).map(pos => pos.段位置);
        },

        fetchProcedures() {
            if (!this.work_number) {
                this.editable_items = [];
                return;
            }

            this.loading = true
            axios.get(route('pressassist.mst.procedure.fetch'), {
                params: {
                    work_number: this.work_number,
                }
            })
                .then(function (response) {
                    this.editable_items = response.data;
                }.bind(this))
                .catch(function (error) {

                }.bind(this))
                .finally(() => {
                    this.loading = false
                });
        },

        insertProcedure() {
            const newItem = Object.assign({}, this.defaultItem);
            newItem.作業番号 = this.work_number;
            newItem.作業順 = this.editable_items.reduce((max, item) => Math.max(max, Number(item.作業順) || 0), 0) + 1
            this.editable_items.push(newItem);
        },

        reorder(fromOrder, toOrder) {
            if (toOrder < 1 || toOrder > this.editable_items.length) return;

            const fromIndex = this.editable_items.findIndex(item => item.作業順 === fromOrder);
            const toIndex = this.editable_items.findIndex(item => item.作業順 === toOrder);

            if (fromIndex === -1 || toIndex === -1) return;

            const tempOrder = this.editable_items[fromIndex].作業順;
            this.editable_items[fromIndex].作業順 = this.editable_items[toIndex].作業順;
            this.editable_items[toIndex].作業順 = tempOrder;
        },

        /**
         * @description 保存クリック時処理
         */
        saveProcedures() {
            this.loading = true
            axios.post(route('pressassist.mst.procedure.regist'), {
                editable_items: this.editable_items
            })
                .then(function (response) {
                    if (response.data.errMessage) {
                        alert('マスタの更新に失敗しました。\n' + response.data.errMessage)
                        console.log(response.data.errMessage)
                    }
                }.bind(this))
                .catch(function (err) {
                    alert(err);
                }.bind(this))
                .finally(() => {
                    this.loading = false
                    this.fetchProcedures()
                });
        },

        deleteProcedures(procedure) {
            this.loading = true
            axios.post(route('pressassist.mst.procedure.delete'), {
                ID: procedure.ID,
            })
                .then(function (response) {
                    if (!response.data.errMessage) {
                        this.dialogDelete = false
                    } else {
                        alert('マスタの削除に失敗しました。\n' + response.data.errMessage)
                        console.log(response.data.errMessage)
                    }
                }.bind(this))
                .catch(function (err) {
                    alert(err);
                }.bind(this))
                .finally(() => {
                    this.loading = false
                    this.fetchProcedures()
                });
        },

        async selectImage(item) {
            try {
                let filePath = await openFilePicker('画像を選択', [{ name: 'Images', extensions: ['png'] }]);
                if (filePath) item.型図パス = filePath;
            } catch (err) {
            }
        },
    },

    watch: {
        editable_items: {
            deep: true,
            handler() {
                this.editable_items.sort((a, b) => (a.作業順 ?? 0) - (b.作業順 ?? 0));
                this.editable_items.forEach((item, index) => {
                    if (typeof item.削除区分 === 'undefined') {
                        item.削除区分 = false;
                    }
                });
            }
        },
        work_number: {
            handler(newVal) {
                this.fetchProcedures();
            }
        },
        loading: async function (val) {
            if (val) {
                await this.$refs.mloading.start([]);
            } else {
                await this.$refs.mloading.stop([]);
            }
        },
    }
}
</script>

<style scoped>
.v-text-field>>>input {
    border-style: none;
}

.text-disabled-input :deep(.v-field__input) {
    color: rgb(var(--v-theme-on-surface));
    opacity: var(--v-disabled-opacity, 0.38);
}
</style>
