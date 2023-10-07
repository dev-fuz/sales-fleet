<template>
  <ISlideover
    id="createActivityModal"
    :visible="visible"
    :title="title || $t('activities::activity.create')"
    :ok-title="$t('core::app.create')"
    :ok-disabled="form.busy"
    :cancel-title="$t('core::app.cancel')"
    :initial-focus="modalCloseElement"
    static-backdrop
    form
    @hidden="handleModalHiddenEvent"
    @shown="handleModalShownEvent"
    @update:visible="$emit('update:visible', $event)"
    @submit="create"
  >
    <FieldsPlaceholder v-if="fields.isEmpty()" />

    <FormFields
      :fields="fields"
      :form-id="form.formId"
      :resource-name="resourceName"
      is-floating
      focus-first
    >
      <template #after-deals-field>
        <span class="-mt-2 block text-right">
          <a
            href="#"
            class="link text-sm"
            @click.prevent="dealBeingCreated = true"
          >
            &plus; {{ $t('deals::deal.create') }}
          </a>
        </span>
      </template>

      <template #after-companies-field>
        <span class="-mt-2 block text-right">
          <a
            href="#"
            class="link text-sm"
            @click.prevent="companyBeingCreated = true"
          >
            &plus; {{ $t('contacts::company.create') }}
          </a>
        </span>
      </template>

      <template #after-contacts-field>
        <span class="-mt-2 block text-right">
          <a
            href="#"
            class="link text-sm"
            @click.prevent="contactBeingCreated = true"
          >
            &plus; {{ $t('contacts::contact.create') }}
          </a>
        </span>
      </template>
    </FormFields>

    <template v-if="withExtendedSubmitButtons" #modal-ok>
      <IDropdownButtonGroup
        type="submit"
        placement="top-end"
        :disabled="form.busy"
        :loading="form.busy"
        :text="$t('core::app.create')"
      >
        <IDropdownItem
          :text="$t('core::app.create_and_add_another')"
          @click="createAndAddAnother"
        />
        <IDropdownItem
          v-if="goToList"
          :text="$t('core::app.create_and_go_to_list')"
          @click="createAndGoToList"
        />
      </IDropdownButtonGroup>
    </template>

    <CreateDealModal
      v-model:visible="dealBeingCreated"
      :overlay="false"
      @created="
        fields.mergeValue('deals', $event.deal), (dealBeingCreated = false)
      "
    />

    <CreateContactModal
      v-model:visible="contactBeingCreated"
      :overlay="false"
      @created="
        fields.mergeValue('contacts', $event.contact),
          (contactBeingCreated = false)
      "
      @restored="
        fields.mergeValue('contacts', $event), (contactBeingCreated = false)
      "
    />

    <CreateCompanyModal
      v-model:visible="companyBeingCreated"
      :overlay="false"
      @created="
        fields.mergeValue('companies', $event.company),
          (companyBeingCreated = false)
      "
      @restored="
        fields.mergeValue('companies', $event), (companyBeingCreated = false)
      "
    />
  </ISlideover>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import { whenever } from '@vueuse/core'
import { computedWithControl } from '@vueuse/shared'
import cloneDeep from 'lodash/cloneDeep'
import map from 'lodash/map'

import { useFieldsForm } from '~/Core/composables/useFieldsForm'
import { useResourceable } from '~/Core/composables/useResourceable'
import { useResourceFields } from '~/Core/composables/useResourceFields'

const emit = defineEmits([
  'created',
  'modal-hidden',
  'modal-shown',
  'update:visible',
])

const props = defineProps({
  visible: { type: Boolean, default: true },
  goToList: { type: Boolean, default: true },
  redirectToView: Boolean,
  withExtendedSubmitButtons: Boolean,
  title: String,
  note: {},
  description: {},
  activityTypeId: {},
  contacts: {},
  companies: {},
  deals: {},
  dueDate: {},
  endDate: {},
  reminderMinutesBefore: {},
})

const resourceName = Innoclapps.resourceName('activities')

const { t } = useI18n()
const router = useRouter()

const { fields, getCreateFields } = useResourceFields()
const { form } = useFieldsForm(fields)
const { createResource } = useResourceable(resourceName)

const dealBeingCreated = ref(false)
const contactBeingCreated = ref(false)
const companyBeingCreated = ref(false)

// Provide initial focus element as the modal can be nested and it's not
// finding an element for some reason when the second modal is closed
// showing error "There are no focusable elements inside the <FocusTrap />"
const modalCloseElement = computedWithControl(
  () => null,
  () => document.querySelector('#modalClose-createActivityModal')
)

whenever(() => props.visible, prepareComponent, { immediate: true })

function create() {
  makeCreateRequest().then(onAfterCreate)
}

function createAndAddAnother() {
  makeCreateRequest('create-another').then(data => {
    form.reset()
    onAfterCreate(data)
  })
}

function createAndGoToList() {
  makeCreateRequest('go-to-list').then(onAfterCreate)
}

async function makeCreateRequest(actionType = null) {
  try {
    let activity = await createResource(form)

    let payload = {
      activity: activity,
      isRegularAction: actionType === null,
      action: actionType,
    }

    emit('created', payload)

    Innoclapps.success(t('activities::activity.created'))

    return payload
  } catch (e) {
    if (e.isValidationError()) {
      Innoclapps.error(t('core::app.form_validation_failed'), 3000)
    }

    return Promise.reject(e)
  }
}

function handleModalShownEvent() {
  emit('modal-shown')

  modalCloseElement.trigger()
}

function handleModalHiddenEvent() {
  emit('modal-hidden')

  fields.value.set([])
}

function onAfterCreate(data) {
  data.indexRoute = { name: 'activity-index' }

  if (data.action === 'go-to-list') {
    return router.push(data.indexRoute)
  }

  if (data.action === 'create-another') return

  // Not used yet as the activity has no view, it's an alias of EDIT
  if (props.redirectToView) {
    let activity = data.activity
    router.activity = activity

    router.push({
      name: 'view-activity',
      params: {
        id: activity.id,
      },
    })
  }
}

async function prepareComponent() {
  fields.value.set(
    map(cloneDeep(await getCreateFields(resourceName)), field => {
      if (
        [
          'contacts',
          'companies',
          'deals',
          'title',
          'note',
          'description',
        ].indexOf(field.attribute) > -1
      ) {
        field.value = props[field.attribute]
      } else if (
        field.attribute === 'activity_type_id' &&
        props.activityTypeId
      ) {
        field.value = props.activityTypeId
      } else if (field.attribute === 'due_date' && props.dueDate) {
        field.value = props.dueDate // object
      } else if (field.attribute === 'end_date' && props.endDate) {
        field.value = props.endDate // object
      } else if (
        field.attribute === 'reminder_minutes_before' &&
        props.reminderMinutesBefore
      ) {
        field.value = props.reminderMinutesBefore
      }

      return field
    })
  )
}
</script>
