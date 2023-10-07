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

import SettingsProducts from './components/SettingsProducts.vue'
import DetailBillableAmountField from './fields/Detail/BillableAmountField.vue'
import IndexBillableAmountField from './fields/Index/BillableAmountField.vue'
import routes from './routes'

if (window.Innoclapps) {
  Innoclapps.booting(function (Vue, router) {
    Vue.component('DetailBillableAmountField', DetailBillableAmountField)
    Vue.component('IndexBillableAmountField', IndexBillableAmountField)

    // Routes
    routes.forEach(route => router.addRoute(route))

    router.addRoute('settings', {
      path: 'products',
      component: SettingsProducts,
      name: 'settings-products',
      meta: { title: i18n.t('billable::product.products') },
    })
  })
}
