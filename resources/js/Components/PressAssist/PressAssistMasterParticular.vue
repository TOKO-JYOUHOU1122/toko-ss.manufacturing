
<template>
  <v-expansion-panels v-model="panel">
    <v-expansion-panel
      title="タイトル"
      expand-icon="mdi-chevron-right"
      collapse-icon="mdi-chevron-down"
    >
      <v-expansion-panel-text>
        中身です
      </v-expansion-panel-text>
    </v-expansion-panel>
  </v-expansion-panels>
</template>


<script>
export default {
    name: 'ModalDialog',
    data() {
        return {
            panel: 0,
            message: '',
            resolveFn: null
        }
    },
    methods: {
    }
}
</script>


<style scoped>
</style>
