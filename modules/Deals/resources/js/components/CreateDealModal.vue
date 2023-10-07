<template>
  <ISlideover
    id="createDealModal"
    :visible="visible"
    :title="title || $t('deals::deal.create')"
    :ok-title="$t('core::app.create')"
    :ok-disabled="form.busy"
    :cancel-title="$t('core::app.cancel')"
    :initial-focus="modalCloseElement"
    static-backdrop
    form
    :size="withProducts ? 'xxl' : 'md'"
    @shown="handleModalShownEvent"
    @hidden="handleModalHiddenEvent"
    @submit="createUsing ? createUsing(create) : create()"
    @update:visible="$emit('update:visible', $event)"
  >
    <FieldsPlaceholder v-if="fields.isEmpty()" />

    <slot name="top" :is-ready="fields.isNotEmpty()"></slot>

    <div v-show="fieldsVisible">
      <div v-if="withProducts" class="mb-4 border-b border-neutral-200 pb-8">
        <h3
          class="mb-5 inline-flex items-center text-base font-medium text-neutral-800"
        >
          <span v-t="'billable::product.products'" />

          <a
            v-show="withProducts"
            v-t="'deals::deal.dont_add_products'"
            href="#"
            class="link ml-2 mt-0.5 text-sm"
            @click.prevent="hideProductsSection"
          />
        </h3>

        <BillableFormTaxTypes
          v-model="form.billable.tax_type"
          class="mb-4 flex flex-col space-y-1 sm:flex-row sm:space-x-2 sm:space-y-0"
        />

        <BillableFormTableProducts
          v-model:products="form.billable.products"
          :tax-type="form.billable.tax_type"
          @product-selected="
            form.errors.clear('billable.products.' + $event.index + '.name')
          "
          @product-removed="handleProductRemovedEvent"
        >
          <template #after-product-select="{ index }">
            <IFormError
              v-text="form.getError('billable.products.' + index + '.name')"
            />
          </template>
        </BillableFormTableProducts>
      </div>

      <FormFields
        :fields="fields"
        :form-id="form.formId"
        :resource-name="resourceName"
        is-floating
        focus-first
      >
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

        <template #after-amount-field>
          <span class="-mt-2 block text-right">
            <a
              v-show="!withProducts"
              href="#"
              class="link text-sm"
              @click.prevent="showProductsSection"
            >
              &plus; {{ $t('deals::deal.add_products') }}
            </a>
            <a
              v-show="withProducts"
              v-t="'deals::deal.dont_add_products'"
              href="#"
              class="link text-sm"
              @click.prevent="hideProductsSection"
            />
          </span>
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
          v-if="goToList"
          :text="$t('core::app.create_and_go_to_list')"
          @click="createAndGoToList"
        />
      </IDropdownButtonGroup>
    </template>

    <CreateContactModal
      v-model:visible="contactBeingCreated"
      :overlay="false"
      @created="
        fields.mergeValue('contacts', $event.contact),
          (contactBeingCreated = false)
      "
    />

    <CreateCompanyModal
      v-model:visible="companyBeingCreated"
      :overlay="false"
      @created="
        fields.mergeValue('companies', $event.company),
          (companyBeingCreated = false)
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
import castArray from 'lodash/castArray'
import cloneDeep from 'lodash/cloneDeep'
import find from 'lodash/find'
import findIndex from 'lodash/findIndex'

import { useFieldsForm } from '~/Core/composables/useFieldsForm'
import { useResourceable } from '~/Core/composables/useResourceable'
import { useResourceFields } from '~/Core/composables/useResourceFields'

import BillableFormTableProducts from '~/Billable/components/BillableFormTableProducts.vue'
import BillableFormTaxTypes from '~/Billable/components/BillableFormTaxTypes.vue'

const emit = defineEmits([
  'created',
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

  disabledFields: [Array, String],
  hiddenFields: [Array, String],

  associations: Object,
  // Must be passed if stageId is provided
  pipeline: Object,
  stageId: Number,
  name: String,
  contacts: Array,
  companies: Array,
})

