<template>
  <IndexFieldsItem
    :resource-name="resourceName"
    :resource-id="resourceId"
    :row="row"
    :field="field"
  >
    <template #field="{ hasValue, value }">
      {{ hasValue ? formatNumber(value) : '' }}
      <span v-if="!hasValue">&mdash;</span>
    </template>
  </IndexFieldsItem>
</template>

<script setup>
import { useAccounting } from '../../composables/useAccounting'

defineProps(['column', 'row', 'field', 'resourceName', 'resourceId'])

const { formatNumber } = useAccounting()
</script>
