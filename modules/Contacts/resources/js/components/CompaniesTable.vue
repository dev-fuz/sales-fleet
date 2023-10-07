<template>
  <ResourceTable
    v-if="initialize"
    :resource-name="resourceName"
    :table-id="tableId"
    :empty-state="{
      to: { name: 'create-company' },
      title: $t('contacts::company.empty_state.title'),
      buttonText: $t('contacts::company.create'),
      description: $t('contacts::company.empty_state.description'),
      secondButtonText: $t('core::import.from_file', { file_type: 'CSV' }),
      secondButtonIcon: 'DocumentAdd',
      secondButtonTo: {
        name: 'import-resource',
        params: { resourceName },
      },
    }"
    @loaded="$emit('loaded', $event)"
  >
    <template #after-search="{ collection }">
      <div class="ml-auto flex items-center text-sm">
        <span
          v-t="{
            path: 'contacts::company.count.all',
            args: { count: collection.state.meta.total },
          }"
          class="font-medium text-neutral-800 dark:text-neutral-300"
        />
      </div>
    </template>
    <template #actions="{ row }">
      <TableRowActions>
        <TableRowAction
          :tooltip="$t('activities::activity.create')"
          icon="Clock"
          @click="activityBeingCreatedRow = row"
        />

        <TableRowAction
          v-if="row.authorizations.update"
          :tooltip="$t('core::app.edit')"
          icon="PencilAlt"
          @click="
            floatResourceInEditMode({
              resourceName,
              resourceId: row.id,
            })
          "
        />

        <TableRowAction
          v-if="row.authorizations.delete"
          :tooltip="$t('core::app.delete')"
          icon="Trash"
          @click="destroy(row.id)"
        />
      </TableRowActions>
    </template>
  </ResourceTable>

  <CreateActivityModal
    :visible="activityBeingCreatedRow !== null"
    :companies="[activityBeingCreatedRow]"
    :with-extended-submit-buttons="true"
    :go-to-list="false"
    @created="
      ({ isRegularAction }) => (
        isRegularAction ? (activityBeingCreatedRow = null) : '',
        reloadTable(tableId)
      )
    "
    @modal-hidden="activityBeingCreatedRow = null"
  />
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'

import ResourceTable from '~/Core/components/Table'
import TableRowAction from '~/Core/components/Table/TableRowAction.vue'
import TableRowActions from '~/Core/components/Table/TableRowActions.vue'
import { useFloatingResourceModal } from '~/Core/composables/useFloatingResourceModal'
import { useResourceable } from '~/Core/composables/useResourceable'
import { useTable } from '~/Core/composables/useTable'

const emit = defineEmits(['deleted', 'loaded'])

const props = defineProps({
  tableId: { required: true, type: String },
  initialize: { default: true, type: Boolean },
})

const resourceName = Innoclapps.resourceName('companies')

const { t } = useI18n()
const { reloadTable } = useTable()
const { floatResourceInEditMode } = useFloatingResourceModal()
const { deleteResource } = useResourceable(resourceName)

const activityBeingCreatedRow = ref(null)

async function destroy(id) {
  await Innoclapps.dialog().confirm()
  await deleteResource(id)

  emit('deleted', id)

  reloadTable(props.tableId)

  Innoclapps.success(t('core::resource.deleted'))
}
</script>
