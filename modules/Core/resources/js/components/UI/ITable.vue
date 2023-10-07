<template>
  <div
    ref="elRef"
    class="table-responsive"
    :style="{ maxHeight: maxHeight }"
    :class="[
      wrapperClass,
      {
        'table-sticky-header': sticky,
      },
    ]"
  >
    <div
      v-bind="$attrs.style"
      :class="[
        $attrs.class,
        { shadow: shadow },
        {
          'border-x border-b border-neutral-200 dark:border-neutral-800':
            bordered,
        },
      ]"
    >
      <table
        :class="[
          'table-primary',
          { 'table-condensed': condensed, 'border-separate': sticky },
        ]"
        v-bind="tableAttrs"
        :style="{ borderSpacing: sticky ? 0 : undefined }"
      >
        <slot></slot>
      </table>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, useAttrs } from 'vue'

defineProps({
  maxHeight: String,
  condensed: Boolean,
  wrapperClass: [String, Object, Array],
  shadow: { default: true, type: Boolean },
  bordered: Boolean,
  sticky: Boolean,
})

defineOptions({
  inheritAttrs: false,
})

const elRef = ref(null)
const attrs = useAttrs()

const tableAttrs = computed(() => {
  const result = { ...attrs }
  delete result.class
  delete result.style

  return result
})

defineExpose({ $el: elRef })
</script>
