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
import CommentsAdd from '~/Comments/components/CommentsAdd.vue'
import CommentsCollapse from '~/Comments/components/CommentsCollapse.vue'
import CommentsList from '~/Comments/components/CommentsList.vue'

import CommentsStore from './store/Comments'

if (window.Innoclapps) {
  Innoclapps.booting(function (Vue, router, store) {
    Vue.component('CommentsAdd', CommentsAdd)
    Vue.component('CommentsCollapse', CommentsCollapse)
    Vue.component('CommentsList', CommentsList)

    store.registerModule('comments', CommentsStore)
  })
}
