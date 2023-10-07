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

import CompanyFloatingModal from './components/CompanyFloatingModal.vue'
import ContactFloatingModal from './components/ContactFloatingModal.vue'
import CreateCompanyModal from './components/CreateCompanyModal.vue'
import CreateContactModal from './components/CreateContactModal.vue'
import SettingsCompanies from './components/SettingsCompanies.vue'
import DetailPhoneField from './fields/Detail/PhoneField.vue'
import FormPhoneField from './fields/Form/PhoneField.vue'
import IndexPhoneField from './fields/Index/PhoneField.vue'
import routes from './routes'

if (window.Innoclapps) {
  Innoclapps.booting(function (Vue, router) {
    Vue.component('CompanyFloatingModal', CompanyFloatingModal)
    Vue.component('ContactFloatingModal', ContactFloatingModal)

    Vue.component('CreateCompanyModal', CreateCompanyModal)
    Vue.component('CreateContactModal', CreateContactModal)

    Vue.component('FormPhoneField', FormPhoneField)
    Vue.component('DetailPhoneField', DetailPhoneField)
    Vue.component('IndexPhoneField', IndexPhoneField)

    // Routes
    routes.forEach(route => router.addRoute(route))

    router.addRoute('settings', {
      path: 'companies',
      component: SettingsCompanies,
      name: 'settings-companies',
      meta: { title: i18n.t('contacts::company.companies') },
    })
  })
}
