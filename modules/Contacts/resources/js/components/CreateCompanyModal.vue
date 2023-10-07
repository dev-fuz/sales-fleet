<template>
  <ISlideover
    id="createCompanyModal"
    :visible="visible"
    :title="title || $t('contacts::company.create')"
    :ok-title="$t('core::app.create')"
    :ok-disabled="form.busy"
    :cancel-title="$t('core::app.cancel')"
    :initial-focus="modalCloseElement"
    static-backdrop
    form
    @shown="handleModalShownEvent"
    @hidden="handleModalHiddenEvent"
    @submit="createUsing ? createUsing(create) : create()"
    @update:visible="$emit('update:visible', $event)"
  >
    <FieldsPlaceholder v-if="fields.isEmpty()" />

    <slot name="top" :is-ready="fields.isNotEmpty()"></slot>

    <div v-show="fieldsVisible">
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

        <template v-if="trashedCompanyByEmail !== null" #after-email-field>
          <IAlert
            dismissible
            class="mb-3"
            @dismissed="
              ;(recentlyRestored.byEmail = false),
                (trashedCompanyByEmail = null)
            "
          >
            {{ $t('contacts::company.exists_in_trash_by_email') }}

            <div class="mt-4">
              <div class="-mx-2 -my-1.5 flex">
                <IButtonMinimal
                  v-show="!recentlyRestored.byEmail"
                  variant="info"
                  :text="$t('core::app.soft_deletes.restore')"
                  @click="restoreTrashed(trashedCompanyByEmail.id, 'byEmail')"
                />
                <IButtonMinimal
                  v-show="recentlyRestored.byEmail"
                  variant="info"
                  :to="{
                    name: 'view-company',
                    params: { id: trashedCompanyByEmail.id },
                  }"
                  :text="$t('core::app.view_record')"
                />
              </div>
            </div>
          </IAlert>
        </template>

        <template v-if="trashedCompanyByName !== null" #after-name-field>
          <IAlert
            v-if="trashedCompanyByName"
            dismissible
            class="mb-3"
            @dismissed="
              ;(recentlyRestored.byName = false), (trashedCompanyByName = null)
            "
          >
            {{ $t('contacts::company.exists_in_trash_by_name') }}

            <div class="mt-4">
              <div class="-mx-2 -my-1.5 flex">
                <IButtonMinimal
                  v-show="!recentlyRestored.byName"
                  variant="info"
                  :text="$t('core::app.soft_deletes.restore')"
                  @click="restoreTrashed(trashedCompanyByName.id, 'byName')"
                />
                <IButtonMinimal
                  v-show="recentlyRestored.byName"
                  variant="info"
                  :to="{
                    name: 'view-company',
                    params: { id: trashedCompanyByName.id },
                  }"
                  :text="$t('core::app.view_record')"
                />
              </div>
            </div>
          </IAlert>
        </template>

        <template v-if="trashedCompaniesByPhone.length > 0" #after-phones-field>
          <IAlert
            v-for="(company, index) in trashedCompaniesByPhone"
            :key="company.id"
            dismissible
            class="mb-3"
            @dismissed="
              ;(recentlyRestored.byPhone[company.id] = false),
                (trashedCompaniesByPhone[index] = null)
            "
          >
            {{
              $t('contacts::company.exists_in_trash_by_phone', {
                company: company.display_name,
                phone_numbers: company.phones
                  .map(phone => phone.number)
                  .join(','),
              })
            }}

            <div class="mt-4">
              <div class="-mx-2 -my-1.5 flex">
                <IButtonMinimal
                  v-show="!recentlyRestored.byPhone[company.id]"
                  variant="info"
                  :text="$t('core::app.soft_deletes.restore')"
                  @click="restoreTrashed(company.id, 'byPhone')"
                />
                <IButtonMinimal
                  v-show="recentlyRestored.byPhone[company.id]"
                  variant="info"
                  :to="{
                    name: 'view-company',
                    params: { id: company.id },
                  }"
                  :text="$t('core::app.view_record')"
                />
              </div>
            </div>
          </IAlert>
        </template>
      </FormFields>
    </div>

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
          v-show="goToList"
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
  </ISlideover>
</template>

<script setup>
import { ref, shallowRef } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import { whenever } from '@vueuse/core'
import { watchDebounced } from '@vueuse/shared'
import { computedWithControl } from '@vueuse/shared'

import { useFieldsForm } from '~/Core/composables/useFieldsForm'
import { useResourceable } from '~/Core/composables/useResourceable'
import { useResourceFields } from '~/Core/composables/useResourceFields'

const emit = defineEmits([
  'created',
  'restored',
  'modal-shown',
  'modal-hidden',
  'update:visible',
  'ready',
])

