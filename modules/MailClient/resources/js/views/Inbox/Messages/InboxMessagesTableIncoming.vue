<template>
  <div :class="{ 'sync-stopped-by-system': isSyncStopped }">
    <ResourceTable
      ref="tableRef"
      :resource-name="resourceName"
      :table-id="tableId"
      :row-class="rowClass"
      :action-request-params="actionRequestParams"
      :data-request-query-string="dataRequestQueryString"
    >
      <template #subject="{ row }">
        <MessageSubject
          :subject="row.subject"
          :message-id="row.id"
          :account-id="accountId"
        />
      </template>
      <template #from="{ row }">
        <MailRecipient v-if="row.from" :recipient="row.from" />
        <p
          v-else
          v-text="'(' + $t('mailclient::inbox.unknown_address') + ')'"
        />
      </template>
      <template #date="{ row }">
        {{ localizedDateTime(row.date) }}
      </template>
    </ResourceTable>
  </div>
</template>

<script setup>
import { ref } from 'vue'

import ResourceTable from '~/Core/components/Table'
import { useDates } from '~/Core/composables/useDates'

import MailRecipient from '../../Emails/MessageRecipient.vue'

import MessageSubject from './InboxMessageSubject.vue'

defineProps({
  tableId: { required: true, type: String },
  dataRequestQueryString: { type: Object, required: true },
  actionRequestParams: { type: Object, required: true },
  isSyncStopped: Boolean,
  accountId: { type: Number, required: true },
})

const resourceName = Innoclapps.resourceName('emails')

const { localizedDateTime } = useDates()

const tableRef = ref(null)

function rowClass(row) {
  return !row.is_read ? 'unread' : 'read'
}

defineExpose({ tableRef })
</script>

<style>
.read td {
  font-weight: normal !important;
}
.unread td {
  font-weight: bold !important;
}
</style>
