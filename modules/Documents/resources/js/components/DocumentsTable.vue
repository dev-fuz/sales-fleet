<template>
  <div class="mb-2 block lg:hidden">
    <DocumentsTableStatusPicker v-model="selectedStatus" />
  </div>

  <ResourceTable
    v-if="initialize"
    :resource-name="resourceName"
    :table-id="tableId"
    :data-request-query-string="dataRequestQueryString"
    :empty-state="{
      to: { name: 'create-document' },
      title: $t('documents::document.empty_state.title'),
      buttonText: $t('documents::document.create'),
      description: $t('documents::document.empty_state.description'),
    }"
    @loaded="$emit('loaded', $event)"
  >
    <template #after-search="{ collection }">
      <div class="hidden lg:ml-6 lg:block">
        <DocumentsTableStatusPicker v-model="selectedStatus" />
      </div>
      <div class="ml-auto flex items-center text-sm">
        <span
          v-t="{
            path: 'documents::document.count.all',
            args: { count: collection.state.meta.total },
          }"
          class="font-medium text-neutral-800 dark:text-neutral-300"
        />
      </div>
    </template>
    <template #status="{ row }">
      <TextBackground
        :color="statuses[row.status].color"
        class="inline-flex items-center justify-center rounded-full px-2.5 text-sm/5 font-normal dark:!text-white"
      >
        {{ $t('documents::document.status.' + row.status) }}
      </TextBackground>
    </template>
    <template #actions="{ row }">
      <TableRowActions>
        <TableRowAction
          :href="row.public_url"
          :tooltip="$t('documents::document.view')"
          icon="Eye"
        />

        <TableRowAction
          v-if="row.authorizations.update && row.status === 'draft'"
          :to="{
            name: 'edit-document',
            params: { id: row.id },
            query: { section: 'send' },
          }"
          :tooltip="$t('documents::document.send.send')"
          icon="Mail"
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
</template>

<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'

import ResourceTable from '~/Core/components/Table'
import TableRowAction from '~/Core/components/Table/TableRowAction.vue'
import TableRowActions from '~/Core/components/Table/TableRowActions.vue'
import TextBackground from '~/Core/components/TextBackground.vue'
import { useResourceable } from '~/Core/composables/useResourceable'
import { useTable } from '~/Core/composables/useTable'

import DocumentsTableStatusPicker from './DocumentsTableStatusPicker.vue'

const emit = defineEmits(['deleted', 'loaded'])

const props = defineProps({
  tableId: { required: true, type: String },
  initialize: { default: true, type: Boolean },
})

const resourceName = Innoclapps.resourceName('documents')

const { t } = useI18n()
const router = useRouter()
const { reloadTable } = useTable()
const { deleteResource, cloneResource } = useResourceable(resourceName)

const statuses = Innoclapps.config('documents.statuses')
const selectedStatus = ref(null)

const dataRequestQueryString = computed(() => ({
  status: selectedStatus.value,
}))

async function clone(id) {
  const document = await cloneResource(id)

  reloadTable(props.tableId)
  router.push({ name: 'edit-document', params: { id: document.id } })
}

async function destroy(id) {
  await Innoclapps.dialog().confirm()
  await deleteResource(id)

  emit('deleted', id)
  reloadTable(props.tableId)

  Innoclapps.success(t('core::resource.deleted'))
}
</script>
