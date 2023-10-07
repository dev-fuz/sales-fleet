<template>
  <IndexFieldsItem
    :resource-name="resourceName"
    :resource-id="resourceId"
    :row="row"
    :field="field"
  >
    <template #field="{ hasValue }">
      {{ formattedDate }}
      <span v-if="!hasValue">&mdash;</span>
    </template>
  </IndexFieldsItem>
</template>

<script setup>
import { computed } from 'vue'

import { useDates } from '~/Core/composables/useDates'

const props = defineProps([
  'column',
  'row',
  'field',
  'resourceName',
  'resourceId',
])

const { localizedDate, localizedDateTime } = useDates()

const formattedDate = computed(() => {
  if (!props.row[props.column.attribute]) {
    return ''
  }

  if (props.row[props.column.attribute].indexOf(' ') > -1) {
    return localizedDateTime(props.row[props.column.attribute])
  } else {
    return localizedDate(props.row[props.column.attribute])
  }
})
</script>
