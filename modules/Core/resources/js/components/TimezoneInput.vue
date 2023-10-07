<template>
  <ICustomSelect v-model="timezone" :input-id="fieldId" :options="timezones">
    <template #option="option">
      {{ label(option) }}
    </template>
  </ICustomSelect>
</template>

<script setup>
import { computed } from 'vue'
import { useStore } from 'vuex'
import { useVModel } from '@vueuse/core'

const emit = defineEmits(['update:modelValue'])

const props = defineProps({
  modelValue: null,
  fieldId: { type: String, default: 'timezone' },
})

const store = useStore()

const timezone = useVModel(props, 'modelValue', emit)

function label(option) {
  return 'UTC/GMT ' + moment.tz(option.label).format('Z') + ' ' + option.label
}

const timezones = computed(() => store.state.timezones)

// Check if the timezones are set in the store
// If not, make a request to fetch the timezones and set them in for future usage
if (timezones.value.length === 0) {
  store.dispatch('fetchTimezones')
}
</script>
