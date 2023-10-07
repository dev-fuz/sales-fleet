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
import SettingsFields from '~/Core/views/Settings/Fields/SettingsFields.vue'
import SettingsRecaptcha from '~/Core/views/Settings/Security/SettingsRecaptcha.vue'
import SettingsSecurity from '~/Core/views/Settings/Security/SettingsSecurity.vue'
// Settings general routes
import SettingsIndex from '~/Core/views/Settings/Settings.vue'
import SettingsGeneral from '~/Core/views/Settings/SettingsGeneral.vue'
import SettingsMailableTemplates from '~/Core/views/Settings/SettingsMailableTemplates.vue'
import SettingsSystemInfo from '~/Core/views/Settings/System/SettingsSystemInfo.vue'
import SettingsSystemLogs from '~/Core/views/Settings/System/SettingsSystemLogs.vue'
import SettingsTools from '~/Core/views/Settings/System/SettingsTools.vue'
import SettingsUpdate from '~/Core/views/Settings/System/SettingsUpdate.vue'
import SettingsWorkflows from '~/Core/views/Workflows/WorkflowsList.vue'

import DashboardEdit from './views/Dashboard/DashboardEdit.vue'
// General routes
import Dashboard from './views/Dashboard/DashboardIndex.vue'
import Error403 from './views/Error403.vue'
import Error404 from './views/Error404.vue'
import NotificationsIndex from './views/Notifications/NotificationsIndex.vue'
import OAuthAccounts from './views/OAuth/OAuthAccounts.vue'
import ResourceImport from './views/Resources/ImportResource.vue'
import TrashedResourceRecords from './views/Resources/TrashedResourceRecords.vue'
import SettingsGoogle from './views/Settings/Integrations/SettingsGoogle.vue'
// Settings integration routes
import SettingsMicrosoft from './views/Settings/Integrations/SettingsMicrosoft.vue'
import SettingsPusher from './views/Settings/Integrations/SettingsPusher.vue'
import SettingsZapier from './views/Settings/Integrations/SettingsZapier.vue'

const routes = [
  {
    alias: '/',
    path: '/dashboard',
    component: Dashboard,
    meta: {
      title: i18n.t('core::dashboard.insights'),
      scrollToTop: false,
    },
  },
  {
    path: '/',
    name: 'dashboard',
    component: Dashboard,
    meta: {
      title: i18n.t('core::dashboard.insights'),
      scrollToTop: false,
    },
  },
  {
    path: '/dashboard/:id/edit',
    name: 'edit-dashboard',
    component: DashboardEdit,
  },
  {
    path: '/notifications',
    name: 'notifications',
    component: NotificationsIndex,
    meta: {
      title: i18n.t('core::notifications.your'),
    },
  },
  {
    path: '/import/:resourceName',
    name: 'import-resource',
    meta: {
      title: i18n.t('core::import.import_records'),
    },
    component: ResourceImport,
  },
  {
    path: '/oauth/accounts',
    name: 'oauth-accounts',
    component: OAuthAccounts,
    meta: {
      title: i18n.t('core::oauth.connected_accounts'),
    },
  },
  {
    path: '/trashed/:resourceName',
    name: 'trashed-resource-records',
    component: TrashedResourceRecords,
    meta: {
      title: i18n.t('core::app.soft_deletes.trashed_records'),
    },
  },
  {
    name: '404',
    path: '/404',
    meta: {
      title: '404',
    },
    component: Error404,
  },
  {
    name: '403',
    path: '/403',
    meta: {
      title: '403',
    },
    component: Error403,
  },
  {
    name: 'not-found',
    path: '/:pathMatch(.*)*',
    component: Error404,
  },

  // Settings routes
  {
    path: '/settings',
    name: 'settings',
    component: SettingsIndex,
    meta: {
      title: i18n.t('core::settings.settings'),
      gate: 'is-super-admin',
    },
    children: [
      {
        path: 'general',
        component: SettingsGeneral,
        name: 'settings-general',
        meta: { title: i18n.t('core::settings.general_settings') },
        alias: '/settings',
      },
      {
        path: 'fields/:resourceName',
        name: 'resource-fields',
        component: SettingsFields,
      },
      // Integration routes
      {
        path: 'integrations/microsoft',
        component: SettingsMicrosoft,
        name: 'settings-integrations-microsoft',
        meta: {
          title: 'Microsoft',
        },
      },
      {
        path: 'integrations/google',
        component: SettingsGoogle,
        name: 'settings-integrations-google',
        meta: {
          title: 'Google',
        },
      },
      {
        path: 'integrations/pusher',
        component: SettingsPusher,
        name: 'settings-integrations-pusher',
        meta: {
          title: 'Pusher',
        },
      },
      {
        path: 'integrations/zapier',
        component: SettingsZapier,
        name: 'settings-integrations-zapier',
        meta: {
          title: 'Zapier',
        },
      },
      {
        path: '/settings/workflows',
        component: SettingsWorkflows,
        meta: { title: i18n.t('core::workflow.workflows') },
      },
      {
        path: '/settings/mailables',
        component: SettingsMailableTemplates,
        meta: { title: i18n.t('core::mail_template.mail_templates') },
      },
      {
        path: '/settings/update',
        component: SettingsUpdate,
        name: 'update',
        meta: { title: i18n.t('core::update.system') },
      },
      {
        path: '/settings/tools',
        component: SettingsTools,
        meta: { title: i18n.t('core::settings.tools.tools') },
      },
      {
        path: '/settings/info',
        component: SettingsSystemInfo,
        meta: { title: i18n.t('core::app.system_info') },
      },
      {
        path: '/settings/logs',
        component: SettingsSystemLogs,
        meta: { title: 'Logs' },
      },
      {
        path: '/settings/security',
        component: SettingsSecurity,
        meta: { title: i18n.t('core::settings.security.security') },
      },
      {
        path: '/settings/recaptcha',
        component: SettingsRecaptcha,
        meta: { title: i18n.t('core::settings.recaptcha.recaptcha') },
      },
    ],
  },
]

export default routes
