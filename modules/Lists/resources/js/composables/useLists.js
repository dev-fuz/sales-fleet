import { ref, computed } from 'vue'
import orderBy from 'lodash/orderBy'
import { createGlobalState } from '@vueuse/core'
import { useLoader } from '~/Core/resources/js/composables/useLoader'

export const useLists = createGlobalState(() => {

  const { setLoading, isLoading: listsAreBeingFetched } = useLoader()

  const lists = ref([])

  //console.log(lists);

  const listsByName = computed(() => orderBy(lists.value, 'name'))

  // Only excuted once
  fetchLists()

  function idx(id) {
    return lists.value.findIndex(list => list.id == id)
  }

  function removeList(id) {
    lists.value.splice(idx(id), 1)
  }

  function addList(list) {
    lists.value.push(list)
  }

  function setList(id, list) {
    lists.value[idx(id)] = list
  }

  function patchList(id, list) {
    const listIndex = idx(id)
    lists.value[listIndex] = Object.assign(lists.value[listIndex], list)
  }

  async function fetchList(id, options = {}) {
    const { data } = await Innoclapps.request().get(`/lists/${id}`, options)
    return data
  }

  async function deleteList(id) {
    await Innoclapps.request().delete(`/lists/${id}`)
    removeList(id)
  }

  function fetchLists() {
    setLoading(true)

    Innoclapps.request()
      .get('/lists')
      .then(({ data }) => (lists.value = data))
      .finally(() => setLoading(false))
  }

  return {
    lists,
    listsByName,
    listsAreBeingFetched,

    addList,
    removeList,
    setList,
    patchList,

    fetchLists,
    fetchList,
    deleteList,
  }
})