const props = defineProps({
  visible: { type: Boolean, default: true },
  goToList: { type: Boolean, default: true },
  redirectToView: Boolean,
  createUsing: Function,
  withExtendedSubmitButtons: Boolean,
  fieldsVisible: { type: Boolean, default: true },
  title: String,

  contacts: Array,
  deals: Array,
})

const { t } = useI18n()
const router = useRouter()

const resourceName = Innoclapps.resourceName('companies')

const dealBeingCreated = ref(false)
const contactBeingCreated = ref(false)

const emailField = ref({})
const nameField = ref({})
const phoneField = ref({})

const trashedCompanyByEmail = shallowRef(null)
const trashedCompanyByName = shallowRef(null)
const trashedCompaniesByPhone = ref([])

const recentlyRestored = ref({
  byName: false,
  byEmail: false,
  byPhone: {},
})

const { fields, getCreateFields } = useResourceFields()

const { form } = useFieldsForm(fields)
const { createResource } = useResourceable(resourceName)

// Provide initial focus element as the modal can be nested and it's not
// finding an element for some reason when the second modal is closed
// showing error "There are no focusable elements inside the <FocusTrap />"
const modalCloseElement = computedWithControl(
  () => null,
  () => document.querySelector('#modalClose-createCompanyModal')
)

whenever(() => props.visible, prepareComponent, { immediate: true })

watchDebounced(
  () => emailField.value?.currentValue,
  newVal => {
    if (!newVal) {
      trashedCompanyByEmail.value = null

      return
    }

    searchTrashedCompanies(newVal, 'email').then(({ data: companies }) => {
      trashedCompanyByEmail.value = companies.length > 0 ? companies[0] : null
    })
  },
  { debounce: 500 }
)

watchDebounced(
  () => nameField.value.currentValue,
  newVal => {
    if (!newVal) {
      trashedCompanyByName.value = null

      return
    }

    searchTrashedCompanies(newVal, 'name').then(({ data: companies }) => {
      trashedCompanyByName.value = companies.length > 0 ? companies[0] : null
    })
  },
  { debounce: 500 }
)

watchDebounced(
  () => phoneField.value?.currentValue,
  newVal => {
    if (!newVal) return

    const numbers = newVal
      .filter(
        phone =>
          !phone.number ||
          !(
            phoneField.value.callingPrefix &&
            phoneField.value.callingPrefix.trim() === phone.number
          )
      )
      .map(phone => phone.number)

    if (numbers.length === 0) {
      trashedCompaniesByPhone.value = []

      return
    }

    Innoclapps.request()
      .get('/trashed/companies/search', {
        params: {
          q: numbers.join(','),
          search_fields: 'phones.number:in',
        },
      })
      .then(({ data: companies }) => {
        trashedCompaniesByPhone.value = companies
      })
  },
  { debounce: 500, deep: true }
)

function onAfterCreate(data) {
  data.indexRoute = { name: 'company-index' }

  if (data.action === 'go-to-list') {
    return router.push(data.indexRoute)
  }

  if (data.action === 'create-another') return

  if (props.redirectToView) {
    let company = data.company
    router.company = company

    router.push({
      name: 'view-company',
      params: {
        id: company.id,
      },
    })
  }
}

function handleModalShownEvent() {
  emit('modal-shown')

  modalCloseElement.trigger()
}

function handleModalHiddenEvent() {
  emit('modal-hidden')

  fields.value.set([])
  form.reset()
}

function searchTrashedCompanies(q, field) {
  return Innoclapps.request().get('/trashed/companies/search', {
    params: {
      q: q,
      search_fields: field + ':=',
    },
  })
}

function restoreTrashed(id, type) {
  Innoclapps.request()
    .post(`/trashed/companies/${id}`)
    .then(({ data }) => {
      if (typeof recentlyRestored.value[type] === 'object') {
        recentlyRestored.value[type][data.id] = true
      } else {
        recentlyRestored.value[type] = true
      }
      emit('restored', data)
    })
}

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
  let company = await createResource(form).catch(e => {
    if (e.isValidationError()) {
      Innoclapps.error(t('core::app.form_validation_failed'), 3000)
    }

    return Promise.reject(e)
  })

  let payload = {
    company: company,
    isRegularAction: actionType === null,
    action: actionType,
  }

  emit('created', payload)

  Innoclapps.success(t('core::resource.created'))

  return payload
}

async function prepareComponent() {
  fields.value.set(await getCreateFields(resourceName))

  // From props, same attribute name and prop name
  ;['contacts', 'deals'].forEach(attribute => {
    if (props[attribute]) {
      fields.value.updateValue(attribute, props[attribute])
    }
  })

  emailField.value = fields.value.find('email')
  nameField.value = fields.value.find('name')
  phoneField.value = fields.value.find('phones')

  emit('ready', fields)
}
</script>
