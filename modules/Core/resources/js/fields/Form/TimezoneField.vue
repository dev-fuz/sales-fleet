<template>
  <FormFieldsGroup
    :field="field"
    :label="label"
    :field-id="fieldId"
    :form-id="formId"
  >
    <TimezoneInput
      v-model="value"
      v-bind="field.attributes"
      :field-id="fieldId"
      :clearable="true"
      :name="field.attribute"
      :disabled="isReadonly"
    />
  </FormFieldsGroup>
</template>

<script setup>
import { onMounted, ref, toRef } from 'vue'
import { useStore } from 'vuex'

import TimezoneInput from '~/Core/components/TimezoneInput.vue'

import { useField } from '../../composables/useFormField'
import FormFieldsGroup from '../FormFieldsGroup.vue'

import propsDefinition from './props'

const props = defineProps(propsDefinition)

const store = useStore()

const value = ref('')

const { field, label, fieldId, isReadonly, initialize } = useField(
  value,
  toRef(props, 'field'),
  props.formId,
  toRef(props, 'isFloating')
)

onMounted(initialize)

// Set the timezones from the field options in store, so the TimezoneInput won't make request
// to actually retrieve the timezones from storage, e.q. useful also on web forms
store.commit('SET_TIMEZONES', props.field.timezones)
</script>
