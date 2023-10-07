<template>
  <div
    v-show="computedShow"
    :class="['p-4', colors[variant].bg, { 'rounded-md': rounded }]"
  >
    <div :class="['flex', wrapperClass]">
      <div class="shrink-0">
        <Icon
          :icon="icon || iconMap[variant]"
          :class="['h-5 w-5', colors[variant].icon]"
        />
      </div>
      <div class="ml-3">
        <h3
          v-show="heading"
          :class="['text-sm font-medium', colors[variant].heading]"
          v-text="heading"
        />
        <div
          class="text-sm"
          :class="[colors[variant].text, { 'mt-2': heading }]"
        >
          <slot></slot>
        </div>
      </div>
      <div v-if="dismissible" class="ml-auto pl-3">
        <div class="-mx-1.5 -my-1.5">
          <button
            type="button"
            class="inline-flex p-1.5 text-neutral-500 hover:opacity-50 focus:outline-none"
            @click="dismiss"
          >
            <Icon icon="X" class="h-5 w-5" />
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const emit = defineEmits(['dismissed'])

const props = defineProps({
  heading: String,
  show: { type: Boolean, default: true },
  dismissible: Boolean,
  rounded: { type: Boolean, default: true },
  icon: String,
  wrapperClass: [Array, Object, String],
  variant: {
    default: 'info',
    type: String,
    validator(value) {
      return ['success', 'info', 'warning', 'danger'].includes(value)
    },
  },
})

const colors = {
  warning: {
    bg: 'bg-warning-50',
    text: 'text-warning-700',
    heading: 'text-warning-800',
    icon: 'text-warning-400',
  },
  danger: {
    bg: 'bg-danger-50',
    text: 'text-danger-700',
    heading: 'text-danger-800',
    icon: 'text-danger-400',
  },
  success: {
    bg: 'bg-success-50',
    text: 'text-success-700',
    heading: 'text-success-800',
    icon: 'text-success-400',
  },
  info: {
    bg: 'bg-info-50',
    text: 'text-info-700',
    heading: 'text-info-800',
    icon: 'text-info-400',
  },
}

const iconMap = {
  warning: 'ExclamationTriangle',
  danger: 'XCircle',
  success: 'CheckCircle',
  info: 'InformationCircle',
}

const dismissed = ref(false)

const computedShow = computed(() => (dismissed.value ? false : props.show))

function dismiss() {
  dismissed.value = true
  emit('dismissed')
}
</script>
