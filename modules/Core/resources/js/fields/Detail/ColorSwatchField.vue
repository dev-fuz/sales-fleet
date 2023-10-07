<template>
  <DetailFieldsItem
    :field="field"
    :is-floating="isFloating"
    :resource="resource"
    :resource-name="resourceName"
    :resource-id="resourceId"
  >
    <template #field="{ hasValue, value }">
      <span
        v-if="hasValue"
        class="block h-6 w-6 rounded-md"
        :style="{ backgroundColor: value }"
      />
      <span v-else>&mdash;</span>
    </template>
  </DetailFieldsItem>
</template>

<script setup>
defineProps(['resource', 'resourceName', 'resourceId', 'field', 'isFloating'])
</script>
