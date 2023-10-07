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

import ProductsCreate from './views/ProductsCreate.vue'
import ProductsEdit from './views/ProductsEdit.vue'
import ProductsIndex from './views/ProductsIndex.vue'

export default [
  {
    path: '/products',
    name: 'product-index',
    component: ProductsIndex,
    meta: {
      title: i18n.t('billable::product.products'),
    },
    children: [
      {
        path: 'create',
        name: 'create-product',
        component: ProductsCreate,
        meta: { title: i18n.t('billable::product.create') },
      },
      {
        path: ':id',
        name: 'view-product',
        component: ProductsEdit,
      },
      {
        path: ':id/edit',
        name: 'edit-product',
        component: ProductsEdit,
      },
    ],
  },
]
