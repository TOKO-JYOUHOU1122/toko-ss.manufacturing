<template>
    <v-dialog v-model="dialog" persistent max-width="250">
        <v-card color="primary" dark>
            <v-card-title class="d-flex align-center pa-1">
                <span class="w-100 text-center text-h5">ユーザー選択</span>
                <v-btn variant="text" @click="result(null)" style="border-radius: 0;">
                    <v-icon size="32">mdi-close</v-icon>
                </v-btn>
            </v-card-title>
            <v-card-text class="pa-0">
                <div style="max-height: 300px; overflow-y: auto;">
                    <v-list class="pa-0">
                        <v-list-item v-for="u in users" :key="u.id" @click="result(u)" style="cursor: pointer;">
                            <v-list-item-title class="text-h5 pl-5">{{ u.user_name }}</v-list-item-title>
                        </v-list-item>
                    </v-list>
                </div>
            </v-card-text>
        </v-card>
    </v-dialog>
</template>

<script>
export default {
    name: 'UserSelectorDialog',
    data() {
        return {
            dialog: false,
            resolveFn: null,
            err_message: '',
            users: [],
        }
    },
    props: {
        department_code: {
            type: String,
            required: true
        },
        line_sign: {
            type: String,
            required: true
        },
    },
    mounted() {
        const response = axios.get(route('util.fetchusers'), {
            params: {
                department_code: this.department_code,
                line_sign: this.line_sign,
            }
        })
        .then(response => {
            this.users = response.data
        })
        .catch(error => {
            console.error(error);
        });
    },
    methods: {
        open() {
            this.dialog = true
            return new Promise((resolve) => {
                this.resolveFn = resolve
            })
        },
        result(val) {
            this.dialog = false
            if (this.resolveFn) {
                this.resolveFn(val)
                this.resolveFn = null
            }
        }
    }
}
</script>

<style scoped>
.v-list-item:nth-child(odd) {
    background-color: #f5f5f5;
}
.v-list-item:nth-child(even) {
    background-color: #e0e0e0;
}
.v-list-item:hover {
    background-color: #e6b422 !important;
}
</style>
