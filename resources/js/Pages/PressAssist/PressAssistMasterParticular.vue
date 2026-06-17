<template>

    <Head title="プレスアシスト特殊指示番号" />
    <UserLayout>
        <template #header>プレスアシスト特殊指示番号</template>

        <template #main>
            <v-container fluid class="pt-3">
                <v-row dense class="pt-3 px-2">
                    <v-col cols="12" class="d-flex justify-end">
                        <v-select v-model="tableFilters.管理番号" label="管理番号" :items="equipment_numbers"
                            variant="underlined" density="compact" hide-details="auto" clearable max-width="200"
                            class="px-3"></v-select>
                        <v-select v-model="tableFilters.指示区分" label="指示区分" :items="categories" variant="underlined"
                            density="compact" hide-details="auto" clearable max-width="200" class="px-3"></v-select>
                        <v-select v-model="tableFilters.段位置" label="段位置"
                            :items="[...new Set(position_numbers.map(item => item.段位置))]" variant="underlined"
                            density="compact" hide-details="auto" clearable max-width="200" class="px-3"></v-select>
                        <v-text-field v-model="tableFilters.登録コード" label="登録コード" variant="underlined" density="compact"
                            hide-details="auto" clearable max-width="200" class="px-3"></v-text-field>
                    </v-col>
                </v-row>
                <v-divider class="my-2"></v-divider>
                <v-row dense class="pt-1 px-2">
                    <v-col cols="12">
                        <v-data-table :headers="headers" :items="filteredItems" item-value="ID"
                            items-per-page-text="表示行数" items-per-page="20" :loading="loading" density="comfortable">
                            <template v-slot:[`item.actions`]="{ item }">
                                <v-icon size="large" color="primary" class="mr-3"
                                    @click="onEdit(item)">mdi-pencil</v-icon>
                                <v-icon size="large" color="red" @click="onDelete(item)">mdi-delete</v-icon>
                            </template>
                        </v-data-table>
                    </v-col>
                </v-row>

                <v-dialog v-model="dialog" max-width="600px" persistent no-click-animation>
                    <v-card>
                        <v-card-title class="headerColor">
                            <span class="text-h5">{{ formTitle }}</span>
                        </v-card-title>
                        <v-card-text>
                            <v-row no-gutters class="mb-3">
                                <v-col cols="6">
                                    <v-select v-model="editedItem.指示区分" label="指示区分" :items="categories"
                                        variant="underlined" hide-details="auto" max-width="300"></v-select>
                                </v-col>
                                <v-col cols="6">
                                    <v-text-field v-model="editedItem.登録コード" label="登録コード" variant="underlined"
                                        hide-details="auto" maxlength=20 :rules="rulesInsert"></v-text-field>
                                </v-col>
                            </v-row>
                            <v-row dense>
                                <v-col cols="6">
                                    <v-select v-model="editedItem.管理番号" label="管理番号" :items="equipment_numbers"
                                        item-title="equipment_numbers" item-value="equipment_numbers"
                                        variant="underlined" hide-details="auto" max="15"></v-select>
                                </v-col>
                                <v-col cols="4">
                                    <v-select v-model="editedItem.段位置" label="段位置" :items="positions"
                                        variant="underlined" hide-details="auto" max="15"></v-select>
                                </v-col>
                                <v-col cols="2">
                                    <v-text-field v-model="editedItem.モニタ番号" label="モニタ番号" variant="underlined"
                                        hide-details="auto" maxlength=20></v-text-field>
                                </v-col>
                            </v-row>
                            <v-row dense>
                                <v-col cols="12">
                                    <v-text-field label="条件" v-model="editedItem.条件" type="text" color="primary"
                                        variant="underlined" hide-details />
                                </v-col>
                            </v-row>
                            <v-row v-if="editedItem && editedItem.指示区分 == 'シリンダ'" dense>
                                <v-col cols="4">
                                    <v-text-field label="入力ピン番号" v-model="editedItem.入力ピン番号" type="number"
                                        color="primary" variant="underlined" min="0" hide-details />
                                </v-col>
                                <v-col cols="4">
                                    <v-text-field label="出力ピン番号" v-model="editedItem.出力ピン番号" type="number"
                                        color="primary" variant="underlined" min="0" hide-details />
                                </v-col>
                                <v-col cols="4">
                                    <v-checkbox v-model="editedItem.置換フラグ" label="置換フラグ" hide-details="auto"
                                        true-value="1" false-value="0" density="compact" />
                                </v-col>
                            </v-row>
                            <v-row>
                                <v-col cols="6">
                                    <v-text-field :label="editedItem && editedItem.指示区分 == '治具照合' ? '治具No' : '表示1'"
                                        v-model="editedItem.表示1" color="primary" variant="underlined" hide-details />
                                </v-col>
                                <v-col cols="6">
                                    <v-text-field :label="editedItem && editedItem.指示区分 == '治具照合' ? '照合値' : '表示2'"
                                        v-model="editedItem.表示2" color="primary" variant="underlined" hide-details />
                                </v-col>
                            </v-row>
                        </v-card-text>

                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn color="grey" variant="elevated" @click="closeEdit()">閉じる</v-btn>
                            <v-btn color="primary" variant="elevated" @click="saveEdit()">保存</v-btn>
                        </v-card-actions>
                    </v-card>
                </v-dialog>
                <ConfirmDialog v-model="dialogDelete" :title="`登録コード:【${editedItem.登録コード}】`" message="上記登録コードを削除します。"
                    btn1Text="キャンセル" btn2Text="削除" @btn1Click="closeDelete()" @btn2Click="deleteItemConfirm()" />
            </v-container>
        </template>
        <template #footer>
            <v-row dense>
                <v-col cols="12" class=" d-flex justify-end">
                    <v-btn color="secondary" class="mx-2" width="150" variant="elevated"
                        @click="dataExport()">エクスポート</v-btn>
                    <v-btn color="red" class="mx-2" width="150" variant="elevated" @click="dataImport()">インポート</v-btn>
                    <v-spacer></v-spacer>
                    <v-btn color="primary" class="mx-2" width="150" variant="elevated"
                        @click="dialog = !dialog">新規作成</v-btn>
                </v-col>
            </v-row>
        </template>
    </UserLayout>
