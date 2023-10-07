<template>
  <div
    :class="[
      'group relative rounded-md px-3 py-2',
      field.isRequired && !fieldHasValue
        ? 'bg-danger-100 hover:bg-danger-200'
        : 'hover:bg-neutral-50 dark:hover:bg-neutral-800',
    ]"
  >
    <FieldInlineEdit
      class="hidden group-hover:block"
      :field="field"
      :resource="resource"
      :resource-name="resourceName"
      :resource-id="resourceId"
      :edit-action="editAction"
      :is-floating="isFloating"
      @updated="$emit('updated', $event)"
    >
      <template v-for="(_, name) in $slots" #[name]="slotData">
        <slot :name="name" v-bind="slotData" />
      </template>
    </FieldInlineEdit>

    <div class="grid grid-flow-row-dense grid-cols-3 gap-4">
      <div
        class="col-span-1 justify-self-end text-right text-sm text-neutral-500 dark:text-neutral-300"
        v-text="field.label"
      />
      <div
        class="col-span-2 break-words text-sm text-neutral-800 dark:text-white"
      >
        <slot name="field" :has-value="fieldHasValue" :value="field.value">
          <p v-if="fieldHasValue">
            {{ field.value }}
          </p>
        </slot>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

import { isValueEmpty } from '@/utils'

defineEmits(['updated'])

const props = defineProps([
  'field',
  'resource',
  'resourceName',
  'resourceId',
  'isFloating',
  'editAction',
])

const fieldHasValue = computed(() => !isValueEmpty(props.field.value))
</script>
