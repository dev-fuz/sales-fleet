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
        :field="field"
        :resource-name="resourceName"
        :resource-id="resourceId"
        :label-key="field.labelKey"
        :display-as-pills="field.displayAsPills"
        :on-click-redirect-to="field.onOptionClickRedirectTo"
      />
    </template>
  </IndexFieldsItem>
</template>

<script setup>
import OptionsList from '../OptionsList.vue'

defineProps(['column', 'row', 'field', 'resourceName', 'resourceId'])
</script>
