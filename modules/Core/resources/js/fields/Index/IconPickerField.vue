<template>
  <IndexFieldsItem
    :resource-name="resourceName"
    :resource-id="resourceId"
    :row="row"
    :field="field"
  >
    <template #field="{ hasValue, value }">
      <Icon v-if="hasValue" :icon="value" class="h-5 w-5" />
      <span v-else>&mdash;</span>
    </template>
  </IndexFieldsItem>
</template>

<script setup>
defineProps(['column', 'row', 'field', 'resourceName', 'resourceId'])
</script>
