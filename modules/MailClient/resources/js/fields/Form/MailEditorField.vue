<template>
  <FormFieldsGroup
    :field="field"
    :label="label"
    :field-id="fieldId"
    :form-id="formId"
  >
    <MailEditor
      v-model="value"
      :disabled="isReadonly"
      v-bind="field.attributes"
    />
  </FormFieldsGroup>
</template>

<script setup>
import { onMounted, ref, toRef } from 'vue'

import { useField } from '~/Core/composables/useFormField'
import propsDefinition from '~/Core/fields/Form/props'
import FormFieldsGroup from '~/Core/fields/FormFieldsGroup.vue'

import MailEditor from '../../components/MailEditor'

const props = defineProps(propsDefinition)

const value = ref('')

const { field, fieldId, label, isReadonly, initialize } = useField(
  value,
  toRef(props, 'field'),
  props.formId,
  toRef(props, 'isFloating')
)

onMounted(initialize)
</script>
