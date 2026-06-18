<template>

    <Head title="プレスアシスト加工手順プレビュー" />
    <UserLayout>
        <template #header>プレスアシスト加工手順プレビュー</template>

        <template #main>
            <v-container fluid class="pt-3">
                <v-row dense class="px-2">
                    <v-col cols="6">
                        <v-row dense>
                            <v-col cols="12" class="d-flex justify-start">
                                <h3>管理番号: <span class=" text-red">{{ current_procedure.管理番号 }}</span></h3>
                                <h3 class="pl-5">段位置:
                                    <span v-for="(value, index) in current_procedure.段位置" :key="index" class="text-red">
                                        {{ value }}
                                    </span>
                                </h3>
                                <h3 class="pl-5">モニタ番号: <span class="text-red">{{ current_procedure.モニタ番号 }}</span></h3>
                            </v-col>
                        </v-row>
                        <v-row dense>
                            <v-col cols="12" class="d-flex justify-start">
                                <h3><span class="text-red">{{ current_particular_codes }}</span></h3>
                            </v-col>
                        </v-row>
                    </v-col>
                    <v-col cols="6" class="pl-5">
                        <v-row dense>
                            <v-col cols="12" class="d-flex justify-end">
                                <v-spacer></v-spacer>
                                <h3 style="color: gray;">入力</h3>
                                <HoverTooltip :text="'形材挿入時の検知ピンのピン番号です。\nONになると形材が挿入されたと判定し、プレス可能になります。'">
                                    <template #activator="{ props }">
                                        <h3 class="pl-5" v-bind="props">検知ピン:
                                            <span v-for="(value, index) in current_procedure.入力_検知ピン番号" :key="index">
                                                {{ value }}
                                            </span>
                                        </h3>
                                    </template>
                                </HoverTooltip>
                                <HoverTooltip :text="'フットスイッチのピン番号です。\nこれがONになるとプレス動作が開始されます。'">
                                    <template #activator="{ props }">
                                        <h3 class="pl-5" v-bind="props">FSピン:
                                            <span v-for="(value, index) in current_procedure.入力_フットスイッチピン番号"
                                                :key="index">
                                                {{ value }}
                                            </span>
                                        </h3>
                                    </template>
                                </HoverTooltip>
                                <HoverTooltip :text="'プレス完了のピン番号です。\nプレスの最下点でONになり、この信号を検知するとプレス完了と判定します。'">
                                    <template #activator="{ props }">
                                        <h3 class="pl-5" v-bind="props">完了ピン:
                                            <span v-for="(value, index) in current_procedure.入力_プレス完了ピン番号" :key="index">
                                                {{ value }}
                                            </span>
                                        </h3>
                                    </template>
                                </HoverTooltip>
                            </v-col>
                        </v-row>
                        <v-row dense>
                            <v-col cols="12" class="d-flex justify-end">
                                <v-spacer></v-spacer>
                                <h3 style="color: gray;">出力</h3>
                                <HoverTooltip :text="'プレスを行うピン番号です。\nフットスイッチピンがONになったとき、このピンをONにしてプレス動作を開始します。'">
                                    <template #activator="{ props }">
                                        <h3 class="pl-5" v-bind="props">プレスピン:
                                            <span v-for="(value, index) in current_procedure.出力_プレスピン番号" :key="index">
                                                {{ value }}
                                            </span>
                                        </h3>
                                    </template>
                                </HoverTooltip>
                                <HoverTooltip :text="'(戸建限定)プレス機前方に取り付けられたLEDのピン番号です。\nどこに形材を挿入するかをLEDを点灯させて示します。'">
                                    <template #activator="{ props }">
                                        <h3 class="pl-5" v-bind="props">ライトピン:
                                            <span v-for="(value, index) in current_procedure.出力_ライトピン番号" :key="index">
                                                {{ value }}
                                            </span>
                                        </h3>
                                    </template>
                                </HoverTooltip>
                            </v-col>
                        </v-row>
                    </v-col>
                </v-row>
                <v-divider class="my-2"></v-divider>
                <v-row dense>
                    <v-col cols="12">
                        <div class="image-wrapper" ref="wrapper" :class="{ 'bg-yellow': current_procedure.反転フラグ == 1 }">
                            <v-img :src="`${current_procedure.型図画像}`" ref="imgRef" aspect-ratio="16/9"
                                class="main-image pa-0" contain @load="onImageLoad">
                                <div class="overlay" :style="overlayStyle">
                                    <div v-for="(char, index) in current_procedure.画像位置" :key="index"
                                        class="overlay-cell">
                                        <div v-if="char === '0' && current_procedure.反転フラグ == 0"
                                            class="mask mask-white" />
                                        <div v-if="char === '0' && current_procedure.反転フラグ == 1"
                                            class="mask mask-yellow" />
                                    </div>
                                </div>
                                <div class="overlay d-flex flex-column">
                                    <h1 v-if="current_procedure.反転フラグ == 1" class="text-center text-red">部材加工前に反転</h1>
                                    <div class="flex-grow-1"></div>
                                    <div class="d-flex w-100">
                                        <div v-for="(index) in [7, 6, 5, 4, 3, 2, 1]" :key="index"
                                            class="text-center flex-fill">
                                            <h2 v-if="positon_column == index" class="bg-green">{{ index }}</h2>
                                            <h2 v-else class="text-grey" style="opacity:0.5;">{{ index }}</h2>
                                        </div>
                                    </div>
                                </div>
                            </v-img>
                        </div>
                    </v-col>
                </v-row>

            </v-container>
        </template>
        <template #footer>
            <v-row dense>
                <v-col cols="12" class=" d-flex justify-end">
                    <v-btn color="primary" class="mx-2" width="150" variant="elevated" @click="move(-1)">前へ</v-btn>
                    <v-spacer></v-spacer>
                    <h3>{{ current_index + 1 }} / {{ procedures.length }}</h3>
                    <v-spacer></v-spacer>
                    <v-btn color="primary" class="mx-2" width="150" variant="elevated" @click="move(1)">次へ</v-btn>
                </v-col>
            </v-row>
        </template>
    </UserLayout>
