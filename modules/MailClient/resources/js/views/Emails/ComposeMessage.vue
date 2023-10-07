<template>
  <IModal
    id="composeMessageModal"
    size="xl"
    static-backdrop
    :hide-footer="showTemplates"
    :visible="visible"
    :title="$t('mailclient::inbox.new_message')"
    @hidden="modalHidden"
    @shown="handleModalShown"
  >
    <div
      class="-mx-6 mb-4 border-y border-neutral-200 px-6 py-3 dark:border-neutral-700"
    >
      <div class="flex">
        <div class="mr-4">
          <a
            v-show="!showTemplates"
            v-t="'mailclient.mail.templates.templates'"
            href="#"
            class="link text-sm font-medium"
            @click.prevent="showTemplates = true"
          />
          <a
            v-show="showTemplates"
            v-t="'mailclient.mail.compose'"
            href="#"
            class="link text-sm font-medium"
            @click.prevent="showTemplates = false"
          />
        </div>
        <div v-show="!showTemplates" class="font-medium">
          <AssociationsPopover
            v-model="form.associations"
            :primary-record-disabled="true"
            :primary-record="resourceName ? relatedResource : undefined"
            :primary-resource-name="resourceName"
            :initial-associateables="resourceName ? relatedResource : undefined"
            :associateables="customAssociationsValue"
            @change="
              parsePlaceholdersForMessage(), parsePlaceholdersForSubject()
            "
          >
            <template
              #after-record="{
                title,
                resource: associatedResourceName,
                record,
                isSelected,
                isSearching,
                selectedRecords,
              }"
            >
              <span
                v-if="
                  showWillUsePlaceholdersIconToAssociateResourceRecord(
                    record,
                    selectedRecords,
                    associatedResourceName,
                    isSelected,
                    isSearching
                  )
                "
                v-i-tooltip.top="
                  $t('mailclient::inbox.will_use_placeholders_from_record', {
                    resourceName: title,
                  })
                "
                class="ml-1.5 self-start"
              >
                <Icon
                  icon="CodeBracket"
                  class="h-5 w-5 text-neutral-500 dark:text-neutral-400"
                />
              </span>
            </template>
          </AssociationsPopover>
        </div>
      </div>
    </div>
    <div v-show="!showTemplates">
      <IOverlay :show="!componentReady">
        <IAlert variant="danger" dismissible :show="hasInvalidAddresses">
          {{ $t('mailclient::mail.validation.invalid_recipients') }}
        </IAlert>
        <MailRecipient
          :form="form"
          type="to"
          :label="$t('mailclient::inbox.to')"
          @recipient-removed="handleToRecipientRemovedEvent"
          @recipient-selected="handleRecipientSelectedEvent"
        >
          <template #after>
            <div v-if="!wantsBcc || !wantsCc" class="-mt-2 ml-2 space-x-2">
              <a
                v-if="!wantsCc"
                v-t="'mailclient.inbox.cc'"
                href="#"
                class="link"
                @click.prevent="setWantsCC"
              />
              <a
                v-if="!wantsBcc"
                v-t="'mailclient.inbox.bcc'"
                href="#"
                class="link"
                @click.prevent="setWantsBCC"
              />
            </div>
          </template>
        </MailRecipient>
        <hr class="my-3 border-t border-neutral-200 dark:border-neutral-700" />
        <div v-show="wantsCc">
          <MailRecipient
            :form="form"
            type="cc"
            :label="$t('mailclient::inbox.cc')"
            @recipient-removed="dissociateRemovedRecipients"
            @recipient-selected="associateSelectedRecipients"
          />
          <hr
            class="my-3 border-t border-neutral-200 dark:border-neutral-700"
          />
        </div>
        <div v-show="wantsBcc">
          <MailRecipient
            :form="form"
            type="bcc"
            :label="$t('mailclient::inbox.bcc')"
          />
          <hr
            class="my-3 border-t border-neutral-200 dark:border-neutral-700"
          />
        </div>
        <div class="flex items-center">
          <IFormLabel :label="$t('mailclient::inbox.from')" class="w-14" />
          <DropdownSelectInput
            v-model="account"
            adaptive-width
            :items="accounts"
            value-key="id"
            label-key="display_email"
          >
            <template #label="{ label }">
              {{ account.formatted_from_name_header }}
              <span v-text="'<' + label + '>'"></span>
            </template>
          </DropdownSelectInput>
        </div>
        <hr class="my-3 border-t border-neutral-200 dark:border-neutral-700" />
        <div class="flex items-center">
          <div class="w-14">
            <IFormLabel
              :label="$t('mailclient::inbox.subject')"
              for="subject"
            />
          </div>
          <div class="grow">
            <div class="relative">
              <IFormInput
                id="subject"
                ref="subjectRef"
                :class="{
                  '!border-danger-600':
                    !subjectPlaceholdersSyntaxIsValid ||
                    hasInvalidSubjectPlaceholders,
                  'border-dashed !border-neutral-400': subjectDragover,
                }"
                :model-value="showParsedSubject ? parsedSubject : subject"
                :disabled="showParsedSubject"
                @update:model-value="subject = $event"
                @dragover="
                  !showParsedSubject ? (subjectDragover = true) : undefined
                "
                @dragleave="subjectDragover = false"
                @drop="subjectDragover = false"
              />
              <a
                v-show="showParsedSubject"
                href="#"
                tabindex="-1"
                @click.prevent="showParsedSubject = false"
              >
                <Icon
                  icon="CodeBracket"
                  class="absolute bottom-0 right-4 top-0 m-auto h-5 w-5 text-neutral-500"
                />
              </a>
              <a
                v-if="
                  subjectContainsPlaceholders &&
                  !showParsedSubject &&
                  resourcesForPlaceholders.length > 0
                "
                href="#"
                tabindex="-1"
                @click.prevent="showParsedSubject = true"
              >
                <Icon
                  icon="ViewfinderCircle"
                  class="absolute bottom-0 right-4 top-0 m-auto h-5 w-5 text-neutral-500"
                />
              </a>
            </div>

            <IFormError v-text="form.getError('subject')" />
          </div>
        </div>
        <hr class="my-3 border-t border-neutral-200 dark:border-neutral-700" />
        <MailEditor
          ref="editorRef"
          v-model="form.message"
          :placeholders="placeholders"
          :with-drop="true"
          @placeholder-inserted="parsePlaceholdersForMessage"
        />
        <div class="relative mt-3">
          <MediaUpload
            :action-url="`${$store.state.apiURL}/media/pending/${attachmentsDraftId}`"
            :select-file-text="$t('core::app.attach_files')"
            @file-uploaded="handleAttachmentUploaded"
          >
            <MediaItemsList
              class="mb-3"
              :items="attachments"
              :authorize-delete="true"
              @delete-requested="destroyPendingAttachment"
            />
          </MediaUpload>
        </div>
      </IOverlay>
      <IAlert
        v-if="showEmptyPlaceholdersMessage"
        variant="warning"
        class="mt-4"
      >
        {{ $t('mailclient::inbox.pre_send_empty_placeholders_found') }}
      </IAlert>
    </div>
    <template #modal-footer="{ cancel }">
      <div class="flex flex-col sm:flex-row sm:items-center">
        <div class="grow">
          <CreateFollowUpTask
            v-show="Boolean(resourceName)"
            v-model="form.task_date"
          />
        </div>
        <div
          class="mt-2 space-y-2 sm:mt-0 sm:flex sm:items-center sm:space-x-2 sm:space-y-0"
        >
          <IButton
            class="w-full sm:w-auto"
            variant="white"
            :text="$t('core::app.cancel')"
            @click="cancel"
          />
          <IButton
            class="w-full sm:w-auto"
            :loading="sending"
            :variant="showEmptyPlaceholdersMessage ? 'danger' : 'primary'"
            :disabled="sendButtonIsDisabled"
            :text="
              showEmptyPlaceholdersMessage
                ? $t('core::app.confirm')
                : $t('mailclient::inbox.send')
            "
            @click="send(showEmptyPlaceholdersMessage)"
          />
        </div>
      </div>
    </template>
    <PredefinedMailTemplatesList
      v-if="showTemplates"
      @selected="handleTemplateSelected"
      @created="scrollToTop"
      @updated="scrollToTop"
      @will-create="scrollToTop"
      @will-edit="scrollToTop"
    />
  </IModal>
