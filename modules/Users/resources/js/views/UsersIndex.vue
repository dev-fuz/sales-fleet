<template>
  <div class="mb-5 flex items-center justify-between">
    <h3
      v-t="'users::user.users'"
      class="whitespace-nowrap text-lg/6 font-medium text-neutral-800 dark:text-white"
    />
    <div class="space-x-3">
      <IButton
        variant="secondary"
        size="sm"
        icon="Mail"
        :disabled="!componentReady"
        :to="{ name: 'invite-user' }"
        :text="$t('users::user.invite')"
      />
      <IButton
        variant="primary"
        size="sm"
        icon="Plus"
        :disabled="!componentReady"
        :to="{ name: 'create-user' }"
        :text="$t('users::user.create')"
      />
    </div>
  </div>

  <ResourceTable
    ref="tableRef"
    :resource-name="resourceName"
    :table-id="tableId"
    :with-customize-button="true"
    @loaded="componentReady = true"
  />
  <!-- Create, Edit -->
  <router-view
    name="createEdit"
    @created="reloadTable(tableId)"
    @updated="reloadTable(tableId)"
    @hidden="$router.push({ name: 'users-index' })"
  />

  <router-view name="invite" />
</template>

<script setup>
import { onUnmounted, ref } from 'vue'
import { useStore } from 'vuex'

import ResourceTable from '~/Core/components/Table'
import { useApp } from '~/Core/composables/useApp'
import { useGlobalEventListener } from '~/Core/composables/useGlobalEventListener'
import { useTable } from '~/Core/composables/useTable'

const resourceName = Innoclapps.resourceName('users')
const tableId = 'users'

const store = useStore()
const { resetStoreState } = useApp()
const { reloadTable } = useTable()

const componentReady = ref(false)
const tableRef = ref(null)

function actionExecutedHandler(action) {
  if (action.destroyable) {
    action.ids.forEach(id => store.commit('users/REMOVE', id))

    // refetch the actions so the deleted user is not in the transfer list.
    tableRef.value.refetchActions()
  }
}

useGlobalEventListener('action-executed', actionExecutedHandler)

onUnmounted(() => {
  /**
   * We need to reset the state in case changes are performed
   * because of the local cached data for the users
   */
  resetStoreState()
})
</script>
