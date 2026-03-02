<template>
    <v-app id="inspire">
        <v-app-bar app class="headerColor" dark height="30">
            <v-app-bar-title class="text-body-1">
                <slot name="header" />
            </v-app-bar-title>
            <div class="align-right px-1"></div>
        </v-app-bar>

        <v-main style="min-height: calc(100vh - 30px);">
            <div class="bg-white h-100">
                <div class="max-w-7xl mx-auto p-4 h-100 fill-height d-flex flex-column">
                    <slot name="main" />
                </div>
            </div>
        </v-main>

        <v-footer app class="headerColor" elevation="1">
            <v-container class="d-flex align-center justify-space-between py-2">
                <span class="text-body-2 text-medium-emphasis">
                    © {{ new Date().getFullYear() }} Your Company
                </span>
                <div class="d-flex align-center ga-3">
                    <v-btn icon="mdi-twitter" variant="text" color="blue" />
                    <v-btn icon="mdi-github" variant="text" color="black" />
                    <v-btn icon="mdi-email" variant="text" />
                </div>
            </v-container>
        </v-footer>

        <slot name="footer" />
    </v-app>
</template>

<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3';
const drawer = ref(false)
</script>

<script>
export default {
    data: () => ({}),
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
