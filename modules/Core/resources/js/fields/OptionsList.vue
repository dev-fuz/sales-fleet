<template>
  <div
    v-if="totalOptions > 0"
    :class="eachOnNewLine ? 'space-y-0.5' : 'space-x-2'"
  >
    <component
      :is="onClickRedirectTo ? 'router-link' : 'span'"
      v-for="(option, index) in options"
      :key="index"
      :class="[
        onClickRedirectTo && !displayAsPills ? 'link' : undefined,
        eachOnNewLine ? 'block' : '',
      ]"
      :to="
        onClickRedirectTo
          ? onClickRedirectTo.replace('{id}', option.id)
          : undefined
      "
    >
      <component
        :is="option.swatch_color && displayAsPills ? TextBackground : 'span'"
        :color="
          option.swatch_color && displayAsPills
            ? option.swatch_color
            : undefined
        "
        :class="[
          'inline-flex items-center justify-center space-x-1.5',
          { 'rounded-full px-2.5 text-sm/5 font-normal': displayAsPills },
          !option.swatch_color && displayAsPills
            ? 'bg-neutral-200 dark:bg-neutral-600'
            : option.swatch_color && displayAsPills
            ? 'dark:!text-white '
            : '',
        ]"
      >
        <Icon
          v-if="option.icon"
          :icon="option.icon"
          class="h-4 w-4 text-current"
        />

        <span>
          {{ option[labelKey] }}
        </span>
      </component>
    </component>
  </div>
  <span v-else>&mdash;</span>
</template>

<script setup>
import { computed } from 'vue'
import castArray from 'lodash/castArray'

import TextBackground from '~/Core/components/TextBackground.vue'

const props = defineProps([
  'resourceName',
  'resourceId',
  'value',
  'displayAsPills',
  'eachOnNewLine',
  'onClickRedirectTo',
  'labelKey',
])

const options = computed(() => (props.value ? castArray(props.value) : []))
const totalOptions = computed(() => options.value.length)
</script>
