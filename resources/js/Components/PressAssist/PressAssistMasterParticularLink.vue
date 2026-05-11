<template>
    <v-dialog v-model="dialog" persistent width="300">
        <v-card color="seisan_secondary">
            <v-card-text>特殊指示</v-card-text>
            <v-card-text>内容</v-card-text>
            <v-card-actions>
                <v-btn color="primary" @click="close(true)">閉じる</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>


<script>
export default {
    name: 'PressAssistMasterParticularLink',
    data: () => ({
        dialog: false,
        message: '',
        resolveFn: null,
        work_number: null,
        work_order: null,
        target_particulars: []

    }),
    methods: {
        open(work_number, work_order, particulars) {
            this.dialog = true
            this.work_number = work_number
            this.work_order = work_order
            this.target_particulars = particulars

            return new Promise((resolve) => {
                this.resolveFn = resolve
            })
        },
        close(val) {
            this.dialog = false
            this.work_number = null
            this.work_order = null
            this.target_particulars = []

            if (this.resolveFn) {
                this.resolveFn(val)
                this.resolveFn = null
            }
        },

    }
}
</script>


<style scoped></style>
