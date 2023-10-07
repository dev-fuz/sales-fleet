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
        :display-as-pills="field.displayAsPills"
        :each-on-new-line="field.eachOnNewLine"
        :on-click-redirect-to="field.onOptionClickRedirectTo"
        :label-key="field.labelKey"
      />
    </template>
  </DetailFieldsItem>
</template>

<script setup>
import OptionsList from '../OptionsList.vue'

defineProps(['resource', 'resourceName', 'resourceId', 'field', 'isFloating'])
</script>
