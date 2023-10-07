<template>
  <div class="mb-2 block lg:hidden">
    <ActivitiesTableTypePicker v-model="selectedType" />
  </div>
  <ResourceTable
    v-if="initialize"
    :resource-name="resourceName"
    :row-class="rowClass"
    :data-request-query-string="dataRequestQueryString"
    :table-id="tableId"
    :empty-state="{
      to: { name: 'create-activity' },
      title: $t('activities::activity.empty_state.title'),
      buttonText: $t('activities::activity.create'),
      description: $t('activities::activity.empty_state.description'),
      secondButtonText: $t('core::import.from_file', { file_type: 'CSV' }),
      secondButtonIcon: 'DocumentAdd',
      secondButtonTo: {
        name: 'import-resource',
        params: { resourceName },
      },
    }"
    v-bind="$attrs"
  >
    <template #after-search="{ collection }">
      <div class="hidden lg:ml-6 lg:block">
        <ActivitiesTableTypePicker v-model="selectedType" />
      </div>
      <div class="ml-auto flex items-center text-sm">
        <span
          v-t="{
            path: 'activities::activity.count',
            args: { count: collection.state.meta.total },
          }"
          class="font-medium text-neutral-800 dark:text-neutral-300"
        />
      </div>
    </template>
    <template #actions="{ row }">
      <TableRowActions>
        <ActivityStateChange
          v-if="row.authorizations.changeState"
          class="mt-1.5"
          :is-completed="row.is_completed"
          :activity-id="row.id"
          @state-changed="reloadTable(tableId)"
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
  <!-- Edit/View -->
  <router-view name="edit" />
</template>

<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'

import ResourceTable from '~/Core/components/Table'
import TableRowAction from '~/Core/components/Table/TableRowAction.vue'
import TableRowActions from '~/Core/components/Table/TableRowActions.vue'
import { useResourceable } from '~/Core/composables/useResourceable'
import { useTable } from '~/Core/composables/useTable'

import ActivitiesTableTypePicker from './ActivitiesTableTypePicker.vue'
import ActivityStateChange from './ActivityStateChange.vue'

const emit = defineEmits(['deleted'])

const props = defineProps({
  tableId: { required: true, type: String },
  initialize: { default: true, type: Boolean },
})

defineOptions({
  inheritAttrs: false,
})

const resourceName = Innoclapps.resourceName('activities')

const { t } = useI18n()
const { reloadTable } = useTable()
const { deleteResource } = useResourceable(resourceName)

const selectedType = ref(undefined)

const dataRequestQueryString = computed(() => ({
  activity_type_id: selectedType.value,
}))

function rowClass(row) {
  return {
    'has-warning': true,
    'warning-confirmed': row.is_due,
    'has-success': true,
    'success-confirmed': row.is_completed,
  }
}

async function destroy(id) {
  await Innoclapps.dialog().confirm()
  await deleteResource(id)

  emit('deleted', id)
  reloadTable(props.tableId)

  Innoclapps.success(t('core::resource.deleted'))
}
</script>
