<template>
  <ILayout>
    <Import :resource-name="$route.params.resourceName" />
  </ILayout>
</template>

<script setup>
import Import from '~/Core/components/Import'
</script>
