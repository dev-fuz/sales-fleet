<template>
  <DetailFieldsItem
    :field="field"
    :is-floating="isFloating"
    :resource="resource"
    :resource-name="resourceName"
    :resource-id="resourceId"
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
  </DetailFieldsItem>
</template>

<script setup>
import { useDates } from '../../composables/useDates'

defineProps(['resource', 'resourceName', 'resourceId', 'field', 'isFloating'])

const { appMoment, dateTimeFormatForMoment } = useDates()
</script>
