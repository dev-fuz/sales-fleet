<template>
  <IFormInput
    type="text"
    :placeholder="placeholder"
    :disabled="readOnly"
    size="sm"
    :model-value="query.value"
    @input="updateValue($event)"
  />
</template>

<script setup>
import { computed, toRef } from 'vue'
import { useI18n } from 'vue-i18n'

import propsDefinition from './props'
import { useType } from './useType'

const props = defineProps(propsDefinition)

defineOptions({
  inheritAttrs: false,
})

const { t } = useI18n()

const { updateValue } = useType(
  toRef(props, 'query'),
  toRef(props, 'operator'),
  props.isNullable
)

const placeholder = computed(() =>
  t('core::filters.placeholders.enter', {
    label: props.operand ? props.operand.label : props.rule.label,
  })
)
</script>
