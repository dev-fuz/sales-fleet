<template>
  <input
    :id="id"
    ref="inputRef"
    v-model="value"
    :name="name"
    :disabled="disabled"
    :autocomplete="autocomplete"
    :autofocus="autofocus"
    :type="type"
    :tabindex="tabindex"
    :required="required"
    :placeholder="placeholder"
    :pattern="pattern"
    :minlength="minlength"
    :maxlength="maxlength"
    :min="min"
    :max="max"
    :class="[
      'form-input dark:bg-neutral-700 dark:text-white dark:placeholder-neutral-400',
      {
        'form-input-sm': size === 'sm',
        'form-input-lg': size === 'lg',
        rounded: rounded && size === 'sm',
        'rounded-md': rounded && size !== 'sm' && size !== false,
        'border-neutral-300 dark:border-neutral-500': bordered,
        'border-transparent': !bordered,
      },
    ]"
    @blur="blurHandler"
    @focus="focusHandler"
    @keyup="keyupHandler"
    @keydown="keydownHandler"
    @input="inputHandler"
  />
</template>

<script setup>
import { computed, ref } from 'vue'

import textInputProps from './textInputProps'
import { useTextInput } from './useTextInput'

const emit = defineEmits([
  'update:modelValue',
  'focus',
  'blur',
  'input',
  'keyup',
  'keydown',
  'change',
])

const props = defineProps({
  rounded: { default: true, type: Boolean },
  bordered: { default: true, type: Boolean },
  type: { type: String, default: 'text' },
  max: { type: [String, Number], default: undefined },
  min: { type: [String, Number], default: undefined },
  size: {
    type: [String, Boolean],
    default: '',
    validator(value) {
      return ['sm', 'lg', 'md', '', false].includes(value)
    },
  },
  ...textInputProps,
})

const inputRef = ref(null)

const value = computed({
  get: () => props.modelValue,
  set: value => emit('update:modelValue', value),
})

const {
  setRangeText,
  select,
  focus,
  click,
  blur,
  keydownHandler,
  keyupHandler,
  blurHandler,
  focusHandler,
} = useTextInput(inputRef, emit, value)

function inputHandler(e) {
  emit('input', e.target.value)
}

defineExpose({
  setRangeText,
  select,
  focus,
  click,
  blur,
  inputRef,
})
</script>
