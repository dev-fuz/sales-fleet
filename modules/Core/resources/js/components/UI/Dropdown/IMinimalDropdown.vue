<template>
  <IDropdown no-caret :full="false" v-bind="$attrs">
    <template #toggle="{ toggle }">
      <IButtonIcon :icon="icon" @click="toggle" />
    </template>
    <div class="py-1">
      <slot></slot>
    </div>
  </IDropdown>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  type: { default: 'vertical', type: String },
})

defineOptions({
  inheritAttrs: false,
})

const icon = computed(() =>
  props.type === 'horizontal' ? 'DotsHorizontal' : 'DotsVertical'
)
</script>
