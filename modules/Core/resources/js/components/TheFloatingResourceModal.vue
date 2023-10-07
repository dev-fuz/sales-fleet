<template>
  <ISlideover
    id="floatingResourceModal"
    v-model:visible="isModalVisible"
    :title="resource.display_name"
    :sub-title="modalSubTitle"
    :initial-focus="modalCloseElement"
    static-backdrop
    form
    @hidden="handleModalHidden"
    @submit="performUpdate"
  >
    <FieldsPlaceholder v-if="!componentReady" />

    <component
      :is="`${resourceSingularName}FloatingModal`"
      v-if="componentReady"
      :resource="resource"
      :form="form"
      :fields="fields"
      :mode="mode"
    />

    <template #modal-footer>
      <div class="flex justify-end space-x-2">
        <IButton
          variant="white"
          :disabled="!componentReady"
          :text="$t('core::app.view_record')"
          @click="view"
        />

        <IButton
          v-if="mode === 'edit'"
          type="submit"
          variant="primary"
          :loading="form.busy"
          :text="$t('core::app.save')"
          :disabled="
            !componentReady ||
            form.busy ||
            (resource.authorizations && !resource.authorizations.update)
          "
        />
      </div>
    </template>
  </ISlideover>
</template>

<script setup>
import { computed, nextTick, provide, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute, useRouter } from 'vue-router'
import { computedWithControl } from '@vueuse/shared'
import omit from 'lodash/omit'

import { useFieldsForm } from '../composables/useFieldsForm'
import { useResource } from '../composables/useResource'
import { useResourceFields } from '../composables/useResourceFields'

const { t } = useI18n()
const router = useRouter()
const route = useRoute()

const resourceId = ref(null)
const resourceName = ref(null)
const mode = computed(() => route.query.mode)

const { fields, getUpdateFields } = useResourceFields()
const { form } = useFieldsForm(fields)

const isModalVisible = ref(false)
const modalSubTitle = ref(null)
const componentReady = ref(false)

// Provide initial focus element as the modal can be nested and it's not
// finding an element for some reason when the second modal is closed
// showing error "There are no focusable elements inside the <FocusTrap />"
const modalCloseElement = computedWithControl(
  () => null,
  () => document.querySelector('#modalClose-floatingResourceModal')
)

const floatingKey = computed(() => {
  return String(
    String(route.query.floating_resource) +
      String(route.query.floating_resource_id)
  )
})

const {
  resource,
  resourceSingularName,
  updateResource,
  fetchResource,
  synchronizeResource,
  detachResourceAssociations,
  incrementResourceCount,
  decrementResourceCount,
} = useResource(resourceName, resourceId, {
  watchId: false,
})

function emitUpdatedGlobalEvent() {
  Innoclapps.$emit('floating-resource-updated', {
    resourceName: resourceName.value,
    resourceId: resourceId.value,
    resource: resource.value,
  })
}

async function performUpdate() {
  await updateResource(form)

  emitUpdatedGlobalEvent()

  Innoclapps.success(t('core::resource.updated'))
}

async function boot() {
  form.clear()
  fields.value.set([])
  resource.value = {}

  isModalVisible.value = true

  await fetchResource()

  // We will use update fields as if we use detail fields, some fields
  // may not be available for update when floating a resource.
  fields.value
    .set(await getUpdateFields(resourceName.value, resourceId.value))
    .populate(resource.value)

  componentReady.value = true
}

/**
 * Helper method to navigate to the actual record full view/update
 * The method uses the current already fetched record from database and passes as meta
 * This helps not making the same request again
 */
function view() {
  router[resourceSingularName.value] = resource.value
  router.push(resource.value.path)
}

function setModalSubTitle(subtitle) {
  modalSubTitle.value = subtitle
}

function handleModalHidden() {
  componentReady.value = false

  router.replace({
    query: omit(route.query, [
      'floating_resource',
      'floating_resource_id',
      'mode',
    ]),
  })
}

watch(
  floatingKey,
  () => {
    resourceId.value = route.query.floating_resource_id
    resourceName.value = route.query.floating_resource

    if (resourceName.value && resourceId.value) {
      componentReady.value = false
      nextTick(boot)
    } else {
      isModalVisible.value = false
    }
  },
  { immediate: true }
)

provide('setModalSubTitle', setModalSubTitle)
provide('synchronizeResource', synchronizeResource)
provide('detachResourceAssociations', detachResourceAssociations)
provide('incrementResourceCount', incrementResourceCount)
provide('decrementResourceCount', decrementResourceCount)
provide('emitUpdatedGlobalEvent', emitUpdatedGlobalEvent)
provide(
  'synchronizeAndEmitUpdatedEvent',
  (updatedResource, isFreshObject = false) => {
    synchronizeResource(updatedResource, isFreshObject)
    emitUpdatedGlobalEvent()
  }
)
</script>
