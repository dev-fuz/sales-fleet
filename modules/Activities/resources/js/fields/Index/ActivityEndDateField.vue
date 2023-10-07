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
  if (!props.field.value) {
    return ''
  }

  if (props.field.value.indexOf(' ') > -1) {
    return localizedDateTime(props.field.value)
  } else {
    return localizedDate(props.field.value)
  }
})
</script>
