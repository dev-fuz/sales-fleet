<template>
  <component
    :is="section.field.formComponent"
    :field="section.field"
    :form-id="form.formId"
  />
</template>

<script setup>
import propsDefinition from './props'

defineProps(propsDefinition)
</script>
