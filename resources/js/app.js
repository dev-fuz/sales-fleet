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
import { createApp } from 'vue'
import get from 'lodash/get'
import mitt from 'mitt'
import Mousetrap from 'mousetrap'

import router from '@/router'
import store from '@/store'

import registerComponents from '~/Core/components'
import { usePageTitle } from '~/Core/composables/usePageTitle'
import registerDirectives from '~/Core/directives'
import registerFields from '~/Core/fields'
import i18n from '~/Core/i18n'
import Broadcast from '~/Core/services/Broadcast'
import HTTP from '~/Core/services/HTTP'

import '~/Core/element-prototypes'
import '~/Core/plugins'
import '~/Core/app.js'
import '~/Users/app.js'
import '~/Activities/app.js'
import '~/Billable/app.js'
import '~/Brands/app.js'
import '~/Calls/app.js'
import '~/Comments/app.js'
import '~/Contacts/app.js'
import '~/Core/app.js'
import '~/Deals/app.js'
import '~/Documents/app.js'
import '~/MailClient/app.js'
import '~/Notes/app.js'
import '~/Translator/app.js'
import '~/WebForms/app.js'
import '~/ThemeStyle/app.js'

import 'unfonts.css'
import '../css/app.css'

window.CreateApplication = (config, callbacks = []) =>
  new Application(config).booting(callbacks)

export default class Application {
  constructor(config) {
    this.bus = mitt()
    this.appConfig = config
    this.bootingCallbacks = []
    this.axios = HTTP

    this.axios.defaults.baseURL = config.apiURL
  }

  /**
   * Start the application
   *
   * @return {Void}
   */
  start() {
    let self = this

    const data = {
      confirmationDialog: null,
    }

    Mousetrap.init()

    const app = createApp({
      data() {
        return data
      },

      mounted() {
        self.$on('conflict', message => {
          if (message) {
            self.info(message)
          }
        })

        self.$on('error-404', () => {
          router.replace({
            name: '404',
          })
        })

        self.$on('error-403', error => {
          if (error.response.config.url !== '/broadcasting/auth') {
            router.replace({
              name: '403',
              query: { message: error.response.data.message },
            })
          }
        })

        self.$on('error', message => {
          if (message) {
            self.error(message)
          }
        })

        self.$on('too-many-requests', () => {
          self.error(this.$t('core::app.throttle_error'))
        })

        self.$on('token-expired', () => {
          self.error(
            this.$t('core::app.token_expired'),
            {
              action: {
                onClick: () => window.location.reload(),
                text: this.$t('core::app.reload'),
              },
            },
            30000
          )
        })

        self.$on('maintenance-mode', message => {
          self.info(
            message || 'Down for maintenance',
            {
              action: {
                onClick: () => window.location.reload(),
                text: this.$t('core::app.reload'),
              },
            },
            30000
          )
        })
      },
    })

    // Broadcasting
    if (this.appConfig.broadcasting) {
      const broadcaster = new Broadcast(this.appConfig.broadcasting)
      this.broadcaster = broadcaster
    }

    // i18n
    app.use(i18n.instance)

    // strore
    app.use(store)

    // App config
    bootApplicationConfig(this.appConfig)

    // Register component and directives
    registerDirectives(app)
    registerComponents(app)
    registerFields(app)

    // Boot app
    this.boot(app, router)

    const pageTitle = usePageTitle()

    // Handle router
    router.beforeEach((to, from, next) =>
      beforeEachRoute(to, from, next, app, pageTitle)
    )

    app.use(router)

    this.app = app

    const vm = app.mount('#app')

    app.config.globalProperties.$dialog = {
      confirm(options) {
        // https://github.com/tailwindlabs/headlessui/issues/493
        const dialogIsOpen = document.querySelectorAll('.dialog')

        return new Promise((resolve, reject) => {
          vm.$data.confirmationDialog = Object.assign({}, options, {
            injectedInDialog: dialogIsOpen.length > 0,
            resolve: attrs => {
              resolve(attrs)
              vm.$data.confirmationDialog = null
            },
            reject: attrs => {
              reject(attrs)
              vm.$data.confirmationDialog = null
            },
          })
        })
      },
    }
  }

  /**
   * Get the application CSRF token
   *
   * @return {String|null}
   */
  csrfToken() {
    return this.appConfig.csrfToken || null
  }

