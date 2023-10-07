<template>
  <FormFieldsGroup :field="field" :form-id="formId" :field-id="fieldId">
    <IFormCheckbox
      :id="field.name"
      v-model:checked="value"
      :disabled="isReadonly"
      :name="field.name"
      :value="field.trueValue"
      v-bind="field.attributes"
      :unchecked-value="field.falseValue"
    >
      <!-- eslint-disable-next-line vue/no-v-html -->
      <span v-html="label"></span>
    </IFormCheckbox>
  </FormFieldsGroup>
</template>

<script setup>
import { onMounted, ref, toRef } from 'vue'

import { useField } from '../../composables/useFormField'
import FormFieldsGroup from '../FormFieldsGroup.vue'

import propsDefinition from './props'

const props = defineProps(propsDefinition)

const value = ref('')

const { field, label, fieldId, isReadonly, initialize } = useField(
  value,
  toRef(props, 'field'),
  props.formId,
  toRef(props, 'isFloating')
)

onMounted(initialize)
</script>
