<template>
  <div class="flex flex-wrap space-x-1.5">
    <button
      v-for="icon in icons"
      :key="icon[valueField]"
      v-i-tooltip="icon.tooltip"
      type="button"
      :disabled="disabled"
      :class="[
        'flex h-9 w-10 items-center justify-center rounded-md border border-neutral-300 p-1 text-neutral-800 dark:border-neutral-500  dark:text-neutral-200',
        selected === icon[valueField]
          ? 'bg-neutral-200 hover:bg-neutral-200 dark:bg-neutral-800'
          : 'bg-white hover:bg-neutral-50 dark:bg-neutral-700 dark:hover:bg-neutral-600',
      ]"
      @click="pickIcon(icon[valueField])"
    >
      <Icon :icon="icon.icon" class="h-5 w-5" />
    </button>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const emit = defineEmits(['update:modelValue', 'change'])

const props = defineProps({
  modelValue: [String, Number],
  label: String,
  disabled: Boolean,
  valueField: { default: 'icon' },
  icons: {
    type: Array,
    default: function () {
      return [
        {
          icon: 'Mail',
          tooltip: null,
        },
        {
          icon: 'PencilAlt',
          tooltip: null,
        },
        {
          icon: 'OfficeBuilding',
          tooltip: null,
        },
        {
          icon: 'Phone',
          tooltip: null,
        },
        {
          icon: 'Calendar',
          tooltip: null,
        },
        {
          icon: 'Collection',
          tooltip: null,
        },
        {
          icon: 'Bell',
          tooltip: null,
        },
        {
          icon: 'AtSymbol',
          tooltip: null,
        },
        {
          icon: 'Briefcase',
          tooltip: null,
        },
        {
          icon: 'Chat',
          tooltip: null,
        },
        {
          icon: 'CheckCircle',
          tooltip: null,
        },
        {
          icon: 'BookOpen',
          tooltip: null,
        },
        {
          icon: 'Camera',
          tooltip: null,
        },
        {
          icon: 'Truck',
          tooltip: null,
        },
        {
          icon: 'Folder',
          tooltip: null,
        },
        {
          icon: 'DeviceMobile',
          tooltip: null,
        },
        {
          icon: 'Users',
          tooltip: null,
        },
        {
          icon: 'CreditCard',
          tooltip: null,
        },
        {
          icon: 'Clock',
          tooltip: null,
        },
        {
          icon: 'ShieldExclamation',
          tooltip: null,
        },
        {
          icon: 'WrenchScrewdriver',
          tooltip: null,
        },
      ]
    },
  },
})

const selected = ref(props.modelValue)

function pickIcon(icon) {
  selected.value = icon
  emit('update:modelValue', icon)
  emit('change', icon)
}

watch(
  () => props.modelValue,
  newVal => {
    selected.value = newVal
    emit('update:modelValue', selected.value)
    emit('change', selected.value)
  }
)
</script>
