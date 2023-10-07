<template>
  <DetailFieldsItem
    :field="field"
    :is-floating="isFloating"
    :resource="resource"
    :resource-name="resourceName"
    :resource-id="resourceId"
  >
    <template #field="{ hasValue, value }">
      {{ hasValue ? localizedDate(value) : '' }}
      <span v-if="!hasValue">&mdash;</span>
    </template>
  </DetailFieldsItem>
</template>

<script setup>
import { useDates } from '../../composables/useDates'

defineProps(['resource', 'resourceName', 'resourceId', 'field', 'isFloating'])

const { localizedDate } = useDates()
</script>
