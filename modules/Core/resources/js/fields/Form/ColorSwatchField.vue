<template>
  <FormFieldsGroup
    :field="field"
    :label="label"
    :form-id="formId"
    :field-id="fieldId"
  >
    <div>
      <IColorSwatch
        v-model="value"
        :swatches="swatches"
        :allow-remove="!field.isRequired"
      />
    </div>
  </FormFieldsGroup>
</template>

<script setup>
import { onMounted, ref, toRef } from 'vue'

import { useField } from '../../composables/useFormField'
import FormFieldsGroup from '../FormFieldsGroup.vue'

import propsDefinition from './props'

const props = defineProps(propsDefinition)

const value = ref('')
const swatches = Innoclapps.config('favourite_colors')

const { field, label, fieldId, initialize } = useField(
  value,
  toRef(props, 'field'),
  props.formId,
  toRef(props, 'isFloating')
)

onMounted(initialize)
</script>
