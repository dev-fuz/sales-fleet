<template>
  <FormFieldsGroup
    :field="field"
    :label="label"
    :field-id="fieldId"
    :form-id="formId"
  >
    <IFormInput
      :id="fieldId"
      v-model="value"
      :disabled="isReadonly"
      :type="field.inputType || 'text'"
      v-bind="field.attributes"
      @input="searchDuplicateResource"
    />
    <IAlert
      v-if="duplicateResource"
      dismissible
      class="mt-2"
      @dismissed="duplicateResource = null"
    >
      <i18n-t scope="global" :keypath="field.checkDuplicatesWith.lang_keypath">
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
