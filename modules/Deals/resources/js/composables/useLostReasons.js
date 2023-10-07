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
import { computed, ref } from 'vue'
import { createGlobalState } from '@vueuse/core'
import orderBy from 'lodash/orderBy'

import { useLoader } from '~/Core/composables/useLoader'

export const useLostReasons = createGlobalState(() => {
  const { setLoading, isLoading: lostReasonsAreBeingFetched } = useLoader()

  const lostReasons = ref([])

  const lostReasonsByName = computed(() => orderBy(lostReasons.value, 'name'))

  function setLostReasons(list) {
    lostReasons.value = list
  }

  function fetchLostReasons() {
    setLoading(true)

    Innoclapps.request()
      .get('/lost-reasons', {
        params: {
          per_page: 100,
        },
      })
      .then(({ data }) => (lostReasons.value = data.data))
      .finally(() => setLoading(false))
  }

  return {
    lostReasons,
    lostReasonsByName,
    lostReasonsAreBeingFetched,

    setLostReasons,
    fetchLostReasons,
  }
})
