<template>
  <div class="flex items-start space-x-2">
    <div class="flex h-5 items-center">
      <input
        :id="id"
        :name="name"
        type="checkbox"
        :disabled="disabled"
        :checked="isChecked"
        :value="value"
        class="form-check dark:bg-neutral-700"
        @change="handleChangeEvent"
      />
    </div>
    <div>
      <IFormLabel :for="id">
        <component
          :is="swatchColor ? TextBackground : 'span'"
          :color="swatchColor || undefined"
          :class="
            swatchColor
              ? 'inline-flex items-center justify-center rounded-full px-2.5 font-normal leading-5 dark:!text-white'
              : null
          "
        >
          <slot>
            {{ label }}
          </slot>
        </component>
      </IFormLabel>
      <IFormText
        v-if="description"
        :id="id + 'description'"
        class="mt-1"
        v-text="description"
      />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import clone from 'lodash/clone'
import findIndex from 'lodash/findIndex'
import isArray from 'lodash/isArray'

import { randomString } from '@/utils'

import TextBackground from '~/Core/components/TextBackground.vue'

const emit = defineEmits(['update:checked', 'change'])

const props = defineProps({
  name: String,
  label: String,
  description: String,
  swatchColor: String,
  checked: [Array, String, Boolean, Number],
  value: { default: true },
  uncheckedValue: { default: false },
  disabled: Boolean,
  id: {
    type: String,
    default() {
      return randomString()
    },
  },
})

const isChecked = computed(() => {
  if (isArray(props.checked)) {
    return (
      Boolean(
        props.checked.filter(value => String(value) === String(props.value))[0]
      ) || false
    )
  }

  return props.checked == props.value
})

function handleChangeEvent(e) {
  const checked = props.checked
  const isChecked = e.target.checked

  if (isArray(checked)) {
    let cloneChecked = clone(checked)

    if (isChecked) {
      cloneChecked.push(props.value)
    } else {
      cloneChecked.splice(
        findIndex(cloneChecked, value => String(value) === String(props.value)),
        1
      )
    }

    emit('update:checked', cloneChecked)
    emit('change', cloneChecked)

    return
  }

  let value = isChecked === true ? props.value : props.uncheckedValue
  emit('update:checked', value)
  emit('change', value)
}
</script>
