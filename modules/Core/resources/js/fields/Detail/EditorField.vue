<template>
  <DetailFieldsItem
    :field="field"
    :is-floating="isFloating"
    :resource="resource"
    :resource-name="resourceName"
    :resource-id="resourceId"
  >
    <template #field="{ hasValue, value }">
      <TextCollapse :text="value || ''" />
      <span v-if="!hasValue">&mdash;</span>
    </template>
  </DetailFieldsItem>
</template>

<script setup>
defineProps(['resource', 'resourceName', 'resourceId', 'field', 'isFloating'])
</script>