</template>

<script setup>
import UserLayout from '@/Layouts/PressAssist/PressAssistLayout.vue';
import { Head } from '@inertiajs/vue3';
import ConfirmDialog from '../../Components/ConfirmDialog.vue'
import { downloadCsv, openFilePicker, uploadCsv } from '../../util';

defineProps({
    position_numbers: {
        type: Array,
    },
    particulars: {
        type: Array,
    },
});
</script>

<script>

export default {
    name: 'PressAssistMasterParticular',
    data: () => ({
        headers: [
            { title: 'ID', key: 'ID', headerProps: { class: 'd-none' }, cellProps: { class: 'd-none' } },
            { title: '管理番号', key: '管理番号', width: '60px' },
            { title: '指示区分', key: '指示区分', width: '80px' },
            { title: '登録コード', key: '登録コード', width: '80px' },
            { title: '段位置', key: '段位置', width: '60px' },
            { title: '条件', key: '条件', width: '200px', align: 'center' },
            { title: '入力ピン', key: '入力ピン番号', width: '60px', align: 'center' },
            { title: '出力ピン', key: '出力ピン番号', width: '60px', align: 'center' },
            { title: '', key: 'actions', sortable: false, width: '80px' },
        ],
        editable_items: [],
        editedIndex: -1,
        editedItem: {},
        defaultItem: {
            ID: null,
            管理番号: '',
            指示区分: '',
            登録コード: '',
            段位置: '',
            モニタ番号: '',
            条件: '',
            入力ピン番号: null,
            出力ピン番号: null,
            置換フラグ: false,
            表示1: '',
            表示2: '',
        },
        equipment_numbers: [],
        categories: [],
        tableFilters: {},
        loading: false,
        dialogDelete: false,
        dialog: false,
    }),
    mounted() {
        this.init()
    },

    computed: {
        formTitle() {
            return this.editedIndex === -1 ? 'マスタ作成' : 'マスタ編集';
        },
        positions() {
            if (!this.editedItem.管理番号) return [];

            let target_items = this.position_numbers.filter(item => item.管理番号 === this.editedItem.管理番号);
            return [...new Set(target_items.map(item => item.段位置))];
        },
        filteredItems() {
            return this.editable_items.filter(item => {
                return (
                    (!this.tableFilters.管理番号 || item.管理番号 == this.tableFilters.管理番号)
                    && (!this.tableFilters.指示区分 || item.指示区分 == this.tableFilters.指示区分)
                    && (!this.tableFilters.段位置 || item.段位置 == this.tableFilters.段位置)
                    && (!this.tableFilters.登録コード || item.登録コード.includes(this.tableFilters.登録コード))
                )
            });
        },
        rulesInsert(v) {
            return [
                v => !!v || '登録コードは必須です',
                v => !this.editable_items.some(item => item.登録コード === v) || '既に存在する登録コードです',
            ]
        },
    },

    methods: {
        init: function () {
            this.editable_items = this.particulars;
            this.equipment_numbers = [...new Set(this.editable_items.map(item => item.管理番号))];
            this.categories = [...new Set(this.editable_items.map(item => item.指示区分))];
            this.editedItem = Object.assign({}, this.defaultItem);
            this.tableFilters = Object.assign({}, this.defaultItem);
        },

        fetchParticulars() {
            axios.get(route('pressassist.mst.particular.fetch'))
                .then(function (response) {
                    this.editable_items = response.data;
                }.bind(this))
                .catch(function (error) {

                }.bind(this))
        },

        /**
         * @description 編集クリック時処理
         * @param {$event} event
         * @param {int} drop_index
         */
        onEdit(item) {
            this.editedIndex = this.editable_items.indexOf(item)
            this.editedItem = Object.assign({}, item)
            this.dialog = true
        },

        /**
         * @description 削除クリック時処理
         * @param {array} item
         */
        onDelete(item) {
            this.editedIndex = this.editable_items.indexOf(item)
            this.editedItem = Object.assign({}, item)
            this.dialogDelete = true
        },

        /**
         * @description 閉じるクリック時処理
         */
        closeEdit() {
            this.dialog = false
            this.$nextTick(() => {
                this.editedItem = Object.assign({}, this.defaultItem)
                this.editedIndex = -1
            })
        },

        /**
         * @description 保存クリック時処理
         */
        saveEdit() {
            this.loading = true
            axios.post(route('pressassist.mst.particular.regist'), {
                editedItem: this.editedItem
            })
                .then(function (response) {
                    if (!response.data.errMessage) {
                        this.close()
                    } else {
                        alert('マスタの更新に失敗しました。\n' + response.data.errMessage)
                        console.log(response.data.errMessage)
                    }
                }.bind(this))
                .catch(function (err) {
                    alert(err);
                }.bind(this))
                .finally(() => {
                    this.loading = false
                    this.fetchParticulars()
                });
        },

        /**
         * 削除ダイアログ閉じるクリック時
         */
        closeDelete() {
            this.dialogDelete = false
            this.$nextTick(() => {
                this.editedItem = Object.assign({}, this.defaultItem)
                this.editedIndex = -1
            })
        },

        deleteItemConfirm() {
            this.loading = true
            axios.post(route('pressassist.mst.particular.delete'), {
                ID: this.editedItem.ID,
            })
                .then(function (response) {
                    if (!response.data.errMessage) {
                        this.closeDelete()
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
                    this.fetchParticulars()
                });
        },

        dataExport() {
            if (!this.tableFilters.管理番号) {
                alert('管理番号を選択してください。');
                return;
            }
            let param = {
                equipment_number: this.tableFilters.管理番号,
                category: this.tableFilters.指示区分,
                position: this.tableFilters.段位置,
            };
            downloadCsv('pressassist.mst.particular.export', param)
        },

        async dataImport() {
            let file = null;
            try {
                file = await openFilePicker('ファイルを選択', [{ name: 'CSV Files', extensions: ['csv'] }]);
            } catch (error) {
                return;
            }

            if (!file || file.length === 0) return;
            const csvFile = fileGenarater(file, 'text/csv');

            try {
                await uploadCsv('pressassist.mst.particular.import', csvFile, {});
                alert('インポートが完了しました。');
                this.fetchParticulars();
            } catch (error) {
                console.error(error);
                alert(error.message);
            }
        },
    },
}
</script>

<style scoped>
.v-text-field>>>input {
    border-style: none;
}
</style>
