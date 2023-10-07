<template>
  <component
    :is="to ? 'router-link' : 'a'"
    v-i-tooltip="tooltip"
    href="#"
    :to="to"
    class="text-neutral-400 hover:text-neutral-600"
    @click.prevent="$emit('click', $event)"
  >
    <slot>
      <Icon :icon="icon" class="h-4 w-4 text-current" />
    </slot>
  </component>
</template>

<script setup>
defineEmits(['click'])
defineProps(['icon', 'tooltip', 'to'])
</script>
