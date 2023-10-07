<template>
  <DetailFieldsItem
    :field="field"
    :is-floating="isFloating"
    :resource="resource"
    :resource-name="resourceName"
    :resource-id="resourceId"
  >
    <template #field="{ hasValue, value }">
      {{ hasValue ? formatNumber(value) : '' }}
      <span v-if="!hasValue">&mdash;</span>
    </template>
  </DetailFieldsItem>
</template>

<script setup>
import { useAccounting } from '../../composables/useAccounting'

defineProps(['resource', 'resourceName', 'resourceId', 'field', 'isFloating'])

const { formatNumber } = useAccounting()
</script>
