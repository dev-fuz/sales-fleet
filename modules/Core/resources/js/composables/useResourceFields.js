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
import { ref, unref } from 'vue'
import { useStore } from 'vuex'

import Fields from '~/Core/fields/Fields'

export function useResourceFields(list = []) {
  const store = useStore()

  const fields = ref(new Fields(list))

  function getCreateFields(resourceName, params = {}) {
    return store.dispatch('fields/getForResource', {
      resourceName: unref(resourceName),
      view: Innoclapps.config('fields.views.create'),
      ...params,
    })
  }

  function getDetailFields(resourceName, id, params = {}) {
    return store.dispatch('fields/getForResource', {
      resourceName: unref(resourceName),
      resourceId: id,
      view: Innoclapps.config('fields.views.detail'),
      ...params,
    })
  }

  function getUpdateFields(resourceName, id, params = {}) {
    return store.dispatch('fields/getForResource', {
      resourceName: unref(resourceName),
      resourceId: id,
      view: Innoclapps.config('fields.views.update'),
      ...params,
    })
  }

  function getIndexFields(resourceName, params = {}) {
    return store.dispatch('fields/getForResource', {
      resourceName: unref(resourceName),
      view: Innoclapps.config('fields.views.index'),
      ...params,
    })
  }

  return {
    fields,

    getCreateFields,
    getUpdateFields,
    getDetailFields,
    getIndexFields,
  }
}
