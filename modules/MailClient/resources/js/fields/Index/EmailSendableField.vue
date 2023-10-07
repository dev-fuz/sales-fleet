<template>
  <Compose
    v-if="isComposing"
    ref="composeRef"
    :visible="isComposing"
    :to="to"
    @modal-hidden="handleComposeModalHidden"
  />
  <BaseEmailField
    v-bind="$attrs"
    ref="originalColumn"
    :column="column"
    :row="row"
    :field="field"
    :resource-name="resourceName"
    :resource-id="resourceId"
    @show="handleDropdownShownEvent"
  >
    <template v-if="field.value" #start>
      <span
        v-i-tooltip="
          hasAccountsConfigured
            ? ''
            : $t('mailclient::mail.account.integration_not_configured')
        "
      >
        <IDropdownItem
          href="#"
          :disabled="!hasAccountsConfigured"
          :text="$t('mailclient::mail.create')"
          @click="compose(true)"
        />
      </span>
    </template>
  </BaseEmailField>
</template>

<script setup>
import { computed, nextTick, ref } from 'vue'
import { useStore } from 'vuex'

import BaseEmailField from '~/Core/fields/Index/EmailField.vue'

import Compose from '~/MailClient/views/Emails/ComposeMessage.vue'

const props = defineProps([
  'column',
  'row',
  'field',
  'resourceName',
  'resourceId',
])

defineOptions({
  inheritAttrs: false,
})

const store = useStore()
const isComposing = ref(false)

const composeRef = ref(null)
const originalColumn = ref(null)

const hasAccountsConfigured = computed(
  () => store.getters['emailAccounts/hasConfigured']
)

/**
 * Get the predefined TO property
 */
const to = computed(() => [
  {
    address: props.field.value,
    name: props.row.display_name || props.row.name,
    resourceName: props.resourceName,
    id: props.row.id,
  },
])

function handleComposeModalHidden() {
  setTimeout(() => {
    isComposing.value = false
  }, 300)
}

/**
 * Compose new email
 */
function compose(state = true) {
  isComposing.value = state
  originalColumn.value.dropdownRef.hide()

  nextTick(() => {
    composeRef.value.subjectRef.focus()
  })
}

function handleDropdownShownEvent() {
  // Load the placeholders when the first time dropdown
  // is shown, helps when first time is clicked on the dropdown -> Create Email the
  // placeholders are not loaded as the editor is initialized before the request is finished
  store.dispatch('fields/fetchPlaceholders')

  // We will check if the accounts are not fetched, if not
  // we will dispatch the store fetch function to retrieve the
  // accounts before the dropdown is shown so the Compose.vue won't need to
  // As if we use the ComposeMessage.vue, every row in the table will retireve the accounts
  // and there will be 20+ requests when the table loads
  if (!store.state.emailAccounts.dataFetched) {
    store.dispatch('emailAccounts/fetch')
  }
}
</script>
