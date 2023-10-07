<template>
  <FormFieldsGroup :field="field" :field-id="fieldId" :form-id="formId">
    <IFormLabel
      :for="fieldId"
      class="mb-1 block sm:hidden"
      :label="$t('activities::activity.type.type')"
    />
    <IIconPicker
      v-model="value"
      :icons="typesForIconPicker"
      value-field="id"
      class="flex-nowrap overflow-auto sm:flex-wrap sm:overflow-visible"
      v-bind="field.attributes"
    />
  </FormFieldsGroup>
</template>

<script setup>
import { onMounted, ref, toRef } from 'vue'
import isNil from 'lodash/isNil'

import { useField } from '~/Core/composables/useFormField'
import propsDefinition from '~/Core/fields/Form/props'
import FormFieldsGroup from '~/Core/fields/FormFieldsGroup.vue'

import { useActivityTypes } from '../../composables/useActivityTypes'

const props = defineProps(propsDefinition)

const { typesForIconPicker } = useActivityTypes()

const value = ref('')

function setInitialValue() {
  value.value = !isNil(props.field.value)
    ? typeof props.field.value == 'object'
      ? props.field.value.id
      : props.field.value
    : null
}

function handleChange(newValue) {
  let id = typeof newValue == 'number' ? newValue : newValue?.id

  value.value = id
  realInitialValue.value = id
}

const { field, fieldId, realInitialValue, initialize } = useField(
  value,
  toRef(props, 'field'),
  props.formId,
  toRef(props, 'isFloating'),
  {
    setInitialValue,
    handleChange,
  }
)

onMounted(initialize)
</script>
