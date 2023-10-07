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
        :label-key="field.labelKey"
        :on-click-redirect-to="field.onOptionClickRedirectTo"
        :display-as-pills="true"
      />
    </template>
  </IndexFieldsItem>
</template>

<script setup>
import OptionsList from '~/Core/fields/OptionsList.vue'

defineProps(['column', 'row', 'field', 'resourceName', 'resourceId'])
</script>
