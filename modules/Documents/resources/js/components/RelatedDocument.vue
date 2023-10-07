<template>
  <DocumentsEdit
    v-if="documentBeingEdited"
    :id="documentId"
    :section="editSection"
    :via-resource="viaResource"
    :exit-using="() => (documentBeingEdited = false)"
    @reactivated="refreshRecordView"
    @sent="refreshRecordView"
    @lost="refreshRecordView"
    @accept="refreshRecordView"
    @changed="handleDocumentChanged"
    @deleted="handleDocumentDeleted"
  />

  <ICard v-bind="$attrs" :class="'document-' + documentId">
    <div class="flex items-center">
      <a
        class="text-base/6 font-medium text-neutral-900 dark:text-white"
        :href="path"
        @click.prevent="editDocument()"
        v-text="displayName"
      />
      <TextBackground
        :color="type.swatch_color"
        class="ml-3 inline-flex items-center justify-center rounded-full px-2.5 text-sm/5 font-normal dark:!text-white"
      >
        <Icon :icon="type.icon" class="mr-1.5 h-4 w-4 text-current" />
        {{ type.name }}
      </TextBackground>
    </div>

    <AssociationsPopover
      :model-value="associations"
      :associateables="associations"
      :initial-associateables="relatedResource"
      :disabled="associationsBeingSaved"
      :primary-resource-name="viaResource"
      @change="
        syncAssociations(documentId, $event).then(updatedDocument =>
          synchronizeResource({ documents: updatedDocument })
        )
      "
    />

    <div class="mt-5">
      <div
        class="rounded-md bg-neutral-50 px-6 py-5 dark:bg-neutral-800 sm:flex sm:items-start sm:justify-between"
      >
        <div class="sm:flex sm:items-start">
          <TextBackground
            :color="statuses[status].color"
            class="inline-flex w-auto items-center justify-center self-start rounded-full px-2.5 text-sm/5 font-normal dark:!text-white sm:shrink-0"
          >
            {{ $t('documents::document.status.' + status) }}
          </TextBackground>

          <div class="mt-3 sm:ml-4 sm:mt-0">
            <div
              class="text-sm font-medium text-neutral-900 dark:text-neutral-100"
              v-text="formatMoney(amount)"
            />

            <div
              class="mt-1 text-sm text-neutral-600 dark:text-neutral-200 sm:flex sm:items-center"
              v-text="
                $t('documents::document.sent_at', {
                  date: lastDateSent ? localizedDateTime(lastDateSent) : 'N/A',
                })
              "
            />

            <div
              v-if="acceptedAt"
              class="mt-1 text-sm text-neutral-600 sm:flex sm:items-center"
            >
              {{ $t('documents::document.accepted_at') }}:
              <span class="ml-1" v-text="localizedDateTime(acceptedAt)" />
            </div>
          </div>
        </div>
        <div class="mt-4 sm:ml-6 sm:mt-0 sm:shrink-0">
          <div class="flex items-center space-x-2">
            <IButton
              v-show="authorizations.view"
              size="sm"
              variant="white"
              :text="$t('core::app.edit')"
              @click="editDocument()"
            />

            <IButton
              v-if="status === 'draft'"
              v-show="authorizations.view"
              size="sm"
              variant="white"
              icon="Mail"
              :text="$t('documents::document.send.send')"
              @click="editDocument('send')"
            />

            <IMinimalDropdown>
              <div class="divide-y divide-neutral-200 dark:divide-neutral-700">
                <IDropdownItem
                  :href="publicUrl"
                  :text="$t('documents::document.view')"
                />
                <div>
                  <IDropdownItem
                    :href="publicUrl + '/pdf'"
                    target="_blank"
                    rel="noopener noreferrer"
                    :text="$t('documents::document.view_pdf')"
                  />
                  <IDropdownItem
                    :href="publicUrl + '/pdf?output=download'"
                    :text="$t('documents::document.download_pdf')"
                  />
                </div>
                <div>
                  <IDropdownItem
                    v-if="authorizations.update"
                    :text="$t('core::app.edit')"
                    @click="editDocument()"
                  />
                  <IDropdownItem
                    v-if="authorizations.delete"
                    :text="$t('core::app.delete')"
                    @click="destroy(documentId)"
                  />
                </div>
              </div>
            </IMinimalDropdown>
          </div>
        </div>
      </div>
    </div>
  </ICard>
</template>

<script setup>
import { computed, inject, ref } from 'vue'
import { useI18n } from 'vue-i18n'

import AssociationsPopover from '~/Core/components/AssociationsPopover.vue'
import TextBackground from '~/Core/components/TextBackground.vue'
import { useAccounting } from '~/Core/composables/useAccounting'
import { useDates } from '~/Core/composables/useDates'
import { useResourceable } from '~/Core/composables/useResourceable'

import { useDocumentTypes } from '../composables/useDocumentTypes'
import DocumentsEdit from '../views/DocumentsEdit.vue'

const props = defineProps({
  documentId: { type: Number, required: true },
  typeId: { type: Number, required: true },
  status: { type: String, required: true },
  displayName: { type: String, required: true },
  path: { type: String, required: true },
  publicUrl: { type: String, required: true },
  acceptedAt: String,
  lastDateSent: String,
  amount: { required: true },
  authorizations: { type: Object, required: true },
  associations: { type: Object, required: true },
  viaResource: { type: String, required: true },
  viaResourceId: { required: true, type: [String, Number] },
  relatedResource: { required: true, type: Object },
})

defineOptions({
  inheritAttrs: false,
})

const synchronizeResource = inject('synchronizeResource')
const decrementResourceCount = inject('decrementResourceCount')

const { t } = useI18n()

const { syncAssociations, associationsBeingSaved, deleteResource } =
  useResourceable(Innoclapps.resourceName('documents'))

const { formatMoney } = useAccounting()
const { localizedDateTime } = useDates()

const { findTypeById } = useDocumentTypes()

const documentBeingEdited = ref(false)
const editSection = ref(null)

const type = computed(() => findTypeById(props.typeId))

const statuses = Innoclapps.config('documents.statuses')

function handleDocumentChanged(changedDocument) {
  synchronizeResource({ documents: changedDocument })
}

function handleDocumentDeleted(deletedDocument) {
  synchronizeResource({ documents: { id: deletedDocument.id, _delete: true } })
  refreshRecordView()
}

function editDocument(section = null) {
  editSection.value = section || 'details'
  documentBeingEdited.value = true
}

function refreshRecordView() {
  Innoclapps.$emit('refresh-details-view')
}

async function destroy(id) {
  await Innoclapps.dialog().confirm()
  await deleteResource(id)

  if (props.status === 'draft') {
    decrementResourceCount('draft_documents_for_user_count')
  }

  synchronizeResource({ documents: { id, _delete: true } })
  decrementResourceCount('documents_count')
  decrementResourceCount('documents_for_user_count')

  Innoclapps.success(t('documents::document.deleted'))
}
</script>
