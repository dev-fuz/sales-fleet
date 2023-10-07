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
          icon="Mail"
          class="h-5 w-5 text-neutral-500 dark:text-neutral-300"
        />
      </div>

      <IFormInput
        :id="fieldId"
        v-model="value"
        :disabled="isReadonly"
        type="email"
        v-bind="field.attributes"
        class="pl-11"
        @input="searchDuplicateResource"
      />

      <IAlert
        v-if="duplicateResource"
        dismissible
        class="mt-2"
        @dismissed="duplicateResource = null"
      >
        <i18n-t
          scope="global"
          :keypath="field.checkDuplicatesWith.lang_keypath"
        >
          <template #display_name>
            <span class="font-medium">
              {{ duplicateResource.display_name }}
            </span>
          </template>
        </i18n-t>

        <div class="mt-4">
          <div class="-mx-2 -my-1.5 flex">
            <IButtonMinimal
              tag="a"
              :href="duplicateResource.path"
              rel="noopener noreferrer"
              target="_blank"
              variant="info"
              icon="ExternalLink"
              :text="$t('core::app.view_record')"
            />
          </div>
        </div>
      </IAlert>
    </div>
  </FormFieldsGroup>
</template>

<script setup>
import { onMounted, ref, shallowRef, toRef } from 'vue'
import debounce from 'lodash/debounce'

import { useField } from '../../composables/useFormField'
import FormFieldsGroup from '../FormFieldsGroup.vue'

import propsDefinition from './props'

const props = defineProps(propsDefinition)

const value = ref('')

const duplicateResource = shallowRef(null)

const {
  field,
  label,
  fieldId,
  isReadonly,
  initialize,
  checksForDuplicates,
  makeDuplicateCheckRequest,
} = useField(
  value,
  toRef(props, 'field'),
  props.formId,
  toRef(props, 'isFloating')
)

/**
 * Search for duplicate record
 */
const searchDuplicateResource = debounce(() => {
  if (!checksForDuplicates.value || props.resourceId || !value.value) {
    duplicateResource.value = null

    return
  }

  makeDuplicateCheckRequest(value.value).then(
    duplicate => (duplicateResource.value = duplicate)
  )
}, 700)

onMounted(initialize)
</script>
