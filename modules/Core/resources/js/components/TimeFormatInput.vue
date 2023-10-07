<template>
  <IFormSelect
    :id="inputId"
    :model-value="modelValue"
    @change="handleChange($event)"
  >
    <option v-for="format in formats" :key="format" :value="format">
      {{ format }} [<span v-once v-text="formatForDisplay(format)"></span>]
    </option>
  </IFormSelect>
</template>

<script setup>
import { onMounted } from 'vue'

const emit = defineEmits(['update:modelValue'])

const props = defineProps({
  modelValue: String,
  inputId: { type: String, default: 'time_format' },
})
const formats = Innoclapps.config('time_formats')

function handleChange(value) {
  emit('update:modelValue', value)
}

function formatForDisplay(value) {
  return moment().formatPHP(value)
}

onMounted(() => handleChange(props.modelValue))
</script>
