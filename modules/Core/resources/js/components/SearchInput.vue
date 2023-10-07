<template>
  <div class="relative rounded-md shadow-sm">
    <div
      class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
    >
      <Icon icon="SearchSolid" class="h-5 w-5 text-neutral-400" />
    </div>
    <IFormInput
      v-bind="$attrs"
      :id="inputId"
      class="pl-10"
      type="search"
      :disabled="disabled"
      :model-value="modelValue"
      :placeholder="placeholder || $t('core::app.search')"
      @click="$emit('click', $event)"
      @keydown.enter="emitEvent($event)"
      @input="emitEvent($event)"
    />
  </div>
</template>

<script setup>
import debounce from 'lodash/debounce'

const emit = defineEmits(['click', 'input', 'update:modelValue'])

defineProps({
  modelValue: {},
  inputId: String,
  placeholder: String,
  disabled: Boolean,
})

defineOptions({
  inheritAttrs: false,
})

const emitEvent = debounce(function (value) {
  if (value instanceof KeyboardEvent) {
    value = value.target.value
  }

  emit('update:modelValue', value)
  emit('input', value)
}, 650)
</script>
