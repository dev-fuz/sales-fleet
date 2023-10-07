<template>
  <ISlideover
    :title="$t('mailclient::mail.account.create')"
    visible
    static-backdrop
    form
    @submit="connect"
    @keydown="form.onKeydown($event)"
    @hidden="$router.back()"
  >
    <EmailAccountsFormFields
      ref="formFieldsRef"
      :type="$route.query.type"
      :test-connection-form="testConnectionForm"
      :form="form"
      @ready="setInitialSyncFromDefault"
      @submit="connect"
    />
    <template #modal-ok>
      <IButton
        v-if="isImapAccount && requirements.imap"
        :loading="testConnectionInProgress"
        :disabled="testConnectionInProgress"
        :text="
          $t('mailclient::mail.account.test_connection_and_retrieve_folders')
        "
        @click="performImapConnectionTest"
      />
      <IButton
        type="submit"
        variant="primary"
        :disabled="isSubmitDisabled"
        :text="$t('mailclient::mail.account.connect')"
        :loading="form.busy"
      />
    </template>
  </ISlideover>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute, useRouter } from 'vue-router'
import { useStore } from 'vuex'

import { useApp } from '~/Core/composables/useApp'
import { useForm } from '~/Core/composables/useForm'

import { useTestImapConnection } from '../../composables/useTestImapConnection'

import EmailAccountsFormFields from './EmailAccountsFormFields.vue'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const store = useStore()

const { currentUser } = useApp()

const { testConnectionForm, testConnection, testConnectionInProgress } =
  useTestImapConnection()

const { form } = useForm()
const requirements = Innoclapps.config('requirements')
const formFieldsRef = ref(null)

const isSubmitDisabled = computed(
  () =>
    (!isImapConnectionSuccessful.value && isImapAccount.value) ||
    form.busy ||
    accountConfigError.value !== null ||
    !form.connection_type
)

const accountConfigError = computed(
  () => store.state.emailAccounts.accountConfigError
)

const isImapConnectionSuccessful = computed(
  () => store.state.emailAccounts.formConnectionState
)

const isImapAccount = computed(() => form.connection_type === 'Imap')

function createOAuthRedirectUrl() {
  return (
    store.getters['emailAccounts/OAuthConnectUrl'](
      form.connection_type,
      route.query.type
    ) +
    '?period=' +
    form.initial_sync_from
  )
}

function setInitialSyncFromDefault() {
  form.set('initial_sync_from', formFieldsRef.value.initialSyncOptions[1].value)
}

function connect() {
  if (!isImapAccount.value) {
    window.location.href = createOAuthRedirectUrl()

    return
  }

  store.dispatch('emailAccounts/store', form).then(() => {
    Innoclapps.success(t('mailclient::mail.account.created'))
    router.push({ name: 'email-accounts-index' })
  })
}

function performImapConnectionTest() {
  testConnection(form).then(data => {
    form.requires_auth = false
    form.folders = data.folders
  })
}

function prepareComponent() {
  let formObject = {
    connection_type: null,
    email: null,
    password: null,
    username: null,
    imap_server: null,
    imap_port: 993,
    imap_encryption: 'ssl',
    smtp_server: null,
    smtp_port: 465,
    smtp_encryption: 'ssl',
    validate_cert: 1,
    folders: [],
    create_contact: false,
    from_name_header: Innoclapps.config('mail.accounts.from_name'),
  }

  if (route.query.type === 'personal') {
    // Indicates that the account is shared
    formObject['user_id'] = currentUser.value.id
  } else if (route.query.type !== 'shared') {
    // We need indicator whether the account is shared or personal
    // if not provided e.q. route accessed directly, show 404
    router.push({
      name: '404',
    })
  }

  form.set(formObject)
}

prepareComponent()
</script>
