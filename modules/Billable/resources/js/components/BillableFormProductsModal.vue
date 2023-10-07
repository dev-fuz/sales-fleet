<template>
  <IModal
    id="productsModal"
    :cancel-title="$t('core::app.cancel')"
    :ok-title="$t('core::app.save')"
    :ok-disabled="form.busy"
    :title="$t('billable::product.manage')"
    :visible="visible"
    size="xxl"
    static-backdrop
    @ok="save"
    @hidden="handleModalHiddenEvent"
    @show="handleModalShowEvent"
  >
    <IOverlay :show="!formReady">
      <BillableFormTaxTypes
        v-model="form.tax_type"
        class="mb-4 mt-6 flex flex-col space-y-1 sm:flex-row sm:space-x-2 sm:space-y-0"
      />

      <BillableFormProductsModal
        v-if="formReady"
        ref="productsRef"
        v-model:products="form.products"
        v-model:removed-products="form.removed_products"
        :tax-type="form.tax_type"
        @product-selected="
          form.errors.clear('products.' + $event.index + '.name')
        "
        @product-removed="handleProductRemovedEvent"
      >
        <template #after-product-select="{ index }">
          <IFormError v-text="form.getError('products.' + index + '.name')" />
        </template>
      </BillableFormProductsModal>
    </IOverlay>
  </IModal>
</template>

<script setup>
import { nextTick, ref } from 'vue'
import { whenever } from '@vueuse/core'
import cloneDeep from 'lodash/cloneDeep'

import { useForm } from '~/Core/composables/useForm'

import BillableFormProductsModal from './BillableFormTableProducts.vue'
import BillableFormTaxTypes from './BillableFormTaxTypes.vue'

const emit = defineEmits(['saved', 'hidden'])

const props = defineProps({
  billable: Object,
  visible: Boolean,
  prefetch: Boolean,
  resourceName: { required: true, type: String },
  resourceId: { required: true, type: Number },
})

const { form } = useForm({
  products: [],
  removed_products: [],
})

const productsRef = ref(null)
const formReady = ref(false)

function handleProductRemovedEvent(e) {
  // Clear errors in case there was error previously for the index
  // If we don't clear the errors the product that is below will be
  // shown as error after the given index is deleted
  // e.q. add 2 products, cause error on first, delete first
  if (form.errors.has('products.' + e.index + '.name')) {
    form.errors.clear('products.' + e.index + '.name')
  }
}

function handleModalHiddenEvent() {
  emit('hidden')
  formReady.value = false
}

async function save() {
  let billable = await form.post(
    `${props.resourceName}/${props.resourceId}/billable`
  )

  emit('saved', billable)

  Innoclapps.modal().hide('productsModal')
}

async function handleModalShowEvent() {
  let billable = cloneDeep(
    props.prefetch ? await fetchBillable() : props.billable || {}
  )

  form.set('tax_type', billable.tax_type || 'exclusive')
  form.set('products', billable.products || [])

  formReady.value = true
}

whenever(
  formReady,
  () => {
    if (form.products.length === 0) {
      nextTick(productsRef.value.insertNewLine)
    }
  },
  { flush: 'post' }
)

async function fetchBillable() {
  let { data } = await Innoclapps.request().get(
    `/${props.resourceName}/${props.resourceId}/billable`
  )

  return data
}
</script>
