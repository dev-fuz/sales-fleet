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

import SettingsBrands from './components/SettingsBrands.vue'
import BrandsCreate from './views/BrandsCreate.vue'
import BrandsEdit from './views/BrandsEdit.vue'

if (window.Innoclapps) {
  Innoclapps.booting(function (Vue, router) {
    router.addRoute('settings', {
      path: '/settings/brands',
      component: SettingsBrands,
      meta: { title: i18n.t('brands::brand.brands') },
    })
    router.addRoute('settings', {
      path: '/settings/brands/create',
      component: BrandsCreate,
      name: 'create-brand',
    })
    router.addRoute('settings', {
      path: '/settings/brands/:id/edit',
      component: BrandsEdit,
      name: 'edit-brand',
    })
  })
}
