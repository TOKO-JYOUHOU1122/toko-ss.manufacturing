<template>

    <Head title="プレスアシストマスタ一覧" />
    <UserLayout>
        <template #header>プレスアシストマスタ一覧</template>

        <template #main>
            <v-container fluid class="pt-5">
                <v-row class="pt-5">
                    <v-col cols="12" class="text-center">
                        <v-btn color="primary" width="200" height="50" variant="outlined" class="mx-5"
                            @click="goMasterItem()">品目マスタ</v-btn>
                        <v-btn color="primary" width="200" height="50" variant="outlined" class="mx-5"
                            @click="goMasterProcedure()">加工手順マスタ</v-btn>
                    </v-col>
                </v-row>
                <v-row>
                    <v-col cols="12" class="text-center">
                        <v-btn color="primary" width="200" height="50" variant="outlined" class="mx-5"
                            @click="goMasterPosition()">位置番号マスタ</v-btn>
                        <v-btn color="primary" width="200" height="50" variant="outlined" class="mx-5"
                            @click="goMasterParticular()">特殊指示情報マスタ</v-btn>
                    </v-col>
                </v-row>
            </v-container>
        </template>
    </UserLayout>
</template>

<script setup>
import UserLayout from '@/Layouts/PressAssist/PressAssistLayout.vue';
import { Head } from '@inertiajs/vue3';
defineProps({
});
</script>

<script>
export default {
    name: 'PressAssistMasterTop',

    data: () => ({
    }),
    created() {
    },

    methods: {
        goMasterItem() {
            this.$inertia.visit(route('pressassist.mst.item'))
        },
        goMasterProcedure() {
            this.$inertia.visit(route('pressassist.mst.procedure'))
        },
        goMasterPosition() {
            this.$inertia.visit(route('pressassist.mst.position'))
        },
        goMasterParticular() {
            this.$inertia.visit(route('pressassist.mst.particular'))
        },
    },
}
</script>

<style></style>
