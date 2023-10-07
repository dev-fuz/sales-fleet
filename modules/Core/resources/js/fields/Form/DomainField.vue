<template>
  <FormFieldsGroup
    :field="field"
    :label="label"
    :field-id="fieldId"
    :form-id="formId"
  >
    <div class="relative">
      <div
        class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
      >
        <Icon
          icon="Globe"
          class="h-5 w-5 text-neutral-500 dark:text-neutral-300"
        />
      </div>

      <IFormInput
        :id="fieldId"
        v-model="value"
        :disabled="isReadonly"
        v-bind="field.attributes"
        class="pl-11"
        @blur="parseDomain"
      />
    </div>
  </FormFieldsGroup>
</template>

<script setup>
import { onMounted, ref, toRef } from 'vue'
import { parse } from 'tldts'

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

function parseDomain() {
  // If not valid, domain will be null.
  value.value = parse(value.value).domain
}

onMounted(initialize)
</script>