</template>

<script setup>
import { computed, inject, nextTick, ref, watch } from 'vue'
import { useStore } from 'vuex'

import { randomString } from '@/utils'

import AssociationsPopover from '~/Core/components/AssociationsPopover.vue'
import MediaItemsList from '~/Core/components/Media/MediaItemsList.vue'
import MediaUpload from '~/Core/components/Media/MediaUpload.vue'

import CreateFollowUpTask from '~/Activities/components/CreateFollowUpTask.vue'

import MailEditor from '../../components/MailEditor'
import { useMessageComposer } from '../../composables/useMessageComposer'
import PredefinedMailTemplatesList from '../Templates/PredefinedMailTemplatesList.vue'

import MailRecipient from './RecipientSelectorField.vue'

const emit = defineEmits(['modal-hidden'])

const props = defineProps({
  resourceName: String,
  resourceId: [String, Number], // Must be provided if resourceName is provided
  relatedResource: Object, // Must be provided if resourceName is provided
  visible: Boolean,
  defaultAccount: Object,
  to: { type: Array, default: () => [] },
  associations: { type: Array, default: () => [] },
})

const synchronizeResource = inject('synchronizeResource', null)

const store = useStore()

const {
  form,
  attachments,
  attachmentsDraftId,
  handleAttachmentUploaded,
  destroyPendingAttachment,
  customAssociationsValue,
  placeholders,
  resourcesForPlaceholders,
  subject,
  parsedSubject,
  subjectPlaceholdersSyntaxIsValid,
  hasInvalidSubjectPlaceholders,
  subjectContainsPlaceholders,
  parsePlaceholdersForMessage,
  parsePlaceholdersForSubject,
  showWillUsePlaceholdersIconToAssociateResourceRecord,
  sending,
  sendRequest,
  hasInvalidAddresses,
  associateSelectedRecipients,
  dissociateRemovedRecipients,
  associateMessageRecord,
  handleRecipientSelectedEvent,
  handleToRecipientRemovedEvent,
  setWantsCC,
  setWantsBCC,
  wantsBcc,
  wantsCc,
  hasEmptyPlaceholders,
} = useMessageComposer(
  props.resourceName,
  props.relatedResource,
  synchronizeResource
)

