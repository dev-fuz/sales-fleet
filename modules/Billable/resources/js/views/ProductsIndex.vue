<template>
  <ILayout>
    <template #actions>
      <NavbarSeparator class="hidden lg:block" />
      <div class="inline-flex items-center">
        <div class="mr-3 lg:mr-6">
          <IMinimalDropdown type="horizontal">
            <IDropdownItem
              icon="DocumentAdd"
              :to="{
                name: 'import-resource',
                params: { resourceName },
              }"
              :text="$t('core::import.import')"
            />
            <IDropdownItem
              v-if="$gate.userCan('export products')"
              icon="DocumentDownload"
              :text="$t('core::app.export.export')"
              @click="$iModal.show('export-modal')"
            />
            <IDropdownItem
              icon="Trash"
              :to="{
                name: 'trashed-resource-records',
                params: { resourceName },
              }"
              :text="$t('core::app.soft_deletes.trashed')"
            />
            <IDropdownItem
              icon="Cog"
              :text="$t('core::table.list_settings')"
              @click="customizeTable(tableId)"
            />
          </IMinimalDropdown>
        </div>

        <IButton
          :to="{ name: 'create-product' }"
          icon="Plus"
          size="sm"
          :text="$t('billable::product.create')"
        />
      </div>
    </template>
    <ResourceTable
      :resource-name="resourceName"
      :table-id="tableId"
      :empty-state="{
        to: { name: 'create-product' },
        title: $t('billable::product.empty_state.title'),
        buttonText: $t('billable::product.create'),
        description: $t('billable::product.empty_state.description'),
        secondButtonText: $t('core::import.from_file', { file_type: 'CSV' }),
        secondButtonIcon: 'DocumentAdd',
        secondButtonTo: {
          name: 'import-resource',
          params: { resourceName },
        },
      }"
    >
      <template #after-search="{ collection }">
        <div class="ml-auto flex items-center text-sm">
          <span
            v-t="{
              path: 'billable::product.count',
              args: { count: collection.state.meta.total },
            }"
            class="font-medium text-neutral-800 dark:text-neutral-300"
          />
        </div>
      </template>
      <template #actions="{ row }">
        <TableRowActions>
          <TableRowAction
            :tooltip="$t('core::app.clone')"
            icon="Duplicate"
            @click="clone(row.id)"
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
    <ProductExport
      url-path="/products/export"
      :resource-name="resourceName"
      :title="$t('billable::product.export')"
    />
    <!-- Create, Edit -->
    <router-view
      @created="reloadTable(tableId)"
      @restored="reloadTable(tableId)"
      @updated="reloadTable(tableId)"
    />
  </ILayout>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'

import ProductExport from '~/Core/components/Export'
import ResourceTable from '~/Core/components/Table'
import TableRowAction from '~/Core/components/Table/TableRowAction.vue'
import TableRowActions from '~/Core/components/Table/TableRowActions.vue'
import { useResourceable } from '~/Core/composables/useResourceable'
import { useTable } from '~/Core/composables/useTable'

const resourceName = Innoclapps.resourceName('products')
const tableId = 'products'

const { t } = useI18n()
const { reloadTable, customizeTable } = useTable()
const { deleteResource, cloneResource } = useResourceable(resourceName)
const router = useRouter()

async function clone(id) {
  const product = await cloneResource(id)

  reloadTable(tableId)

  router.push({ name: 'edit-product', params: { id: product.id } })
}

async function destroy(id) {
  await Innoclapps.dialog().confirm()
  await deleteResource(id)

  reloadTable(tableId)

  Innoclapps.success(t('billable::product.deleted'))
}
</script>
