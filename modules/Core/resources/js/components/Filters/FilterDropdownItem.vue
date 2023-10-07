<template>
  <IDropdownItem
    :active="isActive"
    :text="name"
    :icon="isDefault ? 'Star' : null"
    prepend-icon
    @click="$emit('click', filterId)"
  />
</template>

<script setup>
import { computed } from 'vue'

import { useFilterable } from '../../composables/useFilterable'

defineEmits(['click'])

const props = defineProps({
  identifier: { required: true, type: String },
  view: { required: true, type: String },
  filterId: { type: Number, required: true },
  name: { type: String, required: true },
})

const { activeFilter, userDefaultFilter } = useFilterable(
  props.identifier,
  props.view
)

/**
 * Indicates whether the current filter is active
 */
const isActive = computed(
  () => activeFilter.value && activeFilter.value.id == props.filterId
)

/**
 * Indicates whether the given filter is default for the current view
 */
const isDefault = computed(() => {
  if (!userDefaultFilter.value) {
    return false
  }

  return props.filterId == userDefaultFilter.value.id
})
</script>
