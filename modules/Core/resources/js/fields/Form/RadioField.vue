<template>
  <FormFieldsGroup
    :field="field"
    :label="label"
    :field-id="fieldId"
    :form-id="formId"
  >
    <div
      :class="{
        'flex items-center space-x-2': field.inline,
        'space-y-1': !field.inline,
      }"
    >
      <IFormRadio
        v-for="option in options"
        :id="'radio-' + option[field.valueKey]"
        :key="option[field.valueKey]"
        v-bind="field.attributes"
        v-model="value"
        :name="field.attribute"
        :value="option[field.valueKey]"
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

const value = ref('')

const { field, label, fieldId, isReadonly, initialize } = useField(
  value,
  toRef(props, 'field'),
  props.formId,
  toRef(props, 'isFloating'),
  {
    setInitialValue: function () {
      if (!isNil(props.field.value)) {
        if (typeof props.field.value === 'object') {
          value.value = props.field.value[props.field.valueKey]
        } else {
          value.value = props.field.value
        }
      }
    },
  }
)

getOptions(props.field).then(setOptions)

onMounted(initialize)
</script>
