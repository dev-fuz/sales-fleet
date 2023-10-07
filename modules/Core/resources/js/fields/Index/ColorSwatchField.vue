<template>
  <IndexFieldsItem
    :resource-name="resourceName"
    :resource-id="resourceId"
    :row="row"
    :field="field"
  >
    <template #field="{ hasValue, value }">
      <span
        v-if="hasValue"
        class="block h-6 w-6 rounded-md"
        :style="{ backgroundColor: value }"
      />
      <span v-else>&mdash;</span>
    </template>
  </IndexFieldsItem>
</template>

<script setup>
defineProps(['column', 'row', 'field', 'resourceName', 'resourceId'])
</script>
