<template>
  <div class="mt-1 space-y-1">
    <IFormCheckbox
      v-for="option in options"
      :id="rule.id + '_' + option[rule.valueKey] + '_' + index"
      :key="option[rule.valueKey]"
      v-model:checked="value"
      :value="option[rule.valueKey]"
      :disabled="readOnly"
      :name="rule.id + '_' + option[rule.valueKey]"
      :label="option[rule.labelKey]"
    />
  </div>
</template>

<script setup>
import { computed, toRef } from 'vue'

import { useElementOptions } from '~/Core/composables/useElementOptions'

import propsDefinition from './props'
import { useType } from './useType'

const props = defineProps(propsDefinition)

defineOptions({
  inheritAttrs: false,
})

const { options, setOptions, getOptions } = useElementOptions()

const value = computed({
  get() {
    return props.query.value
  },
  set(value) {
    updateValue(value)
  },
})

const { updateValue } = useType(
  toRef(props, 'query'),
  toRef(props, 'operator'),
  props.isNullable
)

if (props.query.value === null) {
  updateValue([])
}

getOptions(props.rule).then(setOptions)
</script>
