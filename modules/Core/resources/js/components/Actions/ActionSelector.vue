<template>
  <component
    :is="type === 'select' ? ActionSelectorSelect : ActionSelectorDropdown"
    v-if="hasActionsAvailable"
    :action-request-params="actionRequestParams"
    :actions="filteredActions"
    :ids="ids"
    :view="view"
    :resource-name="resourceName"
    @run="actionExecutedHandler"
  />
</template>

<script setup>
import { computed } from 'vue'
import reject from 'lodash/reject'

import ActionSelectorDropdown from './ActionSelectorDropdown.vue'
import ActionSelectorSelect from './ActionSelectorSelect.vue'
import defaultProps from './props'

const emit = defineEmits(['run'])

const props = defineProps({
  ...defaultProps,
  type: { required: true, type: String },
})

const filteredActions = computed(() => {
  return reject(props.actions, action => {
    if (props.view === 'update' && action.hideOnUpdate === true) {
      return true
    } else if (props.view === 'index' && action.hideOnIndex === true) {
      return true
    }

    return false
  })
})

const hasActionsAvailable = computed(() => filteredActions.value.length > 0)

function actionExecutedHandler(response) {
  emit('run', response)
}
</script>
