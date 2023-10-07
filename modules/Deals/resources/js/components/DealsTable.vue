<template>
  <ResourceTable
    v-if="initialize"
    :resource-name="resourceName"
    :row-class="rowClass"
    :table-id="tableId"
    :empty-state="{
      to: { name: 'create-deal' },
      title: $t('deals::deal.empty_state.title'),
      buttonText: $t('deals::deal.create'),
      description: $t('deals::deal.empty_state.description'),
      secondButtonText: $t('core::import.from_file', { file_type: 'CSV' }),
      secondButtonIcon: 'DocumentAdd',
      secondButtonTo: {
        name: 'import-resource',
        params: { resourceName },
      },
    }"
    v-bind="$attrs"
    @loaded="$emit('loaded', $event)"
  >
    <template #after-search="{ collection }">
      <div class="ml-auto flex items-center text-sm">
        <span
          class="font-medium text-neutral-800 dark:text-neutral-300"
          v-text="formatMoney(collection.state.meta.summary?.value)"
        />
        <span
          v-show="
            !isFilteringWonOrLostDeals &&
            collection.state.meta.summary?.weighted_value > 0 &&
            collection.state.meta.summary?.weighted_value !==
              collection.state.meta.summary?.value
          "
          class="mx-1 text-neutral-900 dark:text-neutral-300"
          v-text="'-'"
        />
        <span
          v-show="
            !isFilteringWonOrLostDeals &&
            collection.state.meta.summary?.weighted_value > 0 &&
            collection.state.meta.summary?.weighted_value !==
              collection.state.meta.summary?.value
          "
          class="inline-flex items-center font-medium text-neutral-800 dark:text-neutral-300"
        >
          <Icon icon="Scale" class="mr-1 h-4 w-4" />
          <span>
            {{ formatMoney(collection.state.meta.summary?.weighted_value) }}
          </span>
        </span>
        <span class="mx-1 text-neutral-900 dark:text-neutral-300">-</span>
        <span
          v-t="{
            path: 'deals::deal.count.all',
            args: { count: collection.state.meta.summary?.count },
          }"
          class="font-medium text-neutral-800 dark:text-neutral-300"
        />
      </div>
    </template>
    <template #status="{ row, column }">
      <IBadge
        :variant="column.statuses[row.status].badge"
        :rounded="false"
        class="rounded"
      >
        {{ $t('deals::deal.status.' + column.statuses[row.status].name) }}
      </IBadge>
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
    :deals="[activityBeingCreatedRow]"
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
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'

import ResourceTable from '~/Core/components/Table'
import TableRowAction from '~/Core/components/Table/TableRowAction.vue'
import TableRowActions from '~/Core/components/Table/TableRowActions.vue'
import { useAccounting } from '~/Core/composables/useAccounting'
import { useFloatingResourceModal } from '~/Core/composables/useFloatingResourceModal'
import { useQueryBuilder } from '~/Core/composables/useQueryBuilder'
import { useResourceable } from '~/Core/composables/useResourceable'
import { useTable } from '~/Core/composables/useTable'

const emit = defineEmits(['deleted', 'loaded'])

const props = defineProps({
  tableId: { required: true, type: String },
  initialize: { default: true, type: Boolean },
})

defineOptions({
  inheritAttrs: false,
})

const resourceName = Innoclapps.resourceName('deals')

const { t } = useI18n()
const { formatMoney } = useAccounting()
const { reloadTable } = useTable()
const { floatResourceInEditMode } = useFloatingResourceModal()
const { deleteResource } = useResourceable(resourceName)

const activityBeingCreatedRow = ref(null)

const { findRule } = useQueryBuilder(props.tableId)

const isFilteringWonOrLostDeals = computed(() => {
  let rule = findRule('status')

  if (!rule) {
    return false
  }

  return rule.query.value === 'won' || rule.query.value === 'lost'
})

function rowClass(row) {
  return {
    'has-warning': true,
    'warning-confirmed': row.falls_behind_expected_close_date === true,
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
