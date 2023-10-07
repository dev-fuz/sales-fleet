<template>
  <ICustomSelect
    v-model="action"
    :searchable="false"
    :clearable="false"
    :loading="actionIsRunning"
    :options="actions"
    :placeholder="$t('core::actions.select')"
    label="name"
    @option:selected="runWhenSelected ? run() : $emit('selected', $event)"
  />
</template>

<script setup>
import { computed, toRef } from 'vue'
import castArray from 'lodash/castArray'

import { useAction } from '../../composables/useAction'

import defaultProps from './props'

const emit = defineEmits(['run', 'selected'])

const props = defineProps({
  ...defaultProps,
  runWhenSelected: { type: Boolean, default: true },
})

const actionIds = computed(() => castArray(props.ids))

const { run, action, actionIsRunning } = useAction(
  actionIds,
  {
    resourceName: toRef(props, 'resourceName'),
    requestParams: toRef(props, 'actionRequestParams'),
  },
  response => emit('run', response)
)

defineExpose({ run })
</script>
