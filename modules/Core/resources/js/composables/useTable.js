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
import { unref } from 'vue'
import { useStore } from 'vuex'

export function useTable() {
  const store = useStore()

  function reloadTable(tableId) {
    Innoclapps.$emit('reload-resource-table', unref(tableId))
  }

  function customizeTable(tableId, value = true) {
    store.commit('table/SET_CUSTOMIZE_VISIBILTY', {
      id: unref(tableId),
      value: value,
    })
  }

  return { reloadTable, customizeTable }
}
