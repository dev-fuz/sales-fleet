<template>
  <FormFieldsGroup
    :field="field"
    :label="label"
    :form-id="formId"
    :field-id="fieldId"
  >
    <div class="relative">
      <div
        v-if="
          field.prependText || (field.currency && field.currency.symbol_first)
        "
        class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
      >
        <span
          class="text-sm text-neutral-500 dark:text-neutral-300"
          v-text="!field.currency ? field.prependText : field.currency.iso_code"
        />
      </div>

      <IFormNumericInput
        :id="fieldId"
        v-model="value"
        :disabled="isReadonly"
        :name="field.attribute"
        v-bind="field.attributes"
        :class="{
          'pl-14':
            field.prependText ||
            (field.currency && field.currency.symbol_first),
          'pr-14':
            field.appendText ||
            (field.currency && !field.currency.symbol_first),
        }"
      />

      <div
        v-if="
          field.appendText || (field.currency && !field.currency.symbol_first)
        "
        class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3"
      >
        <span
          class="text-sm text-neutral-500 dark:text-neutral-300"
          v-text="!field.currency ? field.appendText : field.currency.iso_code"
        />
      </div>
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

function handleChange(newValue) {
  value.value = newValue !== null ? newValue : ''
  realInitialValue.value = value.value
}

const { field, label, fieldId, isReadonly, realInitialValue, initialize } =
  useField(
    value,
    toRef(props, 'field'),
    props.formId,
    toRef(props, 'isFloating'),
    {
      handleChange,
    }
  )

onMounted(initialize)
</script>
