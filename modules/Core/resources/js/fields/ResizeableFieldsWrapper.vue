<template>
  <div
    ref="wrapperRef"
    :class="[
      { 'resize-y': isResizeable },
      !fieldsHeight && collapsed ? initialHeightClass : '',
    ]"
    :style="{
      height: fieldsHeight && collapsed ? `${fieldsHeight}px` : null,
    }"
  >
    <slot></slot>
  </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import elementResizeEvent from 'element-resize-event'
import { unbind as unbindElementResizeEvent } from 'element-resize-event'
import debounce from 'lodash/debounce'

import { useGate } from '~/Core/composables/useGate'

import { useResourceable } from '../composables/useResourceable'

const props = defineProps({
  disabled: Boolean,
  resourceName: String,
  initialHeightClass: String,
  collapsed: Boolean,
})

const { gate } = useGate()
const { resourceSingularName } = useResourceable(props.resourceName)

const wrapperRef = ref(null)

const fieldsHeight = ref(
  Innoclapps.config(`${resourceSingularName.value}_fields_height`)
)

const isResizeable = computed(
  () => !props.disabled && props.collapsed && gate.isSuperAdmin()
)

const updateResourceFieldsHeight = debounce(async function () {
  if (!props.collapsed) {
    return
  }

  await Innoclapps.request().post('/settings', {
    [resourceSingularName.value + '_fields_height']:
      wrapperRef.value.offsetHeight,
  })

  fieldsHeight.value = wrapperRef.value.offsetHeight
}, 500)

function createResizableEvent() {
  elementResizeEvent(wrapperRef.value, updateResourceFieldsHeight)
}

function destroyResizableEvent() {
  if (isResizeable.value) {
    unbindElementResizeEvent(wrapperRef.value)
  }
}

function prepareComponent() {
  if (isResizeable.value) {
    nextTick(createResizableEvent)
  }
}

onMounted(prepareComponent)

onBeforeUnmount(destroyResizableEvent)

defineExpose({
  $el: wrapperRef,
})
</script>
