<template>
  <FormFieldsGroup
    :field="field"
    :label="label"
    :form-id="formId"
    :field-id="fieldId"
  >
    <MainLostReasonField v-model="value" :attribute="field.attribute" />
  </FormFieldsGroup>
</template>

<script setup>
import { onMounted, ref, toRef } from 'vue'

import { useField } from '~/Core/composables/useFormField'
import propsDefinition from '~/Core/fields/Form/props'
import FormFieldsGroup from '~/Core/fields/FormFieldsGroup.vue'

import MainLostReasonField from '../../components/DealLostReasonField.vue'

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
