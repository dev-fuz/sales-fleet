<template>
  <DetailFieldsItem
    :field="field"
    :is-floating="isFloating"
    :resource="resource"
    :resource-name="resourceName"
    :resource-id="resourceId"
  >
    <template #field="{ hasValue, value }">
      <ComposeMessage
        v-if="isComposing"
        ref="composeRef"
        :visible="isComposing"
        :resource-name="resourceName"
        :resource-id="resourceId"
        :related-resource="resource"
        :associations="newMessageAdditionalAssociations"
        :to="to"
        @modal-hidden="handleComposeModalHidden"
      />

      <IDropdown
        v-if="hasValue"
        ref="dropdownRef"
        no-caret
        @show="handleDropdownShownEvent"
      >
        <template #toggle="{ toggle }">
          <a
            class="link text-sm"
            :href="'mailto:' + value"
            @click.prevent="toggle"
            v-text="value"
          />
        </template>
        <span
          v-i-tooltip="
            hasAccountsConfigured
              ? ''
              : $t('mailclient::mail.account.integration_not_configured')
          "
        >
          <IDropdownItem
            v-if="!isFloating"
            href="#"
            :disabled="!hasAccountsConfigured"
            :text="$t('mailclient::mail.create')"
            @click="compose(true)"
          />
        </span>
        <IButtonCopy
          :success-message="$t('core::fields.email_copied')"
          :text="value"
          tag="IDropdownItem"
        >
          {{ $t('core::app.copy') }}
        </IButtonCopy>
        <IDropdownItem
          :href="'mailto:' + value"
          :text="$t('core::app.open_in_app')"
        />
      </IDropdown>

      <span v-else>&mdash;</span>
    </template>
  </DetailFieldsItem>
</template>

<script setup>
import { computed, nextTick, ref, toRef } from 'vue'
import { useStore } from 'vuex'

import ComposeMessage from '~/MailClient/views/Emails/ComposeMessage.vue'

import { useMessageAssociations } from '../../composables/useMessageAssociations'

const props = defineProps([
  'resource',
  'resourceName',
  'resourceId',
  'field',
  'isFloating',
])

const store = useStore()
const isComposing = ref(false)

const composeRef = ref(null)
const dropdownRef = ref(null)

const { newMessageAdditionalAssociations } = useMessageAssociations(
  toRef(props, 'resourceName'),
  toRef(props, 'resource')
)

const hasAccountsConfigured = computed(
  () => store.getters['emailAccounts/hasConfigured']
)

const to = computed(() => [
  {
    address: props.field.value,
    name: props.resource.display_name,
    resourceName: props.resourceName,
    id: props.resourceId,
  },
])

function handleComposeModalHidden() {
  setTimeout(() => {
    isComposing.value = false
  }, 300)
}

function compose(state = true) {
  dropdownRef.value.hide()
  isComposing.value = state
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
