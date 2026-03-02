<template>

    <Head title="出庫依頼" />
    <UserLayout>
        <template #header>出庫依頼</template>

        <template #main>
            <v-container fluid>
                <v-row class="mt-12">
                    <v-btn color="primary" size="large" class="ml-5 text-h5" width="150" height="60" @click="GoVerification()">
                        依頼へ
                    </v-btn>
                    <v-btn color="grey" size="large" class="ml-5 text-h5" height="60" width="150" @click="GoHome()">
                            ホームへ
                        </v-btn>
                </v-row>
                <v-row class="mt-7">
                    <v-col cols="3">
                        <span class="text-h5 font-weight-bold">出庫依頼更新</span>
                    </v-col>
                    <v-col cols="3">
                        <span class="text-h5">ライン:<div class="text-h2 font-weight-bold">{{ seisanLine.lineSign }}</div>
                        </span>
                    </v-col>
                </v-row>

                <v-row>
                    <v-btn color="error" size="large" class="ml-3" style="height: 60px; width: 160px; font-size:25px;"
                        @click="regist()">
                        依頼更新
                    </v-btn>
                    <span class="text-h4 mt-3 ml-5">{{ datacount }}件</span>
                </v-row>

                <v-row>
                    <v-col cols="11">
                        <v-table class="ml-3" style="table-layout:fixed;" id="updatelist">
                            <thead>
                                <tr>
                                    <th class="text-center bg-info" style="width:25%; font-size:20px;">品目CD</th>
                                    <th class="text-center bg-info" style="width:50%; font-size:20px;">品目名称</th>
                                    <th class="text-center bg-info" style="width:15%; font-size:20px;">依頼数</th>
                                    <th class="text-center bg-info" style="width:27%;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="rows in updatelist" :key="rows.id">
                                    <td style="font-size:20px;">{{ rows.itemCode }}</td>
                                    <td style="font-size:20px;">{{ rows.itemName }}</td>
                                    <td style="font-size:20px;">{{ rows.requestCount }}</td>
                                    <td>
                                        <v-btn v-if="seisanLine.department == 'EW'" color="primary" size="large"
                                            @click="EditRequestCount(rows)">
                                            変更
                                        </v-btn>
                                        <v-btn color="error" size="large" class="ml-2" @click="Delete(rows.id)">
                                            削除
                                        </v-btn>
                                    </td>
                                </tr>
                            </tbody>
                        </v-table>
                    </v-col>
                </v-row>
                <editItem ref="editItem"></editItem>
            </v-container>
        </template>
    </UserLayout>
</template>


<script setup>
import UserLayout from '@/Layouts/UserLayout.vue';
import { Head } from '@inertiajs/vue3';
import editItem from './IssueRequestStagingList_sub.vue'

defineProps({
    targetLine: {
        type: Object,
    },
    requestQueue: {
        type: Array,
    },
});
</script>

<script>

export default {
    name: 'IssueRequestStagingList',
    components: {
        editItem
    },
    data: () => ({
        requestList: [],
        seisanLine: {},
        renban: 0,
        lock: 0,
        datacount: 0,
        editItemRef: null
    }),
    mounted() {
        this.requestList = this.requestQueue;
    },
    methods: {
        async EditRequestCount(item) {
            const newRequestCount = await this.$refs.editItem.open(item)
            item.requestCount = newRequestCount
        },

        regists() {
            if (this.datacount == 0) return
            if (!window.confirm('更新します。')) return

            axios.post(route('issuerequest.requestqueue.delete'), {
                departmentCode: this.seisanLine.department,
                lineName: this.seisanLine.lineName,
                lineSign: this.seisanLine.lineSign,
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
        },

        Delete(id) {
            if (!window.confirm('削除します。')) return

            axios.post(route('issuerequest.requestqueue.delete'), {
                id: id,
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
        },

        GoVerification() {
            this.$inertia.visit(route('issuerequest.history'), {
                data: {
                    targetLine: this.targetLine,
                }
            });
        },

        GoHome() {
            this.$inertia.visit(route('issuerequest.home'));
        }
    }
}
</script>
