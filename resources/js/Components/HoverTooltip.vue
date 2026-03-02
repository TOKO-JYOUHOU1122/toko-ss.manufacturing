<!-- HoverTooltip.vue（最小・確実版） -->
<script setup>
const props = defineProps({
  text: { type: String, default: '' },
  location: { type: String, default: 'top' },
  openDelay: { type: [Number, String], default: 150 },
  closeDelay: { type: [Number, String], default: 80 },
  maxWidth: { type: [Number, String], default: 320 },
  openOnClick: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
})
</script>

<template>
  <v-tooltip
    :location="location"
    :open-delay="Number(openDelay)"
    :close-delay="Number(closeDelay)"
    :max-width="Number(maxWidth)"
    :open-on-click="openOnClick"
    :disabled="disabled"
  >
    <template #activator="{ props: actProps }">
      <slot name="activator" :props="actProps" />
    </template>

<!-- 本文側に pre-line を指定 -->
    <div style="white-space: pre-line;">
      {{ text }}
    </div>

  </v-tooltip>
</template>
