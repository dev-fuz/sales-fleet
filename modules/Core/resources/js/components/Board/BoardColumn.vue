<template>
  <div
    class="inline-flex h-full w-80 flex-col overflow-y-hidden rounded-lg border border-neutral-300/40 bg-neutral-200/40 align-top shadow dark:border-neutral-700 dark:bg-neutral-900"
  >
    <div class="px-3 py-2.5">
      <div class="flex items-center">
        <slot name="columnHeader">
          <h5
            class="mr-auto truncate text-sm font-medium text-neutral-800 dark:text-neutral-100"
            v-text="name"
          />
          <div>
            <slot name="topRight"></slot>
          </div>
        </slot>
      </div>
      <slot name="afterColumnHeader"></slot>
    </div>
    <div
      :id="'boardColumn' + columnId"
      class="h-auto overflow-y-auto overflow-x-hidden"
    >
      <draggable
        :data-column="columnId"
        :model-value="modelValue"
        :move="onMoveCallback"
        :item-key="item => item.id"
        :empty-insert-threshold="100"
        v-bind="columnCardsDraggableOptions"
        :group="{ name: boardId }"
        @update:model-value="$emit('update:modelValue', $event)"
        @start="onDragStart"
        @end="onDragEnd"
        @change="onChangeEventHandler"
      >
        <template #item="{ element }">
          <div
            class="m-2 overflow-hidden whitespace-normal rounded-md bg-white shadow dark:bg-neutral-800"
          >
            <slot name="card" :card="element">
              <div class="px-4 py-5 sm:p-6">
                {{ element.display_name }}
              </div>
            </slot>
          </div>
        </template>
      </draggable>
    </div>
    <div class="flex items-center p-3"></div>
    <InfinityLoader
      :scroll-element="'#boardColumn' + columnId"
      @handle="infiniteHandler($event)"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import draggable from 'vuedraggable'

import InfinityLoader from '~/Core/components/InfinityLoader.vue'
import { useDraggable } from '~/Core/composables/useDraggable'

const emit = defineEmits([
  'drag-start',
  'drag-end',
  'updated',
  'added',
  'removed',
  'update:modelValue',
])

const props = defineProps({
  name: { required: true, type: String },
  columnId: { required: true, type: Number },
  modelValue: { required: true, type: Array },
  boardId: { required: true, type: String },
  loader: { required: true, type: Function },
  move: Function,
})

const { scrollableDraggableOptions } = useDraggable()

const columnCardsDraggableOptions = computed(() => ({
  ...scrollableDraggableOptions,
  ...{ delay: 5, preventOnFilter: false },
}))

function infiniteHandler(state) {
  props.loader(props.columnId, state)
}

function onDragStart(e) {
  emit('drag-start', e)
}

function onDragEnd(e) {
  emit('drag-end', e)
}

function onMoveCallback(evt, originalEvent) {
  if (props.move && props.move(evt, originalEvent) === false) {
    return false
  }
}

function onChangeEventHandler(e) {
  if (e.removed) {
    emit('removed', {
      columnId: props.columnId,
      event: e,
    })
  }

  if (e.moved) {
    emit('updated', {
      columnId: props.columnId,
      event: e,
    })
  }

  if (e.added) {
    emit('added', {
      columnId: props.columnId,
      event: e,
    })

    emit('updated', {
      columnId: props.columnId,
      event: e,
    })
  }
}
</script>
