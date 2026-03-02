<template>
    <v-app id="inspire">
        <v-app-bar app class="headerColor" dark height="50">
            <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>
            <v-app-bar-title class="text-h5">
                <slot name="header" />
            </v-app-bar-title>
            <div class="align-right px-1"></div>
        </v-app-bar>

        <v-navigation-drawer v-model="drawer" class="headerColor" temporary>
            <v-list v-for="menu in menus" :key="menu.title">
                <Link :href="route(menu.url)">
                    <v-list-item :prepend-icon="menu.icon" link>
                        <v-list-item-title dark>
                            {{ menu.title }}
                        </v-list-item-title>
                    </v-list-item>
                </Link>
            </v-list>
        </v-navigation-drawer>

        <v-main style="min-height: calc(100vh - 100px);">
            <div class="bg-white h-100">
                <div class="max-w-7xl mx-auto p-4 h-100 fill-height d-flex flex-column">
                    <slot name="main" />
                </div>
            </div>
        </v-main>

        <v-footer app class="headerColor" elevation="0" height="50">
            <slot name="footer" />
        </v-footer>

    </v-app>
</template>

<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3';
const drawer = ref(false)
</script>

<script>
export default {
    data: () => ({
        drawer: false,
        menus: [
            { title: '品目マスタ', icon: 'mdi-package-variant', url: 'pressassist.mst.item' },
            { title: '加工手順マスタ', icon: 'mdi-format-list-numbered', url: 'pressassist.mst.procedure' },
            { title: '位置番号マスタ', icon: 'mdi-clipboard-check-multiple-outline', url: 'pressassist.mst.position' },
            //{ title: 'シリンダ・治具マスタ', icon: 'mdi-history', url: 'pressassist.mst.cylinder' },
        ],
    }),
}
</script>

<style>
a {
    text-decoration: none;
    color: inherit;
}

.headerColor {
    background-color: #28385e !important;
    color: #ffffff !important;
    font-family: メイリオ, meiryo, sans-serif !important;
}
</style>
