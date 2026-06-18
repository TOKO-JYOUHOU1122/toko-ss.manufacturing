<template>

    <Head title="プレスアシスト位置番号" />
    <UserLayout>
        <template #header>プレスアシスト位置番号</template>

        <template #main>
            <v-container fluid class="pt-3">
                <v-row dense class="pt-3 px-2">
                    <v-col cols="12" class="d-flex justify-end">
                        <v-select v-model="tableFilters.管理番号" label="管理番号" :items="equipment_numbers" variant="underlined"
                            density="compact" hide-details="auto" clearable max-width="200" class="px-3"></v-select>
                        <v-text-field label="位置番号" v-model="tableFilters.位置番号" type="text" color="primary"
                            variant="underlined" density="compact" hide-details="auto" clearable max-width="400"
                            class="px-3" />
                    </v-col>
                </v-row>
                <v-divider class="my-2"></v-divider>
                <v-row dense class="pt-1 px-2">
                    <v-col cols="12">
                        <v-data-table :headers="headers" :items="filteredItems" item-value="ID" items-per-page-text="表示行数"
                            items-per-page="20" :loading="loading" density="comfortable">
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
                            <v-row dense>
                                <v-col cols="6">
                                    <v-select v-model="editedItem.管理番号" label="管理番号" :items="equipment_numbers"
                                        item-title="equipment_numbers" item-value="equipment_numbers" variant="underlined"
                                        hide-details="auto" max="15"></v-select>
                                </v-col>
                                <v-col cols="4">
                                    <v-text-field v-model="editedItem.位置番号" label="位置番号" variant="underlined"
                                        hide-details="auto" maxlength=20></v-text-field>
                                </v-col>
                                <v-col cols="2">
                                    <v-text-field v-model="editedItem.モニタ番号" label="モニタ番号" variant="underlined"
                                        hide-details="auto" maxlength=20></v-text-field>
                                </v-col>
                            </v-row>
                            <v-row no-gutters class="mt-5 mb-n2">
                                <v-col cols="12">
                                    <span class="text-subtitle-2">入力ピン</span>
                                </v-col>
                            </v-row>
                            <v-row dense>
                                <v-col cols="4">
                                    <v-text-field label="検知ピン番号" v-model="editedItem.入力_検知ピン番号" type="number" color="primary"
                                        variant="underlined" min="0" hide-details />
                                </v-col>
                                <v-col cols="4">
                                    <v-text-field label="FSピン番号" v-model="editedItem.入力_フットスイッチピン番号" type="number" color="primary"
                                        variant="underlined" min="0" hide-details />
                                </v-col>
                                <v-col cols="4">
                                    <v-text-field label="プレス完了ピン番号" v-model="editedItem.入力_プレス完了ピン番号" type="number" color="primary"
                                        variant="underlined" min="0" hide-details />
                                </v-col>
                            </v-row>
                            <v-row no-gutters class="mt-5 mb-n2">
                                <v-col cols="12">
                                    <span class="text-subtitle-2">出力ピン</span>
                                </v-col>
                            </v-row>
                            <v-row dense>
                                <v-col cols="4">
                                    <v-text-field label="プレスピン番号" v-model="editedItem.出力_プレスピン番号" type="number" color="primary"
                                        variant="underlined" min="0" hide-details />
                                </v-col>
                                <v-col cols="4">
                                    <v-text-field label="LEDピン番号" v-model="editedItem.出力_ライトピン番号" type="number" color="primary"
                                        variant="underlined" min="0" hide-details />
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
                <ConfirmDialog v-model="dialogDelete" :title="`位置番号:【${editedItem.位置番号}】`" message="上記位置番号を削除します。"
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
    positions: {
        type: Array,
    },
});
</script>

<script>

export default {
    name: 'PressAssistMasterPosition',
    data: () => ({
        headers: [
            { title: 'ID', key: 'ID', headerProps: { class: 'd-none' }, cellProps: { class: 'd-none' } },
            { title: '管理番号', key: '管理番号', width: '100px' },
            { title: '位置番号', key: '位置番号', width: '100px' },
            { title: 'モニタ番号', key: 'モニタ番号', width: '50px' },
            { title: '検知ピン', key: '入力_検知ピン番号', width: '50px', align: 'center' },
            { title: 'FSピン', key: '入力_フットスイッチピン番号', width: '50px', align: 'center' },
            { title: '完了ピン', key: '入力_プレス完了ピン番号', width: '50px', align: 'center' },
            { title: 'プレスピン', key: '出力_プレスピン番号', width: '50px', align: 'center' },
            { title: 'LEDピン', key: '出力_ライトピン番号', width: '50px', align: 'center' },
            { title: '', key: 'actions', sortable: false, width: '80px' },
        ],
        editable_items: [],
        editedIndex: -1,
        editedItem: {},
        defaultItem: {
            ID: null,
            管理番号: '',
            位置番号: '',
            モニタ番号: '',
            入力_検知ピン番号: null,
            入力_フットスイッチピン番号: null,
            入力_プレス完了ピン番号: null,
            出力_プレスピン番号: null,
            出力_ライトピン番号: null,
        },
        equipment_numbers: [],
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
        filteredItems() {
            return this.editable_items.filter(item => {
                return (
                    (!this.tableFilters.管理番号 || item.管理番号 == this.tableFilters.管理番号)
                    && (!this.tableFilters.位置番号 || item.位置番号.includes(this.tableFilters.位置番号))
                )
            });
        }
    },

    methods: {
        init: function () {
            this.editable_items = this.positions;
            this.editedItem = Object.assign({}, this.defaultItem);
            this.tableFilters = Object.assign({}, this.defaultItem);
        },

        fetchPositions() {
            axios.get(route('pressassist.mst.position.fetch'))
                .then(function (response) {
                    this.editable_items = response.data;
                }.bind(this))
                .catch(function (error) {

                }.bind(this))
        },

        getSelectList() {
            const groups = this.editable_items.reduce((acc, item) => {
                for (const key of ['管理番号']) {
                    const keyValue = item[key];
                    (acc[keyValue] ??= []).push(item);
                }
                return acc;
            }, {});

            this.equipment_numbers = [...new Set(this.editable_items.map(item => item.管理番号))];
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
            axios.post(route('pressassist.mst.position.regist'), {
                editedItem: this.editedItem
            })
                .then(function (response) {
                    if (!response.data.errMessage) {
                        this.closeEdit()
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
                    this.fetchPositions()
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
            axios.post(route('pressassist.mst.position.delete'), {
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
                    this.fetchPositions()
                });
        },

        dataExport() {
            if (!this.tableFilters.管理番号) {
                alert('管理番号を選択してください。');
                return;
            }
            let param = { equipment_number: this.tableFilters.管理番号, position_number: this.tableFilters.位置番号 };
            downloadCsv('pressassist.mst.position.export', param)
        },

        async dataImport() {
            let file = null;
            try {
                file = await openFilePicker('ファイルを選択',[{ name: 'CSV Files', extensions: ['csv'] }]);
            } catch (error) {
                return;
            }

            if (!file || file.length === 0) return;
            const csvFile = fileGenarater(file, 'text/csv');

            try {
                await uploadCsv('pressassist.mst.position.import', csvFile, {});
                alert('インポートが完了しました。');
                this.fetchPositions();
            } catch (error) {
                console.error(error);
                alert(error.message);
            }
        },
    },

    watch: {
        editable_items: {
            deep: true,
            handler() {
                this.getSelectList();
            }
        },
    }
}
</script>

<style scoped>
.v-text-field>>>input {
    border-style: none;
}
</style>
