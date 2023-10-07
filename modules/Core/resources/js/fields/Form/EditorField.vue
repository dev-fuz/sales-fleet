<template>
  <FormFieldsGroup
    :field="field"
    :label="label"
    :field-id="fieldId"
    :form-id="formId"
  >
    <Editor v-model="value" :disabled="isReadonly" v-bind="field.attributes" />
  </FormFieldsGroup>
</template>

<script setup>
import { onMounted, ref, toRef } from 'vue'

import { randomString } from '@/utils'

import { useField } from '../../composables/useFormField'
import FormFieldsGroup from '../FormFieldsGroup.vue'

import propsDefinition from './props'

const props = defineProps(propsDefinition)

const value = ref('')

const { field, label, isReadonly, initialize } = useField(
  value,
  toRef(props, 'field'),
  props.formId,
  toRef(props, 'isFloating')
)

/**
 * Note: We do use pass the field id as editor id
 * some fields may have same name e.q. on resource profile and the editor
 * won't be initialized, in this case, custom editor id will be generated automatically
 */
const fieldId = randomString()

onMounted(initialize)
</script>