const showEmptyPlaceholdersMessage = ref(false)

watch(hasEmptyPlaceholders, (newVal, oldVal) => {
  if (oldVal && !newVal) {
    showEmptyPlaceholdersMessage.value = false
  }
})

const editorRef = ref(null)
const subjectRef = ref(null)
const showParsedSubject = ref(false)

const account = ref({})
const componentReady = ref(false)
const showTemplates = ref(false)
const subjectDragover = ref(false)

const accounts = computed(() => store.getters['emailAccounts/accounts'])

const sendButtonIsDisabled = computed(
  () => form.to.length === 0 || !subject.value || sending.value
)

watch(
  () => props.defaultAccount,
  newVal => {
    account.value = newVal
  }
)

// In case the to is updated
// we need to update the form value too
// e.q. update contact email and click create email
// if we don't update the value the old email will be used
watch(
  () => props.to,
  newVal => {
    form.to = newVal
    associateSelectedRecipients(newVal)
  },
  { immediate: true }
)

function associateAdditionalAssociations(associations) {
  associations.forEach(record =>
    associateMessageRecord(record, record.resourceName)
  )
}

watch(
  () => props.associations,
  newVal => {
    associateAdditionalAssociations(newVal)
  },
  { immediate: true }
)

function handleTemplateSelected(template) {
  // Allow the sales agent to enter custom subject if needed
  if (!subject.value) {
    subject.value = template.subject
  }

  form.message = template.body + form.message
  showTemplates.value = false
  parsePlaceholdersForMessage()
  parsePlaceholdersForSubject()
  scrollToTop()
  nextTick(() => editorRef.value.focus())
}

function scrollToTop() {
  document.querySelector('.dialog').scrollTo({ top: 0, behavior: 'instant' })
}

/**
 * Handle modal shown event
 * Each time the modal is shown we need to generate new draft id for the attachments
 */
function handleModalShown() {
  // If prevously there was to selected, use the same to as associations
  // e.q. open deal modal, close deal modal, open again, the form.to won't be associated
  if (form.to) {
    associateSelectedRecipients(form.to)
  }

  associateAdditionalAssociations(props.associations)

  attachmentsDraftId.value = randomString()
}

/**
 * Handle modal shown hidden
 *
 * Reset the state, we need to reset the form and the
 * attachments because when the modal is hidden each time
 * new attachmentsDraftId is generated
 *
 * @return {Void}
 */
function modalHidden() {
  form.reset()

  // Add to again if there was TO recipients provided
  if (props.to) {
    form.to = props.to
  }
  subject.value = null
  parsedSubject.value = null
  attachments.value = []
  customAssociationsValue.value = {}
  emit('modal-hidden')
}

function send(skipEmptyPlaceholdersCheck = false) {
  if (skipEmptyPlaceholdersCheck === false && hasEmptyPlaceholders.value) {
    showEmptyPlaceholdersMessage.value = true

    return
  } else if (showEmptyPlaceholdersMessage.value) {
    showEmptyPlaceholdersMessage.value = false
  }

  sendRequest(`/inbox/emails/${account.value.id}`).then(() =>
    Innoclapps.modal().hide('composeMessageModal')
  )
}

function prepareComponent() {
  store.dispatch('emailAccounts/fetch').then(accounts => {
    account.value = props.defaultAccount || accounts[0]
    componentReady.value = true
  })
}

prepareComponent()

defineExpose({
  subjectRef,
})
</script>
