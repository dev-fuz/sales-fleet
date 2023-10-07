<template>
  <FormFieldsGroup
    :field="field"
    :label="label"
    :field-id="fieldId"
    :form-id="formId"
  >
    <VisibilityGroupSelector
      v-model:type="value.type"
      v-model:dependsOn="value.depends_on"
      v-bind="field.attributes"
      :disabled="isReadonly"
    />
  </FormFieldsGroup>
</template>

<script setup>
import { onMounted, ref, toRef } from 'vue'

import VisibilityGroupSelector from '~/Core/components/VisibilityGroupSelector.vue'

import { useField } from '../../composables/useFormField'
import FormFieldsGroup from '../FormFieldsGroup.vue'

import propsDefinition from './props'

const props = defineProps(propsDefinition)

const value = ref({})

const { field, label, fieldId, isReadonly, initialize } = useField(
  value,
  toRef(props, 'field'),
  props.formId,
  toRef(props, 'isFloating'),
  {
    setInitialValue,
  }
)

function setInitialValue() {
  if (!props.field.value || !props.field.value.type) {
    value.value = {
      type: 'all',
      depends_on: [],
    }
  } else {
    value.value = props.field.value
  }
}

onMounted(initialize)
</script>
