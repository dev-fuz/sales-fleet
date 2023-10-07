<template>
  <FormFieldsGroup
    :field="field"
    :label="label"
    :form-id="formId"
    :field-id="fieldId"
  >
    <DatePicker
      :id="fieldId"
      v-model="value"
      :required="field.isRequired"
      :name="field.attribute"
      :disabled="isReadonly"
      v-bind="field.attributes"
      mode="dateTime"
    />
  </FormFieldsGroup>
</template>

<script setup>
import { computed, onMounted, ref, toRef } from 'vue'
import isObject from 'lodash/isObject'

import { isValueEmpty } from '@/utils'

import { useField } from '../../composables/useFormField'
import FormFieldsGroup from '../FormFieldsGroup.vue'

import propsDefinition from './props'

const props = defineProps(propsDefinition)

const value = ref('')

const isDirty = computed(() => {
  const areDatesEqual = (d1, d2) => {
    if (isValueEmpty(d1) && isValueEmpty(d2)) {
      return true
    } else if (
      (isValueEmpty(d1) && !isValueEmpty(d2)) ||
      (isValueEmpty(d2) && !isValueEmpty(d1))
    ) {
      return false
    }

    return moment.utc(d1).isSame(d2)
  }

  // Range
  if (isObject(realInitialValue.value) && isObject(value.value)) {
    const keys = Object.keys(value.value)

    return keys.some(
      key => !areDatesEqual(value.value[key], realInitialValue.value[key])
    )
  }

  return !areDatesEqual(value.value, realInitialValue.value)
})

const { field, label, fieldId, isReadonly, realInitialValue, initialize } =
  useField(
    value,
    toRef(props, 'field'),
    props.formId,
    toRef(props, 'isFloating'),
    {
      isDirty,
    }
  )

onMounted(initialize)
</script>
