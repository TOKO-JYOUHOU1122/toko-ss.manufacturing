<template>
    <v-dialog v-model="dialog" persistent width="300">
        <v-card color="seisan_secondary">
            <v-card-text>
                しばらくお待ちください
                <v-progress-linear indeterminate class="mb-0 headerColor" />
            </v-card-text>
        </v-card>
    </v-dialog>
</template>

<script>
export default {
    data() {
        return {
            dialog: false,
            resolve: null,
            reject: null,
            options: {},
        }
    },
    methods: {
        start(options = {}) {
            this.dialog = true
            this.options = { ...this.options, ...options }
            return new Promise((resolve, reject) => {
                this.resolve = resolve
                this.reject = reject
            })
        },
        stop() {
            this.dialog = false
        }
    }
}
</script>
