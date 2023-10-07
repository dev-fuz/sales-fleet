<template>
  <IndexFieldsItem
    :resource-name="resourceName"
    :resource-id="resourceId"
    :row="row"
    :field="field"
  >
    <template #field="{ hasValue, value }">
      <p v-if="hasValue" class="text-sm">
        {{ value }}
        <span class="block text-xs text-neutral-500">
          {{ appMoment(new Date()).tz(value).format(dateTimeFormatForMoment) }}
        </span>
      </p>
      <span v-else>&mdash;</span>
    </template>
  </IndexFieldsItem>
</template>

<script setup>
import { useDates } from '../../composables/useDates'

defineProps(['column', 'row', 'field', 'resourceName', 'resourceId'])

const { appMoment, dateTimeFormatForMoment } = useDates()
</script>
