<template>
  <div class="relative">
    <!-- eslint-disable-next-line vue/no-v-html -->
    <div v-if="!lightbox" v-bind="$attrs" v-html="visibleText" />
    <HtmlableLightbox v-else :html="visibleText" v-bind="$attrs" />
    <div v-show="hasTextToCollapse">
      <slot name="action" :collapsed="localIsCollapsed" :toggle="toggle">
        <div
          v-show="localIsCollapsed"
          class="absolute bottom-0 h-1/2 w-full cursor-pointer bg-gradient-to-t from-white to-transparent dark:from-neutral-900"
          @click="toggle"
        />

        <a
          v-show="!localIsCollapsed"
          v-t="'core::app.show_less'"
          href="#"
          class="link mt-2 block text-sm !no-underline"
          @click.prevent="toggle"
        />
      </slot>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, ref, watch } from 'vue'
import truncate from 'truncate-html'

import HtmlableLightbox from './Lightbox/HtmlableLightbox.vue'

const emit = defineEmits(['update:collapsed', 'hasTextToCollapse'])

const props = defineProps({
  text: { type: String, required: true },
  length: { default: 150, type: Number },
  lightbox: Boolean,
  collapsed: { type: Boolean, default: true },
})

defineOptions({
  inheritAttrs: false,
})

const localIsCollapsed = ref(props.collapsed)
const truncatedText = ref('')

const hasTextToCollapse = computed(() => props.text.length >= props.length)

const visibleText = computed(() =>
  localIsCollapsed.value ? truncatedText.value : props.text
)

watch(
  () => props.collapsed,
  newVal => (localIsCollapsed.value = newVal)
)

watch(
  () => props.text,
  newVal => {
    truncatedText.value = truncate(newVal, props.length)
    nextTick(() => emit('hasTextToCollapse', hasTextToCollapse.value))
  },
  { immediate: true }
)

function toggle() {
  localIsCollapsed.value = !localIsCollapsed.value
  emit('update:collapsed', localIsCollapsed.value)
}
</script>
