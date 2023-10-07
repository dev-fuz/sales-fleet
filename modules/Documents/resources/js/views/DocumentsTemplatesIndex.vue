<template>
  <ILayout>
    <template #actions>
      <NavbarSeparator class="hidden lg:block" />
      <div class="inline-flex items-center">
        <IButton
          :to="{ name: 'create-document-template' }"
          icon="Plus"
          :text="$t('documents::document.template.create')"
          size="sm"
        />
      </div>
    </template>
    <ResourceTable
      :resource-name="resourceName"
      :table-id="tableId"
      :empty-state="{
        to: { name: 'create-document-template' },
        title: $t('documents::document.template.empty_state.title'),
        buttonText: $t('documents::document.template.create'),
        description: $t('documents::document.template.empty_state.description'),
      }"
    >
      <template #actions="{ row }">
        <TableRowActions>
          <TableRowAction
            v-if="row.authorizations.update"
            :to="{ name: 'edit-document-template', params: { id: row.id } }"
            :tooltip="$t('core::app.edit')"
            icon="PencilAlt"
          />

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
    <!-- Create, Edit -->
    <router-view name="create" @created="reloadTable(tableId)" />
    <router-view name="edit" @updated="reloadTable(tableId)" />
  </ILayout>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'

import ResourceTable from '~/Core/components/Table'
import TableRowAction from '~/Core/components/Table/TableRowAction.vue'
import TableRowActions from '~/Core/components/Table/TableRowActions.vue'
import { useResourceable } from '~/Core/composables/useResourceable'
import { useTable } from '~/Core/composables/useTable'

const resourceName = Innoclapps.resourceName('document-templates')
const tableId = 'document-templates'

const { t } = useI18n()
const router = useRouter()
const { reloadTable } = useTable()
const { deleteResource, cloneResource } = useResourceable(resourceName)

async function clone(id) {
  const template = await cloneResource(id)

  reloadTable(tableId)
  router.push({ name: 'edit-document-template', params: { id: template.id } })
}

async function destroy(id) {
  await Innoclapps.dialog().confirm()
  await deleteResource(id)

  reloadTable(tableId)

  Innoclapps.success(t('documents::document.template.deleted'))
}
</script>
