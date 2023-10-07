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
        :display-as-pills="field.displayAsPills"
        :each-on-new-line="field.eachOnNewLine"
        :on-click-redirect-to="field.onOptionClickRedirectTo"
        :label-key="field.labelKey"
      />
    </template>
  </IndexFieldsItem>
</template>

<script setup>
import OptionsList from '../OptionsList.vue'

defineProps(['column', 'row', 'field', 'resourceName', 'resourceId'])
</script>
