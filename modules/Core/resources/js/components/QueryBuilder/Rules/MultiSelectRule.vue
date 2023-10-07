<template>
  <!--  Todo for multiple check if valueKey and labelKey will work -->
  <ICustomSelect
    v-model="selectValue"
    :multiple="true"
    :disabled="readOnly"
    size="sm"
    :input-id="'rule-' + rule.id + '-' + index"
    :placeholder="placeholder"
    :label="rule.labelKey"
    :options="options"
  />
</template>

<script setup>
import { computed, ref, toRef, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import isEqual from 'lodash/isEqual'
import map from 'lodash/map'

import { isValueEmpty } from '@/utils'

import { useElementOptions } from '~/Core/composables/useElementOptions'

import propsDefinition from './props'
import { useType } from './useType'

const props = defineProps(propsDefinition)

defineOptions({
  inheritAttrs: false,
})

const { t } = useI18n()

const { options, setOptions, getOptions } = useElementOptions()

const selectValue = ref(null)

const placeholder = computed(() => {
  return t('core::filters.placeholders.choose_with_multiple', {
    label: props.operand ? props.operand.label : props.rule.label,
  })
})

const { updateValue } = useType(
  toRef(props, 'query'),
  toRef(props, 'operator'),
  props.isNullable
)

/**
 * Watch the value for change and update actual query value
 */
watch(
  selectValue,
  newVal => {
    handleChange(newVal)
  },
  { deep: true }
)

/**
 * Handle change for select to update the value
 *
 * @param  {String|Array|Number|null} value
 *
 * @return {Void}
 */
function handleChange(option) {
  if (isValueEmpty(option)) {
    updateSelectValue([])
  } else {
    updateSelectValue(map(option, props.rule.valueKey))
  }
}

/**
 * Prepare component
 *
 * @param  {Array} list
 */
function prepareComponent(list) {
  setOptions(list)

  if (!isValueEmpty(props.query.value)) {
    setInitialValue()
  }
}

/**
 * Set the select initial internal value
 */
function setInitialValue() {
  if (isValueEmpty(props.query.value)) {
    updateSelectValue([])
  } else {
    updateSelectValue(props.query.value)
  }
}

/**
 * Set the select value from the given query builder value
 *
 * @param  {String|Array|Number|null} value
 *
 * @return {Void}
 */
function setSelectValue(value) {
  if (isValueEmpty(value)) {
    selectValue.value = []

    return
  }

  value =
    options.value.filter(
      option => value.indexOf(option[props.rule.valueKey]) > -1
    ) || []

  if (!isEqual(value, selectValue.value)) {
    selectValue.value = value
  }
}

/**
 * Update the current rule query value
 *
 * @param  {String|Array|Number|null} value
 */
function updateSelectValue(value) {
  updateValue(value)

  if (!isEqual(value, selectValue.value)) {
    setSelectValue(value)
  }
}

getOptions(props.rule).then(prepareComponent)
</script>
