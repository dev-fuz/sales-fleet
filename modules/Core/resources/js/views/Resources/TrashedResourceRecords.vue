<template>
  <ILayout>
    <ResourceTrashedTable :resource-name="$route.params.resourceName" />
  </ILayout>
</template>

<script setup>
import ResourceTrashedTable from '~/Core/components/Table/TrashedTable.vue'
</script>
