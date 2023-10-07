<template>
  <th ref="elRef" class="group text-left">
    <a
      v-if="isSortable"
      href="#"
      class="inline-flex w-full items-center hover:text-neutral-700 focus:outline-none dark:hover:text-neutral-400"
      @click.prevent="toggleSortable"
    >
      {{ heading }}
      <Icon
        :icon="isSortedAscending ? 'ChevronUp' : 'ChevronDown'"
        :class="[
          'ml-1.5 h-3 w-3',
          isTableOrderedByCurrentField
            ? 'opacity-100'
            : 'opacity-0 group-hover:opacity-100',
        ]"
      />
    </a>
    <span v-else v-text="heading" />
  </th>
</template>

<script setup>
import { computed, ref } from 'vue'

const emit = defineEmits(['update:ctx'])

const props = defineProps({
  isSortable: Boolean,
  heading: String,
  headingKey: { type: String, required: true },
  ctx: { type: Object, required: true },
})

const elRef = ref(null)

/**
 * Check whether the table is ordered by current field
 */
const isTableOrderedByCurrentField = computed(() => {
  return props.ctx.sortBy === props.headingKey
})

/**
 * Check whether current field is sorted ascending
 */
const isSortedAscending = computed(() => {
  return props.ctx.direction === 'asc' && isTableOrderedByCurrentField.value
})

/**
 * Toggle sortable column
 */
function toggleSortable() {
  const ctx = {}

  if (isTableOrderedByCurrentField.value) {
    ctx.sortBy = props.headingKey
    ctx.direction = props.ctx.direction === 'desc' ? 'asc' : 'desc'
  } else {
    ctx.sortBy = props.headingKey
    ctx.direction = 'desc'
  }

  emit('update:ctx', Object.assign({}, props.ctx, ctx))
}

defineExpose({ $el: elRef })
</script>
