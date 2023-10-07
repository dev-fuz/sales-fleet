<template>
  <DetailFieldsItem
    :field="field"
    :is-floating="isFloating"
    :resource="resource"
    :resource-name="resourceName"
    :resource-id="resourceId"
  >
    <template #field>
      <OptionsList
        :value="field.value"
        :resource-name="resourceName"
        :resource-id="resourceId"
        label-key="name"
        :display-as-pills="true"
      />
    </template>
  </DetailFieldsItem>
</template>

<script setup>
import OptionsList from '../OptionsList.vue'

defineProps(['resource', 'resourceName', 'resourceId', 'field', 'isFloating'])
</script>
