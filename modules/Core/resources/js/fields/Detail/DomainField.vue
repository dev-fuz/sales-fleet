<template>
  <DetailFieldsItem
    :field="field"
    :is-floating="isFloating"
    :resource="resource"
    :resource-name="resourceName"
    :resource-id="resourceId"
  >
    <template #field="{ hasValue, value }">
      <a
        v-if="hasValue"
        :href="'http://' + value"
        target="_blank"
        rel="noopener noreferrer"
        class="link inline-flex items-center text-sm"
      >
        {{ value }}
        <Icon icon="ExternalLink" class="ml-1 h-4 w-4" />
      </a>
      <span v-else>&mdash;</span>
    </template>
  </DetailFieldsItem>
</template>

<script setup>
defineProps(['resource', 'resourceName', 'resourceId', 'field', 'isFloating'])
</script>
