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
                        <v-select v-model="work_number" label="作業番号" :items="editable_work_numbers" variant="underlined"
                            density="compact" hide-details="auto" clearable max-width="200" class="px-3"></v-select>
                    </v-col>
                </v-row>
                <v-divider class="my-2"></v-divider>
                <draggable v-model="editable_items" item-key="_dragKey" handle=".drag-handle" @start="isDragging = true" @end="onDragEnd">
                    <template #item="{ element: item, index }">
                    <div :class="{ 'even-row': index % 2 === 1 }">
                    <v-row no-gutters class="py-3" :style="{ opacity: item.削除区分 ? 0.4 : 1 }">
                        <v-col cols="12" class="d-flex align-end">
                            <v-icon class="drag-handle pl-2 pr-5" size="30" color="gray">mdi-dots-grid</v-icon>
                            <v-label class="pr-3 pb-1" style="width: 20px;">{{ item.作業順 }} .</v-label>
                            <v-select v-model="item.管理番号" label="管理番号" :items="equipment_numbers" variant="underlined"
                                hide-details="auto" max-width="150" class="px-3"></v-select>
                            <v-select v-model="item.段位置" label="段位置" :items="targetPositions(item.管理番号)"
                                variant="underlined" hide-details="auto" max-width="220" class="px-3" multiple chips closable-chips></v-select>
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
                                true-value="1" false-value="0" density="compact" />
                            <HoverTooltip
                                :text="'エアシリンダや治具照合等の\n特殊指示を登録します'" location="left">
                                <template #activator="{ props }">
                                    <v-badge :content="item.特殊指示件数 || 0" :color="item.特殊指示件数 && item.特殊指示件数 > 0 ? 'red' : 'grey'" offset-x="20" offset-y="5">
                                        <v-icon size="40" color="green" class="mr-3" @click="openParticular(item.作業順)" v-bind="props">mdi-toy-brick-plus</v-icon>
                                    </v-badge>
                                </template>
                            </HoverTooltip>
                            <v-icon v-if="item.削除区分" color="secondary" size="40"
                                @click="item.削除区分 = false">mdi-delete-restore</v-icon>
                            <v-icon v-else color="red" size="40" @click="item.削除区分 = true">mdi-delete</v-icon>
                        </v-col>
                    </v-row>
                    </div>
                    </template>
                </draggable>
                <v-row v-if="work_number" no-gutters class="pt-3">
                    <v-col cols="12" class="d-flex justify-center">
                        <v-btn variant="outlined" color="primary" class="ma-2" @click="insertProcedure()">手順追加</v-btn>
                    </v-col>
                </v-row>

                <ParticularLink ref="particular" @saved="fetchProcedures()"></ParticularLink>
                <ConfirmDialog v-model="dialog_confirm.is_show" :title="dialog_confirm.title"
                    :message="dialog_confirm.message" :btn1Text="dialog_confirm.btn1Text"
                    :btn2Text="dialog_confirm.btn2Text" @btn1Click="onDialogBtn1Click()"
                    @btn2Click="onDialogBtn2Click()">
                    <div
                        v-if="isSameObject(dialog_confirm, dialog_insert) || isSameObject(dialog_confirm, dialog_copy)">
                        <span>{{ dialog_confirm.message }}</span>
                        <v-text-field v-model="insert_work_number" label="作業番号" :items="editable_work_numbers"
                            variant="underlined" clearable max-width="300" class="px-3"
                            :rules="rulesInsert"></v-text-field>
                    </div>
                </ConfirmDialog>
                <LoadingModal ref="mloading" />
            </v-container>
        </template>
        <template #footer>
            <v-row dense>
                <v-col cols="12">
                    <div v-if="work_number" class="d-flex justify-end">
                        <v-btn v-if="!isEdited" color="secondary" class="mx-2" width="150" variant="elevated"
                            @click="previewProcedures()">プレビュー</v-btn>
                        <v-btn color="red" class="mx-2" width="150" variant="elevated"
                            @click="openDialog(dialog_delete)">削除</v-btn>
                        <v-spacer></v-spacer>
                        <v-btn v-if="isEdited" color="primary" class="mx-2" width="150" variant="elevated"
                            @click="openDialog(dialog_save)">保存</v-btn>
                        <v-btn color="primary" class="mx-2" width="150" variant="elevated"
                            @click="openDialog(dialog_copy)">このデータを複製</v-btn>
                    </div>
                    <div v-else class="d-flex justify-end">
                        <v-btn color="primary" class="mx-2" width="150" variant="elevated"
                            @click="openDialog(dialog_insert)">新規作成</v-btn>
                    </div>
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
import ParticularLink from '@/components/PressAssist/PressAssistMasterParticularLink.vue';
import { openFilePicker } from '../../util';
import draggable from 'vuedraggable';

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
        editable_work_numbers: [],
        editable_items: [],
        editable_items_Init: [],
        isDragging: false,
        defaultItem: {
            ID: null,
            管理番号: null,
            段位置: [],
            作業順: null,
            型図パス: null,
            画像位置: '1',
            反転フラグ: false,
            削除区分: false,
        },
        equipment_numbers: [],
        work_number: null,
        insert_work_number: null,

        loading: false,
        dialog_insert: { is_show: false, title: '作業番号追加', message: '以下の作業番号を追加します', btn1Text: 'キャンセル', btn2Text: '追加' },
        dialog_copy: { is_show: false, title: 'データ複製', message: '以下の作業番号を作成し、表示中の加工手順を複製します', btn1Text: 'キャンセル', btn2Text: '複製' },
        dialog_delete: { is_show: false, title: '作業番号削除', message: '表示されている作業番号および加工手順を削除します', btn1Text: 'キャンセル', btn2Text: '削除' },
        dialog_save: { is_show: false, title: '作業番号保存', message: '表示されている作業番号および加工手順を保存します', btn1Text: 'キャンセル', btn2Text: '保存' },
        dialog_confirm: { is_show: false, title: '', message: '', btn1Text: '', btn2Text: '' },
    }),
    mounted() {
        this.init()
    },

    computed: {
        isEdited() {
            return (JSON.stringify(this.editable_items) != JSON.stringify(this.editable_items_Init));
        },
        rulesInsert(v) {
            return [
                v => !!v || '作業番号は必須です',
                v => !this.editable_work_numbers.includes(v) || '既に存在する作業番号です',
            ]
        },
    },

    methods: {
        init: function () {
            this.editable_work_numbers = this.work_numbers;
            this.editable_items = this.groupItems(this.procedures);
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
                    this.editable_items = this.groupItems(response.data);
                }.bind(this))
                .catch(function (error) {

                }.bind(this))
                .finally(() => {
                    this.loading = false
                });
        },

        onDialogBtn1Click() {
            if (this.isSameObject(this.dialog_confirm, this.dialog_insert)) this.insert_work_number = null
            if (this.isSameObject(this.dialog_confirm, this.dialog_copy)) this.insert_work_number = null

            this.dialog_confirm.is_show = false
        },

        onDialogBtn2Click() {
            if (this.isSameObject(this.dialog_confirm, this.dialog_insert)) this.insertWorkNumber()
            if (this.isSameObject(this.dialog_confirm, this.dialog_copy)) this.copyProcedures()
            if (this.isSameObject(this.dialog_confirm, this.dialog_delete)) this.deleteProcedures()
            if (this.isSameObject(this.dialog_confirm, this.dialog_save)) this.saveProcedures(this.work_number)

            this.dialog_confirm.is_show = false
        },

        openDialog(dialog_confirm) {
            this.dialog_confirm = dialog_confirm
            this.dialog_confirm.is_show = true
        },

        isSameObject(a, b) {
            const keysA = Object.keys(a);
            const keysB = Object.keys(b);

            if (keysA.length !== keysB.length) return false;

            return keysA.every(
                key => b.hasOwnProperty(key) && a[key] === b[key]
            );
        },

        insertWorkNumber() {
            this.work_number = this.insert_work_number;
        },

        insertProcedure() {
            const newItem = Object.assign({}, this.defaultItem);
            newItem.作業番号 = this.work_number;
            newItem.作業順 = this.editable_items.reduce((max, item) => Math.max(max, Number(item.作業順) || 0), 0) + 1
            newItem._dragKey = Date.now();
            this.editable_items.push(newItem);
        },

        onDragEnd() {
            // 作業順を振りなおす（連続する同じ値のグループは同一番号を維持）
            const originalOrders = this.editable_items.map(item => String(item.作業順));
            let order = 1;
            this.editable_items[0].作業順 = order;
            for (let i = 1; i < this.editable_items.length; i++) {
                if (originalOrders[i] !== originalOrders[i - 1]) {
                    order++;
                }
                this.editable_items[i].作業順 = order;
                this.$refs.particular?.updateParticularWorkOrder?.(originalOrders[i], order);
            }
        },

        /**
         * @description 保存クリック時処理
         */
        saveProcedures(work_number) {
            this.loading = true
            axios.post(route('pressassist.mst.procedure.regist'), {
                work_number: work_number,
                editable_items: this.expandItems(this.editable_items)
            })
                .then(function (response) {
                    if (response.data.errMessage) {
                        alert('マスタの更新に失敗しました。\n' + response.data.errMessage)
                        console.log(response.data.errMessage)
                    } else {
                        this.fetchProcedures()
                        if (!this.editable_work_numbers.includes(work_number)) {
                            this.editable_work_numbers.push(work_number)
                        }
                    }

                    if (this.work_number != work_number) {
                        this.work_number = work_number
                    }
                }.bind(this))
                .catch(function (err) {
                    alert(err);
                }.bind(this))
                .finally(() => {
                    this.loading = false
                });
        },

        /**
         * @description コピークリック時処理
         */
        copyProcedures() {
            if (!this.insert_work_number) return;

            for (const item of this.editable_items) {
                item.ID = null;
            }

            this.saveProcedures(this.insert_work_number);
        },

        deleteProcedures() {
            this.loading = true
            axios.post(route('pressassist.mst.procedure.delete'), {
                work_number: this.work_number,
            })
                .then(function (response) {
                    if (response.data.errMessage) {
                        alert('マスタの削除に失敗しました。\n' + response.data.errMessage)
                        console.log(response.data.errMessage)
                    } else {
                        this.editable_work_numbers = this.editable_work_numbers.filter(num => num !== this.work_number)
                        this.work_number = null
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

        previewProcedures() {
            if (!this.work_number) return;

            const url = route('pressassist.mst.procedure.preview', { work_number: this.work_number })
            window.open(url, '_blank');
        },

        openParticular(work_order) {
            this.$refs.particular?.open?.(this.work_number, work_order);
        },

        groupItems(items) {
            const grouped = [];
            let keyCounter = 0;
            for (const item of items) {
                const existing = grouped.find(g =>
                    g.作業番号 === item.作業番号 &&
                    String(g.作業順) === String(item.作業順) &&
                    g.管理番号 === item.管理番号 &&
                    g.型図パス === item.型図パス &&
                    g.画像位置 === item.画像位置 &&
                    g.反転フラグ === item.反転フラグ
                );
                if (existing) {
                    if (item.段位置 && !existing.段位置.includes(item.段位置)) {
                        existing.段位置.push(item.段位置);
                    }
                } else {
                    grouped.push({
                        ...item,
                        段位置: item.段位置 ? [item.段位置] : [],
                        _dragKey: keyCounter++,
                    });
                }
            }
            return grouped;
        },

        expandItems(items) {
            const expanded = [];
            for (const item of items) {
                const positions = Array.isArray(item.段位置) ? item.段位置 : [item.段位置];
                if (positions.length === 0) {
                    expanded.push({ ...item, 段位置: null });
                } else {
                    for (const pos of positions) {
                        expanded.push({ ...item, 段位置: pos });
                    }
                }
            }
            return expanded;
        },
    },

    watch: {
        editable_items: {
            deep: false,
            handler(newVal) {
                this.editable_items.forEach((item, index) => {
                    if (typeof item.削除区分 === 'undefined') {
                        item.削除区分 = false;
                    }
                });
                if (!this.isDragging) {
                    this.editable_items.sort((a, b) => (a.作業順 ?? 0) - (b.作業順 ?? 0));
                }
                this.editable_items_Init = JSON.parse(JSON.stringify(newVal));
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

.scale-btn:active {
    transform: scale(0.85);
}

.even-row {
    background-color: #f5f5f5;
}

.drag-handle {
    cursor: grab;
}

.drag-handle:active {
    cursor: grabbing;
}

.sortable-ghost {
    opacity: 0.4;
    background-color: #e3f2fd;
}

.sortable-chosen {
    background-color: #f5f5f5;
}
</style>
