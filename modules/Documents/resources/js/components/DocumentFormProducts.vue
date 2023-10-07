<template>
  <div v-show="visible" class="mx-auto max-w-5xl">
    <h3
      v-t="'documents::document.document_products'"
      class="mb-6 text-base font-medium text-neutral-800 dark:text-neutral-100"
    />

    <div
      :class="{
        'pointer-events-none opacity-70': document.status === 'accepted',
      }"
    >
      <BillableFormTaxTypes
        v-model="form.billable.tax_type"
        class="mb-4 flex flex-col space-y-1 sm:flex-row sm:space-x-2 sm:space-y-0"
      />

      <BillableFormTableProducts
        v-model:products="form.billable.products"
        v-model:removed-products="form.billable.removed_products"
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
  </div>
</template>

<script setup>
import BillableFormTableProducts from '~/Billable/components/BillableFormTableProducts.vue'
import BillableFormTaxTypes from '~/Billable/components/BillableFormTaxTypes.vue'

import propsDefinition from './formSectionProps'

const props = defineProps(propsDefinition)

function handleProductRemovedEvent(e) {
  // Clear errors in case there was error previously for the index
  // If we don't clear the errors the product that is below will be
  // shown as error after the given index is deleted
  // e.q. add 2 products, cause error on first, delete first
  if (props.form.errors.has('billable.products.' + e.index + '.name')) {
    props.form.errors.clear('billable.products.' + e.index + '.name')
  }
}
</script>
