<template>
  <BillableFormProductsModal
    v-if="manageProducts"
    :resource-name="resourceName"
    :resource-id="resourceId"
    visible
    prefetch
    @saved="handleBillableModelSavedEvent"
    @hidden="handleModalHidden"
  />

  <DetailNumericField
    v-bind="$attrs"
    :resource-name="resourceName"
    :resource-id="resourceId"
    :is-floating="isFloating"
    :field="{ ...field, ...{ readonly: hasProducts } }"
    :resource="resource"
    :edit-action="
      field.onlyProducts || hasProducts ? initiateProductsModal : undefined
    "
    @updated="$emit('updated', $event)"
  >
    <template #numeric-field="{ formattedValue }">
      {{ formattedValue }}
      <span
        v-if="hasProducts"
        class="ml-1 text-xs text-neutral-500 dark:text-neutral-400"
      >
        ({{
          $t('billable::product.count', { count: resource.products_count })
        }})
      </span>
    </template>
    <template #after-inline-edit-form-fields="{ hidePopover }">
      <a
        href="#"
        class="link flex items-center text-sm"
        @click.prevent="initiateProductsModal(), hidePopover()"
      >
        <span v-t="'billable::product.manage'"></span>
        <Icon icon="Window" class="ml-2 mt-px h-4 w-4" />
      </a>
    </template>
  </DetailNumericField>
</template>

<script setup>
import { computed, ref } from 'vue'

import BillableFormProductsModal from '../../components/BillableFormProductsModal.vue'

const emit = defineEmits(['updated'])

const props = defineProps([
  'resource',
  'resourceName',
  'resourceId',
  'field',
  'isFloating',
])

defineOptions({
  inheritAttrs: false,
})

const hasProducts = computed(() => props.resource.products_count > 0)

const manageProducts = ref(false)

function handleModalHidden() {
  setTimeout(() => (manageProducts.value = false), 200)
}

function handleBillableModelSavedEvent(billable) {
  emit(
    'updated',
    Object.assign({}, props.resource, {
      billable,
      [props.field.attribute]: billable.total,
      products_count: billable.products.length,
    })
  )
}

function initiateProductsModal() {
  manageProducts.value = true
}
</script>
