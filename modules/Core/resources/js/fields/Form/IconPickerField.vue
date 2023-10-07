<template>
  <FormFieldsGroup
    :field="field"
    :label="label"
    :field-id="fieldId"
    :form-id="formId"
  >
    <IIconPicker v-model="value" v-bind="field.attributes" />
  </FormFieldsGroup>
</template>

<script setup>
import { onMounted, ref, toRef } from 'vue'

import { useField } from '../../composables/useFormField'
import FormFieldsGroup from '../FormFieldsGroup.vue'

import propsDefinition from './props'

const props = defineProps(propsDefinition)

const value = ref('')

const { field, label, fieldId, initialize } = useField(
  value,
  toRef(props, 'field'),
  props.formId,
  toRef(props, 'isFloating')
)

onMounted(initialize)
</script>
