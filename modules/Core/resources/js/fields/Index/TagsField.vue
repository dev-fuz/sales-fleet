<template>
  <IndexFieldsItem
    :resource-name="resourceName"
    :resource-id="resourceId"
    :row="row"
    :field="field"
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
  </IndexFieldsItem>
</template>

<script setup>
import OptionsList from '../OptionsList.vue'

defineProps(['column', 'row', 'field', 'resourceName', 'resourceId'])
</script>
