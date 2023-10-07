<template>
  <div>
    <EmailAccountsFormFolder
      v-for="(folder, index) in folders"
      :key="index + folder.name"
      :folder="folder"
      :index="index"
      :folders="folders"
    />
  </div>
</template>

<script setup>
import EmailAccountsFormFolder from './EmailAccountsFormFolder.vue'

defineProps({ folders: Array })
</script>
