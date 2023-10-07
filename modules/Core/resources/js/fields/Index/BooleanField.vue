<template>
  <IndexFieldsItem
    :resource-name="resourceName"
    :resource-id="resourceId"
    :row="row"
    :field="field"
  >
    <template #field>
      <span
        :class="[
          'mt-0.5 inline-block h-4 w-4 rounded-full',
          isTruthy ? 'bg-success-400' : 'bg-danger-400',
        ]"
      />
    </template>
  </IndexFieldsItem>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps([
  'column',
  'row',
  'field',
  'resourceName',
  'resourceId',
])

const isTruthy = computed(() => props.field.value === props.field.trueValue)
</script>
