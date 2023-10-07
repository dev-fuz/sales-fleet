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
import { useNotification } from '~/Core/composables/useBroadcast'
import { GatePlugin } from '~/Core/gate'
import i18n from '~/Core/i18n'

import UserInvitationAcceptForm from './components/UserInvitationAcceptForm.vue'
import UsersStore from './store/Users'
import UsersPersonalAccessTokens from './views/UsersPersonalAccessTokens.vue'
import UsersProfile from './views/UsersProfile.vue'
import settingsRoutes from './settingsRoutes'

if (window.Innoclapps) {
  Innoclapps.booting((Vue, router, store) => {
    Vue.component('UserInvitationAcceptForm', UserInvitationAcceptForm)

    store.registerModule('users', UsersStore)

    store.commit('users/SET', Innoclapps.config('users') || [])

    Vue.use(GatePlugin, store.getters['users/current'])

    if (config.user_id) {
      useNotification(config.user_id, notification => {
        Innoclapps.request()
          .get(`/notifications/${notification.id}`)
          .then(({ data }) => {
            store.commit('users/NEW_NOTIFICATION', data)
          })
      })
    }

    settingsRoutes.forEach(route => router.addRoute('settings', route))

    router.addRoute({
      path: '/profile',
      name: 'profile',
      component: UsersProfile,
      meta: {
        title: i18n.t('users::profile.profile'),
      },
    })

    router.addRoute({
      path: '/personal-access-tokens',
      name: 'personal-access-tokens',
      component: UsersPersonalAccessTokens,
      meta: {
        title: i18n.t('core::api.personal_access_tokens'),
        gate: 'access-api',
      },
    })
  })
}
