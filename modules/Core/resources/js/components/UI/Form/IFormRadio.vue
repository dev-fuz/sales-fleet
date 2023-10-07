<template>
  <div class="flex items-start">
    <input
      :id="id"
      v-model="localModelValue"
      :name="name"
      type="radio"
      :value="value"
      :disabled="disabled"
      class="form-radio dark:bg-neutral-700"
      @change="handleChangeEvent"
    />
    <IFormLabel :for="id" class="-mt-0.5 ml-2">
      <component
        :is="swatchColor ? TextBackground : 'span'"
        :color="swatchColor || undefined"
        :class="
          swatchColor
            ? 'inline-flex items-center justify-center rounded-full px-2.5 font-normal leading-5 dark:!text-white'
            : null
        "
      >
        <slot>{{ label }}</slot>
      </component>
    </IFormLabel>
    <IFormText v-if="description" :id="id + 'description'" class="mt-1">
      {{ description }}
    </IFormText>
  </div>
</template>

<script setup>
import { onMounted, shallowRef, watch } from 'vue'

import { randomString } from '@/utils'

import TextBackground from '~/Core/components/TextBackground.vue'

const emit = defineEmits(['update:modelValue', 'change'])

const props = defineProps({
  name: String,
  label: String,
  description: String,
  swatchColor: String,
  modelValue: {},
  value: {},
  disabled: Boolean,
  id: {
    type: [String, Number],
    default() {
      return randomString()
    },
  },
})

const localModelValue = shallowRef(null)

watch(
  () => props.modelValue,
  newVal => {
    localModelValue.value = newVal
  }
)

function handleChangeEvent(e) {
  let value = e.target.value

  // Allow providing null as a value
  if (value === 'on' && props.value === null) {
    value = null
  }

  if (value === 'false') {
    value = false
  } else if (value === 'true') {
    value = true
  }

  emit('update:modelValue', value)
  emit('change', value)
}

onMounted(() => (localModelValue.value = props.modelValue))
</script>
