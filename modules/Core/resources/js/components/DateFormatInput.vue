<template>
  <ICustomSelect
    :input-id="inputId"
    :clearable="false"
    :model-value="modelValue"
    :options="formats"
    @option:selected="handleChange($event)"
  >
    <template #option="option">
      {{ option.label + ' [' + formatLabel(option) + ']' }}
    </template>
  </ICustomSelect>
</template>

<script setup>
import { onMounted } from 'vue'

const emit = defineEmits(['update:modelValue'])

const props = defineProps({
  modelValue: String,
  inputId: { type: String, default: 'date_format' },
})

const formats = Innoclapps.config('date_formats')

function handleChange(value) {
  emit('update:modelValue', value)
}

function formatLabel(option) {
  return moment().formatPHP(option.label)
}

onMounted(() => {
  // Emit the initial value in case it's only taken from the configuration to fill the form
  handleChange(props.modelValue)
})
</script>
