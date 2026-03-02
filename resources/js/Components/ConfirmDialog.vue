<template>
    <v-dialog v-model="internalModelValue" persistent max-width="800px">
        <v-card>
            <v-card-title class="text-h6 headerColor">
                {{ title }}
            </v-card-title>
            <v-card-text>
                <slot>
                    {{ message }}
                </slot>
            </v-card-text>
            <v-card-actions>
                <v-spacer />
                <v-btn width="100" :color="btn1Text === '削除' ? 'red' : 'primary'" variant="outlined"
                    @click="onClickBtn1">
                    {{ btn1Text }}
                </v-btn>
                <v-btn width="100" :color="btn2Text === '削除' ? 'red' : 'primary'" variant="outlined"
                    @click="onClickBtn2">
                    {{ btn2Text }}
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
export default {
    name: 'ConfirmDialog',
    props: {
        modelValue: {
            type: Boolean,
            required: true,
        },
        title: {
            type: String,
            default: '確認',
        },
        message: {
            type: String,
            default: '本当に実行しますか？',
        },
        btn1Text: {
            type: String,
            default: 'キャンセル',
        },
        btn2Text: {
            type: String,
            default: 'OK',
        },
    },
    emits: ['update:modelValue', 'btn1Click', 'btn2Click'],
    computed: {
        internalModelValue: {
            get() {
                return this.modelValue
            },
            set(val) {
                this.$emit('update:modelValue', val)
            },
        },
    },
    methods: {
        onClickBtn1() {
            this.internalModelValue = false
            this.$emit('btn1Click')
        },
        onClickBtn2() {
            this.internalModelValue = false
            this.$emit('btn2Click')
        },
    },
}
</script>
