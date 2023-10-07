<template>
  <div
    class="flex items-center space-x-1 text-sm text-neutral-600 dark:text-white"
  >
    <span v-t="'core::table.length_menu.show'"></span>

    <DropdownSelectInput
      v-model="collection.perPage"
      :items="collection.perPageOptions"
      :disabled="disabled"
      @change="$emit('change', $event)"
    />

    <span v-t="'core::table.length_menu.entries'"></span>
  </div>
</template>

<script setup>
defineEmits(['change'])
defineProps({
  disabled: Boolean,
  collection: { required: true, type: Object },
})
</script>
