<template>
  <th
    scope="col"
    :class="[
      'relative whitespace-nowrap',
      column.thClass,
      {
        'table-primary-column': column.primary,
        'table-actions-column': column.attribute === 'actions',
        'text-left': isLeftAligned,
        'text-center': column.centered,
      },
    ]"
    :style="{
      width: width,
      'min-width': column.minWidth,
    }"
  >
    <div
      v-if="column.attribute === 'actions'"
      v-once
      class="absolute -left-px top-0 h-full w-px bg-neutral-200 dark:bg-neutral-800/40"
    />

    <div
      v-else-if="column.primary"
      v-once
      style="box-shadow: 0 1px 15px rgba(0, 0, 0, 0.25)"
      class="absolute -right-px top-0 h-full w-px bg-neutral-200 dark:bg-neutral-800/40"
    />

    <div
      v-if="isSelectable"
      :class="{
        'left-12': !condensed,
        'left-8': condensed,
      }"
      class="absolute top-0 h-full w-px bg-neutral-200 dark:bg-neutral-800"
    />

    <div class="flex items-center">
      <IFormCheckbox
        v-if="isSelectable"
        :class="{ '-ml-2': !condensed }"
        :checked="allRowsSelected"
        @change="$emit('toggle-select-all')"
      />

      <div
        :class="[
          'grow',
          {
            'ml-6': isSelectable && !condensed,
            'ml-2': isSelectable && condensed,
          },
        ]"
      >
        <a
          v-if="column.sortable"
          class="group inline-flex hover:text-neutral-700 focus:outline-none dark:hover:text-neutral-400"
          href="#"
          @click.prevent="$emit('sort-requested', column.attribute)"
        >
          <slot>
            {{ column.label }}
          </slot>
          <span
            v-show="isOrdered"
            class="ml-2 flex-none rounded bg-neutral-200 text-sm text-neutral-900 group-hover:bg-neutral-300"
          >
            <Icon
              :icon="isSortedAscending ? 'ChevronUp' : 'ChevronDown'"
              class="h-4 w-4"
            />
          </span>
          <span
            v-show="!isOrdered"
            class="invisible ml-2 flex-none rounded text-neutral-400 group-hover:visible group-focus:visible"
          >
            <Icon
              v-once
              icon="ChevronDown"
              class="invisible h-4 w-4 flex-none rounded text-neutral-400 group-hover:visible group-focus:visible"
            />
          </span>
        </a>
        <span v-else>
          <slot>
            {{ column.label }}
          </slot>
        </span>
      </div>
    </div>
  </th>
</template>

<script setup>
import { computed } from 'vue'

defineEmits(['toggle-select-all', 'sort-requested'])

const props = defineProps({
  column: { type: Object, required: true },
  // Whether the current column is ordered
  isOrdered: Boolean,
  condensed: Boolean,
  isSortedAscending: Boolean,
  resourceName: String,
  isSelectable: Boolean,
  allRowsSelected: Boolean,
})

const width = computed(() => props.column.width || 'auto')

const isLeftAligned = computed(
  () =>
    !props.column.thClass ||
    !['text-center', 'text-left', 'text-right'].some(alignmentClass =>
      props.column.thClass.includes(alignmentClass)
    )
)
</script>
