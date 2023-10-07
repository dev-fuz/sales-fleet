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
import { createRouter, createWebHistory } from 'vue-router'

import routes from '@/router/Routes'

/**
 * Scroll behavior
 *
 * @param  {Object} to
 * @param  {Object} from
 * @param  {Object|undefined} savedPosition
 *
 * @return {Object}
 */
function scrollBehavior(to, from, savedPosition) {
  if (savedPosition) {
    return savedPosition
  }

  if (to.hash) {
    return { el: to.hash }
  }

  if (to.meta && to.meta.scrollToTop === false) {
    return {}
  }

  return { left: 0, top: 0 }
}

const router = createRouter({
  scrollBehavior,
  history: createWebHistory(),
  routes,
})

export default router
