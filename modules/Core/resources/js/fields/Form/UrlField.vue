<template>
  <FormFieldsGroup
    :field="field"
    :label="label"
    :field-id="fieldId"
    :form-id="formId"
  >
    <div class="relative">
      <div
        v-if="field.https"
        class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
      >
        <span
          class="text-sm text-neutral-500 dark:text-neutral-300"
          v-text="'https://'"
        />
      </div>

      <IFormInput
        :id="fieldId"
        v-model="value"
        :disabled="isReadonly"
        type="url"
        v-bind="field.attributes"
        :class="['pr-10', { 'pl-16': field.https }]"
      />

      <div class="absolute inset-y-0 right-0 flex items-center pr-3">
        <a
          :href="value || '#'"
          :class="[
            !value ? 'text-neutral-400 dark:text-neutral-300' : 'link',
            { 'pointer-events-none': !value },
          ]"
        >
          <Icon icon="Link" class="h-5 w-5" />
        </a>
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

const { field, label, fieldId, isReadonly, initialize } = useField(
  value,
  toRef(props, 'field'),
  props.formId,
  toRef(props, 'isFloating')
)

onMounted(initialize)
</script>
