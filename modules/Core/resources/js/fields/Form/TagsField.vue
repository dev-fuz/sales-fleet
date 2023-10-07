<template>
  <FormFieldsGroup
    :field="field"
    :label="label"
    :field-id="fieldId"
    :form-id="formId"
  >
    <TagsSelectInput
      v-model="value"
      :disabled="isReadonly"
      :input-id="fieldId"
      :name="field.attribute"
      :type="field.type"
      v-bind="field.attributes"
    />
  </FormFieldsGroup>
</template>

<script setup>
import { onMounted, ref, toRef } from 'vue'

import TagsSelectInput from '../../components/TagsSelectInput.vue'
import { useField } from '../../composables/useFormField'
import FormFieldsGroup from '../FormFieldsGroup.vue'

import propsDefinition from './props'

const props = defineProps(propsDefinition)

const value = ref([])

function setInitialValue() {
  value.value =
    props.field.value?.map(tag => (typeof tag === 'string' ? tag : tag.name)) ||
    []
}

function handleChange(value) {
  value.value =
    value?.map(tag => (typeof tag === 'string' ? tag : tag.name)) || []

  realInitialValue.value = value.value
}

const { field, label, fieldId, isReadonly, initialize, realInitialValue } =
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

onMounted(initialize)
</script>
