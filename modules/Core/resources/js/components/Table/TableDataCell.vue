<template>
  <td v-bind="cellAttributes">
    <div
      :class="{
        flex: isSelectable,
        'mr-5': isCentered && isSortable,
        'whitespace-pre-line': column.newlineable,
      }"
    >
      <div
        v-if="column.attribute === 'actions'"
        v-once
        class="absolute -left-px top-0 h-full w-px bg-neutral-200 dark:bg-neutral-700/70"
      />

      <div
        v-else-if="column.primary"
        v-once
        style="box-shadow: 0 1px 15px rgba(0, 0, 0, 0.25)"
        class="absolute -right-px top-0 h-full w-px bg-neutral-200 dark:bg-neutral-800"
      />

      <IFormCheckbox
        v-if="isSelectable"
        :class="{ '-ml-2': !condensed }"
        :checked="isSelected"
        @click="$emit('selected', row)"
      />

      <div
        v-if="isSelectable"
        :class="{
          'left-12': !condensed,
          'left-8': condensed,
        }"
        class="absolute top-0 h-full w-px bg-neutral-200 dark:bg-neutral-800"
      />

      <component
        :is="column.link ? 'a' : column.route ? 'router-link' : 'div'"
        :class="{
          'ml-6': isSelectable && !condensed,
          'ml-2': isSelectable && condensed,
        }"
        v-bind="linkBindings"
      >
        <slot> </slot>
      </component>
    </div>
  </td>
</template>

<script setup>
import { computed } from 'vue'
import cloneDeep from 'lodash/cloneDeep'
import each from 'lodash/each'
import get from 'lodash/get'
import isObject from 'lodash/isObject'
import isString from 'lodash/isString'

import { isValueEmpty } from '@/utils'

defineEmits(['selected'])

const props = defineProps({
  column: { required: true, type: Object },
  row: { required: true, type: Object },
  condensed: Boolean,
  isCentered: Boolean,
  isSortable: Boolean,
  isSelected: Boolean,
  isSelectable: Boolean,
})

function replaceUrlBindings(url) {
  Object.keys(props.row).forEach(attribute => {
    url = url.replace('{' + attribute + '}', get(props.row, attribute))
  })

  return url
}

const linkBindings = computed(() => {
  if (!props.column.route && !props.column.link) {
    return {}
  }

  let bindings = { class: 'link' }

  const objectToBindings = function (object) {
    each(
      object,
      (value, key) =>
        (object[key] = isString(value) ? replaceUrlBindings(value) : value)
    )
  }

  if (props.column.route) {
    let to = cloneDeep(props.column.route)

    if (isObject(to)) {
      objectToBindings(to.params || {})
      objectToBindings(to.query || {})
    } else {
      to = replaceUrlBindings(to)
    }

    bindings.to = to
  } else {
    bindings.href = replaceUrlBindings(props.column.link)
  }

  return bindings
})

const cellAttributes = computed(() => ({
  style: {
    width: props.column.width,
    'min-width': props.column.minWidth,
  },
  class: [
    props.column.tdClass,
    'whitespace-normal group/td relative',
    props.column.field &&
    props.column.field.isRequired &&
    isValueEmpty(props.row[props.column.attribute])
      ? '!bg-danger-100 hover:!bg-danger-200'
      : props.isSelected
      ? '!bg-neutral-50 dark:!bg-neutral-800'
      : !props.isSelectable
      ? 'group-hover/tr:bg-neutral-50 group-hover/tr:dark:bg-neutral-800'
      : 'group-hover/tr:bg-neutral-100 group-hover/tr:dark:bg-neutral-700',
    {
      'text-center': props.isCentered,
      'table-primary-column': props.column.primary === true,
      'table-actions-column': props.column.attribute === 'actions',
    },
  ],
}))
</script>
