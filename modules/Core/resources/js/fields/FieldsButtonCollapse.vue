<template>
  <button
    type="button"
    class="flex items-center rounded-md border border-transparent text-neutral-500 outline-none hover:border-neutral-100 hover:bg-neutral-50 focus:border-neutral-100 dark:hover:border-neutral-700 dark:hover:bg-neutral-800 dark:focus:border-neutral-700"
    @click="$emit('update:collapsed', !collapsed)"
  >
    <div class="inline-flex px-1.5 py-1">
      <Icon
        :icon="collapsed ? 'ChevronDown' : 'ChevronUp'"
        class="-mt-px h-[1.1rem] w-[1.1rem]"
      />
      <span
        v-t="{
          path: collapsed
            ? 'core::fields.more_fields'
            : 'core::fields.hide_fields',
          args: { count: total },
        }"
        class="ml-2 text-xs"
      />
    </div>
  </button>
</template>

<script setup>
defineEmits(['update:collapsed'])

defineProps({
  collapsed: { required: true, type: Boolean },
  total: { type: Number, required: true },
})
</script>
