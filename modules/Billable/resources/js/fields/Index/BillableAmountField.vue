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

  <IndexNumericField
    v-bind="$attrs"
    :resource-name="resourceName"
    :resource-id="resourceId"
    :row="row"
    :field="field"
    :edit-action="
      field.onlyProducts || hasProducts ? initiateProductsModal : undefined
    "
    @reload="$emit('reload')"
  >
    <template #numeric-field="{ formattedValue }">
      {{ formattedValue }}
      <span
        v-if="hasProducts"
        class="ml-1 text-xs text-neutral-500 dark:text-neutral-400"
      >
        ({{ $t('billable::product.count', { count: row.products_count }) }})
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
  </IndexNumericField>
</template>

<script setup>
import { computed, ref } from 'vue'

import BillableFormProductsModal from '../../components/BillableFormProductsModal.vue'

const emit = defineEmits(['reload'])

const props = defineProps(['resourceName', 'resourceId', 'row', 'field'])

defineOptions({
  inheritAttrs: false,
})

const manageProducts = ref(false)

const hasProducts = computed(() => props.row.products_count > 0)

function handleModalHidden() {
  setTimeout(() => (manageProducts.value = false), 200)
}

function initiateProductsModal() {
  manageProducts.value = true
}

function handleBillableModelSavedEvent() {
  emit('reload')
}
</script>
