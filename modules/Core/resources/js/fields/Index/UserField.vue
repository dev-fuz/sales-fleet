<template>
  <IndexFieldsItem
    :resource-name="resourceName"
    :resource-id="resourceId"
    :row="row"
    :field="field"
  >
    <template #field="{ hasValue, value }">
      <span v-if="hasValue" class="inline-flex items-center">
        <IAvatar :src="value.avatar_url" :alt="value.name" size="xs" />

        <span class="ml-1 truncate" v-text="value.name"></span>
      </span>
      <span v-else>&mdash;</span>
    </template>
  </IndexFieldsItem>
</template>

<script setup>
defineProps(['column', 'row', 'field', 'resourceName', 'resourceId'])
</script>
