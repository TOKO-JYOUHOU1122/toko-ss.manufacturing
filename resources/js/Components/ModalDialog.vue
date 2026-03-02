<template>
  <v-dialog v-model="dialog" persistent max-width="400">
    <v-card>
      <v-card-text class="text-pre-wrap">{{ message }}</v-card-text>
      <v-card-actions>
        <v-spacer />
        <v-btn @click="confirm(true)" color="primary" variant="outlined">OK</v-btn>
        <v-btn @click="confirm(false)" color="grey" variant="outlined">キャンセル</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  name: 'ModalDialog',
  data() {
    return {
      dialog: false,
      message: '',
      resolveFn: null
    }
  },
  methods: {
    open(msg) {
      this.message = msg
      this.dialog = true
      return new Promise((resolve) => {
        this.resolveFn = resolve
      })
    },
    confirm(val) {
      this.dialog = false
      if (this.resolveFn) {
        this.resolveFn(val)
        this.resolveFn = null
      }
    }
  }
}
</script>