<template>
  <a
    href="#"
    class="flex items-center px-4 py-2 text-sm font-medium"
    aria-current="step"
    @click.prevent="$emit('click')"
  >
    <span
      class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border-2 border-success-500 dark:border-success-400"
    >
      <span class="text-success-500 dark:text-success-400">
        <ISpinner
          v-if="requestInProgress"
          class="h-6 w-6 text-success-500 dark:text-success-400"
        />
        <Icon
          v-else
          icon="Check"
          class="h-6 w-6 text-success-500 dark:text-success-400"
        />
      </span>
    </span>
    <span
      class="ml-4 text-sm font-medium text-success-500 dark:text-success-400"
    >
      <slot></slot>
    </span>
  </a>
</template>

<script setup>
defineEmits(['click'])

defineProps({ requestInProgress: Boolean })
</script>
