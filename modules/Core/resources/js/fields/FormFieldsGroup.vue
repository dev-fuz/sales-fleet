<template>
  <IFormGroup :description="description">
    <a
      v-if="isToggleable"
      href="#"
      class="link flex items-center text-sm"
      @click.prevent="toggleIsVisible = true"
    >
      <Icon icon="Plus" class="mr-1 h-4 w-4" />
      <span v-text="label" />
    </a>

    <div v-show="!isToggleable">
      <IFormLabel
        v-if="label && !field.hideLabel"
        :for="fieldId"
        class="mb-1 inline-flex items-center"
        :required="field.isRequired"
      >
        <Icon
          v-if="displayHelpAsIcon"
          v-i-tooltip="field.helpText"
          icon="QuestionMarkCircle"
          class="mr-1 h-4 w-4 text-neutral-500 hover:text-neutral-700 dark:text-white dark:hover:text-neutral-300"
        />
        <!-- eslint-disable-next-line vue/no-v-html -->
        <span v-html="label"></span>
      </IFormLabel>

      <slot></slot>
    </div>

    <IAlert v-if="displayHelpAsAlert" variant="info" class="mt-3">
      {{ field.helpText }}
    </IAlert>

    <IFormError
      v-if="form.getError(field.attribute)"
      v-text="form.getError(field.attribute)"
    />
  </IFormGroup>
</template>

<script setup>
import { computed, ref } from 'vue'

import { isValueEmpty } from '@/utils'

import { useForm } from '../composables/useFieldsForm'

const props = defineProps({
  formId: { required: true, type: String },
  field: { required: true, type: Object },
  fieldId: String,
  label: String,
})

const form = useForm(props.formId)
const toggleIsVisible = ref(false)

const isToggleable = computed(
  () => props.field.toggleable && !toggleIsVisible.value && !hasValue.value
)

const displayHelpAsIcon = computed(
  () => props.field.helpText && props.field.helpTextDisplay === 'icon'
)

const displayHelpAsText = computed(
  () => props.field.helpText && props.field.helpTextDisplay === 'text'
)

const displayHelpAsAlert = computed(
  () => props.field.helpText && props.field.helpTextDisplay === 'alert'
)

const hasValue = computed(() => !isValueEmpty(props.field.value))

const description = computed(() =>
  !isToggleable.value && displayHelpAsText.value ? props.field.helpText : null
)
</script>
