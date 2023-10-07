<template>
  <button
    type="button"
    :disabled="disabled"
    class="ml-1 text-neutral-400 hover:text-neutral-600 focus:outline-none dark:text-neutral-200 dark:hover:text-neutral-400"
    :title="`Deselect ${label}`"
    :aria-label="`Deselect ${label}`"
    @click.prevent.stop="deselect(option)"
  >
    <Icon icon="X" class="h-4 w-4" />
  </button>
</template>

<script setup>
defineProps({
  option: { required: true },
  deselect: { type: Function, required: true },
  disabled: Boolean,
  label: { type: [String, Number], required: true },
})
</script>
