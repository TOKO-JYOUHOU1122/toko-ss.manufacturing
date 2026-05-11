<template>

    <Head title="プレスアシスト品目" />
    <UserLayout>
        <template #header>プレスアシスト品目</template>

        <template #main>
            <v-container fluid class="pt-3">
                <v-row dense class="pt-3 px-2">
                    <v-col cols="12" class="d-flex justify-end">
                        <v-select v-model="tableFilters.機種" label="区分" :items="divisions" variant="underlined"
                            density="compact" hide-details="auto" clearable max-width="200" class="px-3"></v-select>
                        <v-combobox label="作業番号" v-model="tableFilters.作業番号" :items="work_numbers" color="primary"
                            variant="underlined" density="compact" hide-details="auto" clearable max-width="400"
                            class="px-3" />
                        <v-text-field label="品名" v-model="tableFilters.品名" type="text" color="primary"
                            variant="underlined" density="compact" hide-details="auto" clearable max-width="400"
                            class="px-3" />
                    </v-col>
                </v-row>
                <v-divider class="my-2"></v-divider>
                <v-row dense class="pt-1 px-2">
                    <v-col cols="12">
                        <v-data-table :headers="headers" :items="filteredItems" item-value="ID" items-per-page-text="表示行数"
                            items-per-page="20" :loading="loading" density="comfortable">
                            <template #item.作業番号="{ item }">
                                <v-hover v-slot="{ isHovering, props }">
                                    <div v-bind="props" class="b-cell" :class="{ 'b-cell--hover': isHovering }">
                                        <!-- ホバー時にツールチップ -->
                                        <v-tooltip :text="'作業番号 : ' + item.作業番号 + 'のマスタへ'" location="top" activator="parent" open-delay="200" />
                                        <span>{{ item.作業番号 }}</span>

                                        <v-btn size="x-small" variant="text" color="primary"
                                            class="ml-2" @click.stop="goProcedures(item.作業番号)">
                                            <v-icon size="20">mdi-open-in-new</v-icon>
                                        </v-btn>
                                    </div>
                                </v-hover>
                            </template>

                            <template v-slot:[`item.actions`]="{ item }">
                                <v-icon size="large" color="primary" class="mr-3"
                                    @click="onEdit(item)">mdi-pencil</v-icon>
                                <v-icon size="large" color="red" @click="onDelete(item)">mdi-delete</v-icon>
                            </template>
                        </v-data-table>
                    </v-col>
                </v-row>

                <v-dialog v-model="dialog" max-width="500px" persistent no-click-animation>
                    <v-card>
                        <v-card-title class="headerColor">
                            <span class="text-h5">{{ formTitle }}</span>
                        </v-card-title>
                        <v-card-text>
                            <v-row dense>
                                <v-col cols="6">
                                    <v-select v-model="editedItem.機種" label="区分" :items="divisions"
                                        item-title="division" item-value="division" variant="underlined"
                                        hide-details="auto" max="15"></v-select>
                                </v-col>
                                <v-col cols="3">
                                    <v-text-field v-model="editedItem.作業番号" label="作業番号" variant="underlined"
                                        hide-details="auto" maxlength=20></v-text-field>
                                </v-col>
                                <v-col cols="3">
                                    <v-text-field label="表示順" v-model="editedItem.表示順" type="number" color="primary"
                                        variant="underlined" min="1" hide-details />
                                </v-col>
                            </v-row>
                            <v-row dense>
                                <v-col cols="9">
                                    <v-text-field label="品名" v-model="editedItem.品名" type="text" color="primary"
                                        variant="underlined" hide-details clearable />
                                </v-col>
                                <v-col cols="3">
                                    <v-text-field label="同時加工数" v-model="editedItem.同時加工数" type="number" color="primary"
                                        variant="underlined" min="1" hide-details />
                                </v-col>
                            </v-row>
                            <v-row dense>
                                <v-col cols="12">
                                    <v-textarea v-model="editedItem.条件" label="条件" variant="underlined" hide-details
                                        maxlength=100 rows="1" placeholder="例:[下枠]='25D'    []内は変数の為情報システムに要確認" auto-grow clearable></v-textarea>
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
                <ConfirmDialog v-model="dialogDelete" :title="`品名:【${editedItem.品名}】`" message="上記品目を削除します。"
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
import { downloadCsv, openFilePicker, fileGenarater, uploadCsv } from '../../util';

defineProps({
    items: {
        type: Array,
    },
});
</script>

<script>

export default {
    name: 'PressAssistMasterItem',
    data: () => ({
        headers: [
            { title: 'ID', key: 'ID', headerProps: { class: 'd-none' }, cellProps: { class: 'd-none' } },
            { title: '区分', key: '機種', width: '160px' },
            { title: '作業番号', key: '作業番号', width: '120px' },
            { title: '品名', key: '品名' },
            { title: '表示品名', key: '表示品名', headerProps: { class: 'd-none' }, cellProps: { class: 'd-none' } },
            { title: '条件', key: '条件' },
            { title: '同時加工数', key: '同時加工数', headerProps: { class: 'd-none' }, cellProps: { class: 'd-none' } },
            { title: '表示順', key: '表示順', width: '100px' },
            { title: '', key: 'actions', sortable: false, width: '100px' },
        ],
        editable_items: [],
        editedIndex: -1,
        editedItem: {},
        defaultItem: {
            ID: null,
            機種: '',
            作業番号: '',
            品名: '',
            表示品名: '',
            表示順: null,
            同時加工数: 1,
            条件: '',
        },
        divisions: [],
        work_numbers: [],
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
                    (!this.tableFilters.機種 || item.機種 == this.tableFilters.機種)
                    && (!this.tableFilters.作業番号 || item.作業番号.includes(this.tableFilters.作業番号))
                    && (!this.tableFilters.品名 || item.品名.includes(this.tableFilters.品名))
                )
            });
        }
    },

    methods: {
        init: function () {
            this.editable_items = this.items;
            this.editedItem = Object.assign({}, this.defaultItem);
            this.tableFilters = Object.assign({}, this.defaultItem);
        },

        fetchItems() {
            axios.get(route('pressassist.mst.item.fetch'))
                .then(function (response) {
                    this.editable_items = response.data;
                }.bind(this))
                .catch(function (error) {

                }.bind(this))
        },

        getSelectList() {
            const groups = this.editable_items.reduce((acc, item) => {
                for (const key of ['機種', '作業番号']) {
                    const keyValue = item[key];
                    (acc[keyValue] ??= []).push(item);
                }
                return acc;
            }, {});

            this.divisions = [...new Set(this.editable_items.map(item => item.機種))];
            this.work_numbers = [...new Set(this.editable_items.map(item => item.作業番号))];
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
            axios.post(route('pressassist.mst.item.regist'), {
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
                    this.fetchItems()
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
            axios.post(route('pressassist.mst.item.delete'), {
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
                    this.fetchItems()
                });
        },

        dataExport() {
            if (!this.tableFilters.機種) {
                alert('区分を選択してください。');
                return;
            }
            let param = { division: this.tableFilters.機種, work_number: this.tableFilters.作業番号, name: this.tableFilters.品名 };
            downloadCsv('pressassist.mst.item.export', param)
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
                await uploadCsv('pressassist.mst.item.import', csvFile, {});
                alert('インポートが完了しました。');
                this.fetchItems();
            } catch (error) {
                console.error(error);
                alert(error.message);
            }
        },

        goProcedures(work_number) {
            const url = route('pressassist.mst.procedure', { work_number: work_number })
            window.open(url, '_blank');
        }
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
