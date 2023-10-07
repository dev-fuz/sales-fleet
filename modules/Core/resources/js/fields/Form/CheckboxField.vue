<template>
  <FormFieldsGroup
    :field="field"
    :label="label"
    :form-id="formId"
    :field-id="fieldId"
  >
    <div
      :class="{
        'flex items-center space-x-2': field.inline,
        'space-y-1': !field.inline,
      }"
    >
      <IFormCheckbox
        v-for="option in options"
        :key="option[field.valueKey]"
        v-model:checked="value"
        :value="option[field.valueKey]"
        :name="field.attribute"
        v-bind="field.attributes"
        :disabled="isReadonly"
        :swatch-color="option.swatch_color"
        :label="option[field.labelKey]"
      />
    </div>
  </FormFieldsGroup>
</template>

<script setup>
import { onMounted, ref, toRef } from 'vue'
import isNil from 'lodash/isNil'

import { useElementOptions } from '../../composables/useElementOptions'
import { useField } from '../../composables/useFormField'
import FormFieldsGroup from '../FormFieldsGroup.vue'

import propsDefinition from './props'

const props = defineProps(propsDefinition)

const { options, setOptions, getOptions } = useElementOptions()

const value = ref([])

function setInitialValue() {
  value.value = prepareValue(props.field.value)
}

function handleChange(newValue) {
  value.value = prepareValue(newValue)
  realInitialValue.value = value.value
}

function prepareValue(value) {
  return (!isNil(value) ? value : []).map(value => value[props.field.valueKey])
}

const { field, label, fieldId, isReadonly, realInitialValue, initialize } =
  useField(
    value,
    toRef(props, 'field'),
    props.formId,
    toRef(props, 'isFloating'),
    {
      setInitialValue,
      handleChange,
    }
  )

getOptions(props.field).then(setOptions)

onMounted(initialize)
</script>
