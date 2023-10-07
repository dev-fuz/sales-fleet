<template>
  <IndexFieldsItem
    :resource-name="resourceName"
    :resource-id="resourceId"
    :row="row"
    :field="field"
  >
    <template #field="{ hasValue, value }">
      <p v-if="hasValue">
        {{ value }}
      </p>
      <span v-else>&mdash;</span>
    </template>
  </IndexFieldsItem>
</template>

<script setup>
defineProps(['column', 'row', 'field', 'resourceName', 'resourceId'])
</script>
