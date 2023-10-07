<template>
  <IAlert class="mb-6 border border-info-200">
    {{
      $t('core::app.soft_deletes.trashed_pruning_info', {
        total: pruneAfter,
      })
    }}
  </IAlert>

  <ResourceTable
    :url-path="`/${resourceName}/table`"
    :table-id="tableId"
    :data-request-query-string="{ trashed: 1 }"
    :action-request-params="{ trashed: 1 }"
    :resource-name="resourceName"
    @loaded="isEmpty = $event.empty"
  >
    <template v-if="!isEmpty" #after-search>
      <form class="ml-auto" @submit.prevent="emptyTrash">
        <IButtonMinimal
          type="submit"
          variant="danger"
          icon="Trash"
          :disabled="trashBeingEmptied"
          :loading="trashBeingEmptied"
          :text="$t('core::app.soft_deletes.empty_trash')"
        />
      </form>
    </template>
  </ResourceTable>
</template>

<script setup>
import { ref } from 'vue'

import { useTable } from '~/Core/composables/useTable'

import ResourceTable from './Table.vue'

const props = defineProps({
  resourceName: { required: true, type: String },
})

const tableId = ref(`${props.resourceName}-trashed`)

const trashBeingEmptied = ref(false)
const isEmpty = ref(true)

const { reloadTable } = useTable()

async function emptyTrash() {
  await Innoclapps.dialog().confirm()

  try {
    trashBeingEmptied.value = true

    await Innoclapps.request().delete(`/trashed/${props.resourceName}`)
    reloadTable(tableId)
  } finally {
    trashBeingEmptied.value = false
  }
}

const pruneAfter = Innoclapps.config('soft_deletes.prune_after')
</script>