  /**
   * Register a callback to be called before the application starts
   */
  booting(callback) {
    if (Array.isArray(callback)) {
      callback.forEach(c => this.booting(c))
    } else {
      this.bootingCallbacks.push(callback)
    }

    return this
  }

  /**
   * Execute all of the booting callbacks.
   */
  boot(app, router) {
    this.bootingCallbacks.forEach(callback => callback(app, router, store))
    this.bootingCallbacks = []
  }

  /**
   * Get all of the available resource objects.
   */
  resources() {
    return this.config('resources')
  }

  /**
   * Get the serialized resource object.
   */
  resource(name) {
    return this.config(`resources.${name}`)
  }

  /**
   * Get the given resource name.
   *
   * NOTE: Useful to avoid using plain names in .vue files and always use
   * the name from the serialized resource object.
   */
  resourceName(name) {
    return this.resource(name).name
  }

  /**
   * Get configuration for the given key.
   *
   * @param key string
   */
  config(key) {
    return get(this.appConfig, key)
  }

  /**
   * Helper request function
   * @param  {Object} options
   *
   * @return {Object}
   */
  request(options) {
    if (options !== undefined) {
      return this.axios(options)
    }

    return this.axios
  }

  /**
   * Register global event
   * @param  {mixed} args
   *
   * @return {Void}
   */
  $on(...args) {
    this.bus.on(...args)
  }

  /**
   * Deregister event
   * @param  {mixed} args
   *
   * @return {Void}
   */
  $off(...args) {
    this.bus.off(...args)
  }

  /**
   * Emit global event
   * @param  {mixed} args
   *
   * @return {Void}
   */
  $emit(...args) {
    this.bus.emit(...args)
  }

  /**
   * Show toasted success messages
   *
   * @param {String} message
   * @param {Object} options
   * @param {Number} duration
   *
   * @return {Void}
   */
  success(message, options, duration = 4000) {
    this.app.config.globalProperties.$notify(
      Object.assign({}, options, {
        text: message,
        type: 'success',
        group: 'app',
      }),
      duration
    )
  }

  /**
   * Show toasted info messages
   *
   * @param {String} message
   * @param {Object} options
   * @param {Number} duration
   *
   * @return {Void}
   */
  info(message, options, duration = 4000) {
    this.app.config.globalProperties.$notify(
      Object.assign({}, options, {
        text: message,
        type: 'info',
        group: 'app',
      }),
      duration
    )
  }

  /**
   * Show toasted error messages
   *
   * @param {String} message
   * @param {Object} options
   * @param {Number} duration
   *
   * @return {Void}
   */
  error(message, options, duration = 4000) {
    this.app.config.globalProperties.$notify(
      Object.assign({}, options, {
        text: message,
        type: 'error',
        group: 'app',
      }),
      duration
    )
  }

  /**
   * Add new a keyboard shortcut
   *
   * @return {Void}
   */
  addShortcut(keys, callback) {
    Mousetrap.bind(keys, callback)
  }

  /**
   * Disable keyboard shortcut
   *
   * @return {Void}
   */
  disableShortcut(keys) {
    Mousetrap.unbind(keys)
  }

  /**
   * Get the global dialog instance
   *
   * @return {Object}
   */
  dialog() {
    return this.app.config.globalProperties.$dialog
  }

  /**
   * Get the global modal instance
   *
   * @return {Object}
   */
  modal() {
    return this.app.config.globalProperties.$iModal
  }
}

/**
 * Before each route callback function
 */
function beforeEachRoute(to, from, next, app, pageTitle) {
  // Close sidebar on route change when on mobile
  if (store.state.sidebarOpen) {
    store.commit('SET_SIDEBAR_OPEN', false)
  }

  // Check if it's a gate route, if yes, perform check before each route
  const gateRoute = to.matched.find(match => match.meta.gate)

  if (gateRoute && typeof gateRoute.meta.gate === 'string') {
    if (app.config.globalProperties.$gate.userCant(gateRoute.meta.gate)) {
      next({ path: '/403' })
    }
  }

  // Now when the user is allowed to access the route
  // let's try to configure the page title if set via the route meta options.
  if (to.meta.title) {
    pageTitle.value = to.meta.title
  }

  next()
}

/**
 * Boot the application config
 */
function bootApplicationConfig(config) {
  store.commit('SET_SETTINGS', config.options ?? {})
  store.commit('SET_API_URL', config.apiURL ?? null)
  store.commit('SET_URL', config.url ?? null)
  store.commit('SET_MENU', config.menu ?? [])
}
