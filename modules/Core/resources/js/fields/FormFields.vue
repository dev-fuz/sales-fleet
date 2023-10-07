<template>
  <div ref="wrapperRef" v-bind="$attrs">
    <div class="grid grid-cols-12 gap-x-4">
      <div
        v-for="field in iterableFields"
        :key="field.attribute"
        :class="
          field.width === 'half' ? 'col-span-12 sm:col-span-6' : 'col-span-12'
        "
      >
        <slot :name="`before-${field.attribute}-field`" :field="field"></slot>

        <div
          :class="
            field.displayNone || field.hidden || (collapsed && field.collapsed)
              ? 'hidden'
              : ''
          "
        >
          <component
            :is="field.formComponent"
            :field="field"
            :resource-id="resourceId"
            :resource-name="resourceName"
            :is-floating="isFloating"
            :form-id="formId"
          />
        </div>

        <slot :name="`after-${field.attribute}-field`" :field="field"></slot>
      </div>
    </div>
  </div>
  <slot :fields="iterableFields" name="after"></slot>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import castArray from 'lodash/castArray'

import Fields from '~/Core/fields/Fields'

const props = defineProps({
  fields: { required: true, type: [Array, Fields] },
  formId: { required: true, type: String },

  collapsed: Boolean,

  resourceId: [String, Number],
  resourceName: String,

  except: [Array, String],
  only: [Array, String],

  isFloating: Boolean,
  focusFirst: Boolean,
})

defineOptions({ inheritAttrs: false })

const wrapperRef = ref(null)

let timeoutClear = null

const onlyFields = computed(() => (props.only ? castArray(props.only) : []))

const exceptFields = computed(() =>
  props.except ? castArray(props.except) : []
)

const iterableFields = computed(() => {
  if (!props.fields) {
    return []
  }

  let fields =
    props.fields instanceof Fields ? props.fields.all() : props.fields

  if (props.only) {
    return fields.filter(
      field => onlyFields.value.indexOf(field.attribute) > -1
    )
  } else if (props.except) {
    return fields.filter(
      field => exceptFields.value.indexOf(field.attribute) === -1
    )
  }

  return fields
})

function focusToFirstFocusableElement() {
  const focusAbleInputs = [
    'date',
    'datetime-local',
    'email',
    'file',
    'month',
    'number',
    'password',
    'range',
    'search',
    'tel',
    'text',
    'time',
    'url',
    'week',
  ]

  const input = wrapperRef.value.querySelector('div:first-child input')
  const textarea = wrapperRef.value.querySelector('div:first-child textarea')

  if (input && focusAbleInputs.indexOf(input.getAttribute('type')) > -1) {
    input.focus()
  } else if (textarea) {
    textarea.focus()
  }
}

if (props.focusFirst) {
  onMounted(() => {
    timeoutClear = setTimeout(focusToFirstFocusableElement, 600)
  })
  onBeforeUnmount(() => {
    timeoutClear && clearTimeout(timeoutClear)
  })
}
</script>
