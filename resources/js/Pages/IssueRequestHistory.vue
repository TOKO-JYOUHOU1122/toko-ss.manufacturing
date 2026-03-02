<template>
    <Head title="出庫依頼履歴" />
    <UserLayout>
        <template #header>出庫依頼履歴</template>

        <template #main>
            <v-container fluid>
                <v-row class="mt-8">
                    <v-col>
                        <v-btn color="grey" size="large" class="ml-5 text-h5" height="60" width="150" @click="GoHome()">
                            ホームへ
                        </v-btn>
                    </v-col>
                </v-row>

                <v-row>
                    <v-col cols="3" class="mt-3 text-h4">
                        出庫依頼履歴
                    </v-col>
                    <v-col cols="6" class="mt-2">
                        <span class="text-h5">ライン:</span>
                        <span class="text-h4 font-weight-bold">{{ targetLine.lineName }}</span>
                    </v-col>
                </v-row>

                <v-row no-gutters>
                    <v-col cols="12" class="mt-3 px-5">
                        <v-data-table :headers="headers" :items="history" class="elevation-1" fixed-header />
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
    history: {
        type: Array,
    },

});
</script>

<script>
export default {
    name: 'IssueRequestHistory',
    mounted() {
        console.log(this.history);
    },
    data: () => ({
        headers: [
            { title: '出庫番号', key: 'shippingNumber', align: 'center', sortable: true, width: '10%'},
            { title: '依頼日', key: 'requestDate', align: 'center', sortable: true, width: '10%'},
            { title: '品目CD', key: 'itemCode', align: 'center', sortable: true, width: '25%'},
            { title: '品目名称', key: 'itemName', align: 'center', sortable: true, width: '30%'},
            { title: '依頼残数', key: 'requestCount', align: 'center', sortable: true, width: '15%'},
        ],
    }),
    methods: {
        GoHome() {
            this.$inertia.visit(route('issuerequest.home'));
        }
    }
}
</script>

<style scoped>
::v-deep(.v-data-table thead th) {
  background-color: #3c9cfd !important; /* Vuetify primary */
  color: white;
  font-size: 18px;
}
</style>
