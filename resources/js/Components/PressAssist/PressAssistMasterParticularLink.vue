<template>
    <v-dialog v-model="dialog" persistent max-width="700">
        <v-card>
            <v-card-title class="d-flex align-center">
                <span>特殊指示 (作業番号: {{ work_number }} / 手順: {{ work_order }})</span>
            </v-card-title>
            <v-divider></v-divider>
            <v-card-text>
                <div v-for="(item, index) in editable_items" :key="item.ID || index"
                    :style="{ opacity: item.削除区分 ? 0.4 : 1 }">
                    <v-row dense align="center" class="pb-4">
                        <v-col cols="3"><v-select v-model="item.管理番号" :items="equipment_numbers" label="管理番号"
                                variant="underlined" density="compact" hide-details></v-select></v-col>
                        <v-col cols="3"><v-select v-model="item.指示名" :items="getNamesForEquipment(item.管理番号)"
                                label="指示区分" variant="underlined" density="compact" hide-details></v-select></v-col>
                        <v-col cols="3"><v-select v-model="item.表示1" :items="getCodesForEquipment(item.管理番号, item.指示名)"
                                label="登録コード" variant="underlined" density="compact" hide-details></v-select></v-col>
                        <v-col cols="2"><v-checkbox v-if="item.指示名 === 'シリンダ'" v-model="item.置換フラグ" label="置換"
                                hide-details density="compact" true-value="1" false-value="0"></v-checkbox></v-col>
                        <v-col cols="1">
                            <v-icon v-if="item.削除区分" color="secondary" size="30"
                                @click="item.削除区分 = false">mdi-delete-restore</v-icon>
                            <v-icon v-else color="red" size="30" @click="item.削除区分 = true">mdi-delete</v-icon>
                        </v-col>
                        <v-col cols="12" no-gutters class="mt-n2">
                            <v-text-field v-model="item.条件" label="条件" variant="underlined" density="compact"
                                hide-details></v-text-field>
                        </v-col>
                    </v-row>
                </div>
                <v-row v-if="editable_items.length === 0" dense>
                    <v-col cols="12" class="text-center text-grey py-4">特殊指示がありません</v-col>
                </v-row>
            </v-card-text>
            <v-card-actions>
                <v-btn color="primary" variant="outlined" size="small" @click="addItem()">
                    <v-icon start>mdi-plus</v-icon>追加
                </v-btn>
                <v-spacer></v-spacer>
                <v-btn color="primary" variant="elevated" @click="saveItems()">保存</v-btn>
                <v-btn color="grey" @click="close()">閉じる</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
export default {
    name: 'PressAssistMasterParticularLink',
    data: () => ({
        dialog: false,
        work_number: null,
        work_order: null,
        editable_items: [],
        equipment_numbers: [],
        unique_info: [],
        defaultItem: {
            ID: null,
            管理番号: null,
            登録コード: null,
            条件: null,
            置換フラグ: '0',
        },
    }),
    mounted() {
        this.fetchOptions();
    },
    methods: {
        getCodesForEquipment(equipment_number, instruction_name) {
            if (!equipment_number || !instruction_name) return [];
            return this.unique_info
                .filter(e => e.管理番号 === equipment_number && e.指示区分 === instruction_name)
                .map(e => e.登録コード);
        },
        getNamesForEquipment(equipment_number) {
            if (!equipment_number) return [];
            const names = this.unique_info
                .filter(e => e.管理番号 === equipment_number)
                .map(e => e.指示区分);
            return [...new Set(names)];
        },

        async open(work_number, work_order) {
            this.work_number = work_number;
            this.work_order = work_order;
            this.dialog = true;
            await this.fetchItems();
        },

        close() {
            this.dialog = false;
            this.work_number = null;
            this.work_order = null;
            this.editable_items = [];
        },

        async fetchItems() {
            try {
                const response = await axios.get(route('pressassist.mst.procedure.particular.fetch'), {
                    params: {
                        work_number: this.work_number,
                        work_order: this.work_order,
                    }
                });
                this.editable_items = response.data;
                this.editable_items.forEach((item, index) => {
                    if (typeof item.削除区分 === 'undefined') {
                        item.削除区分 = false;
                    }
                });

            } catch (err) {
                console.error(err);
            }
        },

        fetchOptions() {
            axios.get(route('pressassist.mst.procedure.particular.options'))
                .then(function (response) {
                    this.equipment_numbers = response.data.equipment_numbers;
                    this.unique_info = response.data.unique_info;
                }.bind(this))
                .catch(function (error) {
                    console.error(error);
                    alert('特殊指示のオプションの取得に失敗しました。' + error);
                }.bind(this))
        },

        addItem() {
            const newItem = { ...this.defaultItem };
            this.editable_items.push(newItem);
        },

        saveItems() {
            axios.post(route('pressassist.mst.procedure.particular.regist'), {
                work_number: this.work_number,
                work_order: this.work_order,
                editedItems: this.editable_items,
            })
                .then(function (response) {
                    if (response.data.errMessage) {
                        alert('保存に失敗しました。\n' + response.data.errMessage);
                        return;
                    }

                    this.dialog = false;
                    this.fetchItems();
                    this.$emit('saved');
                }.bind(this))
                .catch(function (err) {
                    alert(err);
                }.bind(this));
        }
    },

    updateParticularWorkOrder(oldWorkOrder, newWorkOrder) {
        if (this.work_order === oldWorkOrder) {
            this.work_order = newWorkOrder;
        }
    },
}
</script>

<style scoped></style>