</template>

<script setup>
import UserLayout from '@/Layouts/PressAssist/PressAssistLayout.vue';
import { Head } from '@inertiajs/vue3';
import HoverTooltip from '@/components/HoverTooltip.vue';

defineProps({
    procedures: {
        type: Array,
    },
    particular_instructions: {
        type: Array,
    },

});
</script>

<script>

export default {
    name: 'PressAssistMasterProcedurePreview',
    data: () => ({
        current_procedure: {},
        current_index: 0,
        overlayStyle: {},
        resizeObserver: null,

    }),
    mounted() {
        this.init()
    },

    computed: {
        positon_column() {
            if (!this.current_procedure.段位置 || !this.current_procedure.段位置[0]) return 0;

            const match = this.current_procedure.段位置[0].match(/\d/);
            return match ? match[0] : null;
        },
        current_particular_codes() {
            const list = this.particular_instructions && this.particular_instructions[this.current_index];
            if (!Array.isArray(list) || list.length === 0) return '';
            let msg = "特殊指示コード:";
            for (let i = 0; i < list.length; i++) {
                const code = list[i]?.登録コード ?? '';
                const input = list[i]?.入力ピン番号 ?? '';
                const output = list[i]?.出力ピン番号 ?? '';
                if (code !== '') {
                    msg += (i === 0 ? '' : ',') + `${code} (入力: ${input}  出力: ${output})`;
                }
            }

            return msg;
        }
    },

    methods: {
        init: function () {
            if (this.procedures.length == 0) return;

            this.current_index = 0;
            this.current_procedure = this.procedures[this.current_index];

            this.$nextTick(() => {
                const wrapper = this.$refs.wrapper
                if (!wrapper) return

                this.resizeObserver = new ResizeObserver(() => {
                    this.updateOverlay()
                })

                this.resizeObserver.observe(wrapper)
            })
        },

        move(value) {
            if (this.current_index + value < 0 || this.current_index + value >= this.procedures.length) return;
            this.current_index += value;
            this.current_procedure = this.procedures[this.current_index];
        },

        onImageLoad() {
            this.$nextTick(() => {
                setTimeout(() => {
                    this.updateOverlay()
                }, 0)
            })
        },

        updateOverlay() {
            this.$nextTick(() => {
                const wrapperEl = this.$refs.wrapper
                const imgComponent = this.$refs.imgRef

                if (!wrapperEl || !imgComponent) return

                const imgEl = imgComponent.$el?.querySelector('img')
                if (!imgEl) return

                const naturalWidth = imgEl.naturalWidth
                const naturalHeight = imgEl.naturalHeight
                const wrapperRect = wrapperEl.getBoundingClientRect()
                const wrapperRatio = wrapperRect.width / wrapperRect.height
                const imageRatio = naturalWidth / naturalHeight

                let width, height, top, left

                if (imageRatio > wrapperRatio) {
                    width = wrapperRect.width
                    height = width / imageRatio
                    top = (wrapperRect.height - height) / 2
                    left = 0
                } else {
                    height = wrapperRect.height
                    width = height * imageRatio
                    left = (wrapperRect.width - width) / 2
                    top = 0
                }

                this.overlayStyle = {
                    position: 'absolute',
                    top: `${top}px`,
                    left: `${left}px`,
                    width: `${width}px`,
                    height: `${height}px`
                }
            })
        }
    },

}
</script>

<style scoped>
.v-text-field>>>input {
    border-style: none;
}

.text-disabled-input :deep(.v-field__input) {
    color: rgb(var(--v-theme-on-surface));
    opacity: var(--v-disabled-opacity, 0.38);
}

.image-wrapper {
    position: relative;
    overflow: hidden;
    height: 100%;
}

.main-image {
    object-fit: contain;
    height: 70vh;
}

.overlay {
    position: absolute;
    inset: 0;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(20px, 1fr));
}

.overlay-cell {
    flex: 1;
    position: relative;
    height: 100%;
}

.mask {
    position: absolute;
    inset: 0;
    height: 100%;
}

.mask-white {
    background-color: rgba(255, 255, 255, 0.8);
}

.mask-yellow {
    background-color: rgba(255, 235, 59, 0.8);
}
</style>
