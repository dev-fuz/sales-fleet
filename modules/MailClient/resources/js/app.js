/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.3.1
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2023 KONKORD DIGITAL
 */
import { usePrivateChannel } from '~/Core/composables/useBroadcast'

import RecordTabTimelineEmail from './components/RecordTabTimelineEmail.vue'
import DetailEmailSendableField from './fields/Detail/EmailSendableField.vue'
import FormMailEditorField from './fields/Form/MailEditorField.vue'
import IndexEmailSendableField from './fields/Index/EmailSendableField.vue'
import EmailAccountsStore from './store/EmailAccounts'
import EmailsTab from './views/Emails/RecordTabEmails.vue'
import EmailsTabPanel from './views/Emails/RecordTabEmailsPanel.vue'
import routes from './routes'

if (window.Innoclapps) {
  Innoclapps.booting((Vue, router, store) => {
    Vue.component('RecordTabTimelineEmail', RecordTabTimelineEmail)

    // Fields
    Vue.component('FormMailEditorField', FormMailEditorField)
    Vue.component('DetailEmailSendableField', DetailEmailSendableField)
    Vue.component('IndexEmailSendableField', IndexEmailSendableField)

    // Tabs
    Vue.component('EmailsTab', EmailsTab)
    Vue.component('EmailsTabPanel', EmailsTabPanel)

    store.registerModule('emailAccounts', EmailAccountsStore)

    routes.forEach(route => router.addRoute(route))

    listenForEmailAccountSync(window.Innoclapps, store)
  })
}

/**
 * Listen when email accounts sync is finished.
 */
function listenForEmailAccountSync(app, store) {
  usePrivateChannel(
    'inbox',
    '.Modules\\MailClient\\Events\\EmailAccountsSyncFinished',
    e => {
      app.$emit('email-accounts-sync-finished', e)

      app
        .request()
        .get('mail/accounts/unread')
        .then(({ data }) =>
          store.dispatch('emailAccounts/updateUnreadCountUI', data)
        )
    }
  )
}
