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
import { useTags } from './composables/useTags'
import FieldsStore from './store/Fields'
import FiltersStore from './store/Filters'
import TableStore from './store/Table'
import routes from './routes'

const { setTags } = useTags()

if (window.Innoclapps) {
  Innoclapps.booting(function (Vue, router, store) {
    store.registerModule('fields', FieldsStore)
    store.registerModule('table', TableStore)
    store.registerModule('filters', FiltersStore)

    setTags(Innoclapps.config('tags') || [])

    // Routes
    routes.forEach(route => router.addRoute(route))
  })
}
