<template>
  <IndexFieldsItem
    :resource-name="resourceName"
    :resource-id="resourceId"
    :row="row"
    :field="field"
  >
    <template #field="{ hasValue, value }">
      {{ hasValue ? localizedDateTime(value) : '' }}
      <span v-if="!hasValue">&mdash;</span>
    </template>
  </IndexFieldsItem>
</template>

<script setup>
import { useDates } from '~/Core/composables/useDates'

defineProps(['column', 'row', 'field', 'resourceName', 'resourceId'])

const { localizedDateTime } = useDates()
</script>
