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
import { computed } from 'vue'
import { useStore } from 'vuex'
import get from 'lodash/get'

export function useApp() {
  const store = useStore()

  const locales = computed(() => Innoclapps.config('locales'))

  const currentUser = computed(() => {
    return store.getters['users/current']
  })

  const users = computed(() => {
    return store.state.users.collection
  })

  function findUserById(id) {
    return store.getters['users/getById'](id)
  }

  function setting(name) {
    return get(store.state.settings, name)
  }

  function resetStoreState() {
    store.commit('table/RESET_SETTINGS')
    store.commit('fields/RESET')
  }

  /**
   * Checks whether a Microsoft application is configured
   * The function uses the Innoclapps.config because it will check
   * whether Microsoft application credentials are configured in .env file
   * or via settings
   */
  function isMicrosoftGraphConfigured() {
    return Boolean(Innoclapps.config('microsoft.client_id'))
  }

  /**
   * Checks whether a Google project is configured
   * The function uses the Innoclapps.config because it will check
   * whether Google application credentials are configured in .env file
   * or via settings
   */
  function isGoogleApiConfigured() {
    return Boolean(Innoclapps.config('google.client_id'))
  }

  return {
    locales,
    currentUser,
    users,

    findUserById,
    setting,
    resetStoreState,
    isGoogleApiConfigured,
    isMicrosoftGraphConfigured,
  }
}
