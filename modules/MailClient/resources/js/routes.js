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
import i18n from '~/Core/i18n'

import EmailAccountsCreate from './views/Accounts/EmailAccountsCreate.vue'
import EmailAccountsEdit from './views/Accounts/EmailAccountsEdit.vue'
import EmailAccountsIndex from './views/Accounts/EmailAccountsIndex.vue'
import Inbox from './views/Inbox/InboxMessages.vue'
import InboxMessage from './views/Inbox/Messages/InboxMessage.vue'
import InboxMessages from './views/Inbox/Messages/InboxMessagesTable.vue'

export default [
  {
    path: '/inbox',
    name: 'inbox',
    component: Inbox,
    meta: {
      title: i18n.t('mailclient::inbox.inbox'),
    },
    children: [
      {
        path: ':account_id/folder/:folder_id/messages',
        components: {
          messages: InboxMessages,
        },
        name: 'inbox-messages',
        meta: {
          title: i18n.t('mailclient::inbox.inbox'),
        },
      },
      {
        path: ':account_id/folder/:folder_id/messages/:id',
        components: {
          message: InboxMessage,
        },
        name: 'inbox-message',
        meta: {
          scrollToTop: false,
        },
      },
    ],
  },
  {
    path: '/mail/accounts',
    name: 'email-accounts-index',
    component: EmailAccountsIndex,
    meta: {
      title: i18n.t('mailclient::mail.account.accounts'),
    },
    children: [
      {
        path: 'create',
        name: 'create-email-account',
        component: EmailAccountsCreate,
        meta: { title: i18n.t('mailclient::mail.account.create') },
      },
      {
        path: ':id/edit',
        name: 'edit-email-account',
        component: EmailAccountsEdit,
      },
    ],
  },
]
