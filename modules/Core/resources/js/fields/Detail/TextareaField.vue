<template>
  <DetailFieldsItem
    :field="field"
    :is-floating="isFloating"
    :resource="resource"
    :resource-name="resourceName"
    :resource-id="resourceId"
  >
    <template #field="{ hasValue, value }">
      <div v-if="hasValue" v-text="value" />
      <span v-else>&mdash;</span>
    </template>
  </DetailFieldsItem>
</template>

<script setup>
defineProps(['resource', 'resourceName', 'resourceId', 'field', 'isFloating'])
</script>