const resourceName = Innoclapps.resourceName('deals')

const { t } = useI18n()
const router = useRouter()

const contactBeingCreated = ref(false)
const companyBeingCreated = ref(false)

const withProducts = ref(false)

const { fields, getCreateFields } = useResourceFields()

const { form } = useFieldsForm(fields, {
  billable: {
    tax_type: Innoclapps.config('options.tax_type'),
    products: [],
  },
})

const { createResource } = useResourceable(resourceName)

// Provide initial focus element as the modal can be nested and it's not
// finding an element for some reason when the second modal is closed
// showing error "There are no focusable elements inside the <FocusTrap />"
const modalCloseElement = computedWithControl(
  () => null,
  () => document.querySelector('#modalClose-createDealModal')
)

whenever(() => props.visible, prepareComponent, { immediate: true })

function onAfterCreate(data) {
  data.indexRoute = { name: 'deal-index' }

  if (data.action === 'go-to-list') {
    return router.push(data.indexRoute)
  }

  if (data.action === 'create-another') return

  if (props.redirectToView) {
    let deal = data.deal
    router.deal = deal

    router.push({
      name: 'view-deal',
      params: {
        id: deal.id,
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

  withProducts.value = false
  resetBillable()

  fields.value.set([])
  form.reset()
}

/** Reset the form billable */
function resetBillable() {
  form.billable.products = []
  form.billable.tax_type = Innoclapps.config('options.tax_type')
}

function showProductsSection() {
  withProducts.value = true
  fields.value.update('amount', { readonly: true })
}

function hideProductsSection() {
  withProducts.value = false
  fields.value.update('amount', { readonly: false })
}

function handleProductRemovedEvent(e) {
  // Clear errors in case there was error previously for the index
  // If we don't clear the errors the product that is below will be
  // shown as error after the given index is deleted
  // e.q. add 2 products, cause error on first, delete first
  if (form.errors.has('billable.products.' + e.index + '.name')) {
    form.errors.clear('billable.products.' + e.index + '.name')
  }
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
  if (!withProducts.value) {
    resetBillable()
  }

  if (props.associations) {
    form.fill(props.associations)
  }

  let deal = await createResource(form).catch(e => {
    if (e.isValidationError()) {
      Innoclapps.error(t('core::app.form_validation_failed'), 3000)
    }

    return Promise.reject(e)
  })

  let payload = {
    deal: deal,
    isRegularAction: actionType === null,
    action: actionType,
  }

  emit('created', payload)

  Innoclapps.success(t('core::resource.created'))

  return payload
}

async function prepareComponent() {
  let createFields = cloneDeep(await getCreateFields(resourceName))

  // From props, same attribute name and prop name
  ;['contacts', 'companies', 'name'].forEach(attribute => {
    if (props[attribute]) {
      createFields[findIndex(createFields, ['attribute', attribute])].value =
        props[attribute]
    }
  })

  if (props.pipeline) {
    createFields[findIndex(createFields, ['attribute', 'pipeline_id'])].value =
      props.pipeline

    // If pipeline is provided and no stage is provided, the default field value may have a different
    // stage, in this case, we need to set the first stage as active stage from the provided pipeline.
    if (!props.stageId) {
      createFields[findIndex(createFields, ['attribute', 'stage_id'])].value =
        props.pipeline.stages[0]
    }
  }

  if (props.stageId) {
    // Sets to read only as if the user change the e.q. stage
    // manually will have unexpected UI confusions
    createFields[findIndex(createFields, ['attribute', 'stage_id'])].value =
      props.stageId
        ? find(props.pipeline.stages, stage => stage.id === props.stageId)
        : null
  }

  fields.value.set(createFields)

  if (props.disabledFields) {
    castArray(props.disabledFields).forEach(attribute =>
      fields.value.update(attribute, { readonly: true })
    )
  }

  if (props.hiddenFields) {
    castArray(props.hiddenFields).forEach(attribute =>
      fields.value.update(attribute, { displayNone: true })
    )
  }

  emit('ready', fields)
}
</script>
