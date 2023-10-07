<template>
  <IPopover
    v-if="canBeInlineEdited"
    ref="popoverRef"
    :class="widthClass"
    :portal="false"
    adaptive-width
    :title="$t('core::fields.update_field')"
    shift
    :disabled="Boolean(editAction)"
    @show="handlePopoverShowEvent"
    @hide="handlePopoverHideEvent"
  >
    <button
      v-bind="$attrs"
      type="button"
      :class="{ '!block': popoverVisible }"
      class="absolute right-2 z-20 my-auto -mt-0.5 mr-4 rounded-md border border-neutral-200 bg-white p-1 text-neutral-600 hover:text-neutral-800 focus:outline-none dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-200"
      @click="editAction"
    >
      <Icon icon="Pencil" class="h-4 w-4" />
    </button>
    <template #popper>
      <form @submit.prevent="save">
        <div class="p-4">
          <IOverlay :show="fields.isEmpty()">
            <FormFields
              v-if="inlineEditReady"
              :fields="fields"
              :form-id="form.formId"
              :resource-name="resourceName"
              :resource-id="resourceId"
              :is-floating="isFloating"
              :collapsed="false"
            />
            <slot
              name="after-inline-edit-form-fields"
              :hide-popover="hidePopover"
            />
          </IOverlay>
        </div>
        <div
          class="border-t border-neutral-200 bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-800"
        >
          <div class="flex items-center justify-end space-x-1 px-4 py-2.5">
            <IButton
              size="sm"
              variant="white"
              :text="$t('core::app.cancel')"
              :disabled="!inlineEditReady"
              @click="cancel"
            />
            <IButton
              size="sm"
              :text="$t('core::app.save')"
              :disabled="form.busy || !inlineEditReady"
              :loading="form.busy"
              @click="save"
            />
          </div>
        </div>
      </form>
    </template>
  </IPopover>
</template>

<script setup>
import { computed, ref } from 'vue'
import castArray from 'lodash/castArray'
import cloneDeep from 'lodash/cloneDeep'
import get from 'lodash/get'
import isFunction from 'lodash/isFunction'

import { CancelToken } from '~/Core/services/HTTP'

import { useFieldsForm } from '../composables/useFieldsForm'
import { useResourceable } from '../composables/useResourceable'

import Fields from './Fields'

const emit = defineEmits(['updated'])

const props = defineProps([
  'field',
  'fieldFetcher',
  'resource',
  'resourceName',
  'resourceId',
  'editAction',
  'isFloating',
  'width',
])

defineOptions({
  inheritAttrs: false,
})

const popoverRef = ref(null)
const inlineEditReady = ref(false)
const popoverVisible = ref(false)
let saveCancelToken = null

const inlineEditField = ref({})
const fields = ref(new Fields())

const { form } = useFieldsForm(fields)
const { updateResource } = useResourceable(props.resourceName)

const widthClass = computed(() => {
  if (props.field.inlineEditPanelWidth === 'medium') {
    return 'w-80'
  } else if (props.field.inlineEditPanelWidth === 'large') {
    return 'w-96'
  }

  return ''
})

function hidePopover() {
  popoverRef.value.hide()
}

function cancel() {
  if (saveCancelToken) {
    saveCancelToken()
    saveCancelToken = null
  }

  hidePopover()
}

async function getFieldsForInlineEdit() {
  let field = await new Promise(resolve => {
    return isFunction(props.fieldFetcher)
      ? resolve(props.fieldFetcher())
      : resolve(props.field)
  })

  inlineEditField.value = field

  if (field.inlineEditWith !== null) {
    field = field.inlineEditWith
  }

  return field
}

async function handlePopoverShowEvent() {
  let availableFields = cloneDeep(castArray(await getFieldsForInlineEdit()))
  availableFields.forEach(f => (f.width = 'full'))

  fields.value.set(availableFields).populate(props.resource)

  popoverVisible.value = true
  inlineEditReady.value = true
}

function handlePopoverHideEvent() {
  form.errors.clear()
  inlineEditReady.value = false
  fields.value.set([])

  setTimeout(() => {
    popoverVisible.value = false
  }, 300)
}

async function save() {
  const updatedResource = await updateResource(form, props.resourceId, {
    cancelToken: new CancelToken(token => (saveCancelToken = token)),
  })

  hidePopover()
  emit('updated', updatedResource)
}

const canBeInlineEdited = computed(() => {
  if (
    !props.resource.authorizations.update ||
    props.field.inlineEditDisabled === true ||
    get(props.resource, `_edit_disabled.${props.field.attribute}`)
  ) {
    return false
  }

  if (props.field.inlineEditWith !== null) {
    return true
  }

  return props.field.applicableForUpdate
})
</script>
