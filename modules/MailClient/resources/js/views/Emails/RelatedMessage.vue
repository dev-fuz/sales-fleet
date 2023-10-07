<template>
  <ICard
    v-observe-visibility="{
      callback: handleVisibilityChanged,
      once: true,
      throttle: 300,
      intersection: {
        threshold: 0.5,
      },
    }"
    :class="'email-' + email.id"
    condensed
  >
    <template #header>
      <div class="flex space-x-1">
        <div class="inline-flex grow flex-col">
          <h3
            :class="[!email.is_read ? 'font-bold' : 'font-semibold']"
            class="truncate whitespace-normal text-base/6 font-medium text-neutral-700 dark:text-white md:text-lg"
          >
            <span
              v-once
              v-text="
                email.subject
                  ? email.subject
                  : '(' + $t('mailclient::inbox.no_subject') + ')'
              "
            />
          </h3>
          <span v-once class="text-sm text-neutral-500 dark:text-neutral-300">
            {{ localizedDateTime(email.date) }}
          </span>
          <div class="flex">
            <AssociationsPopover
              :model-value="email.associations"
              :associateables="email.associations"
              :initial-associateables="relatedResource"
              :disabled="associationsBeingSaved"
              :primary-resource-name="viaResource"
              @change="
                syncAssociations(email.id, $event).then(updatedMessage =>
                  synchronizeResource({ emails: updatedMessage })
                )
              "
            />
          </div>
        </div>
        <div class="ml-3 flex shrink-0 items-center self-start">
          <IBadge
            v-if="email.opened_at"
            v-once
            variant="success"
            wrapper-class="mr-1 flex"
          >
            <Icon icon="Eye" class="mr-1 h-5 w-5" />
            <span class="mr-1"> ({{ email.opens }}) - </span>
            {{ localizedDateTime(email.opened_at) }}
          </IBadge>
          <IMinimalDropdown>
            <IDropdownItem
              :text="$t('mailclient::mail.view')"
              @click="
                $router.push({
                  name: 'inbox-message',
                  params: {
                    id: email.id,
                    account_id: email.email_account_id,
                    folder_id: email.folders[0].id,
                  },
                })
              "
            />

            <IDropdownItem :text="$t('core::app.delete')" @click="destroy" />
          </IMinimalDropdown>
        </div>
      </div>
    </template>
    <MessageRecipients
      v-once
      :label="$t('mailclient::inbox.from')"
      :recipients="email.from"
    />
    <div class="flex">
      <div>
        <MessageRecipients
          v-once
          :label="$t('mailclient::inbox.to')"
          :recipients="email.to"
        />
      </div>
      <div class="-mt-0.5 ml-3">
        <IPopover placement="top" class="w-72">
          <button type="button" class="link text-sm focus:outline-none">
            {{ $t('core::app.details') }}
            <span aria-hidden="true">&rarr;</span>
          </button>
          <template #popper>
            <div class="flex flex-col px-4 py-2">
              <MessageRecipients
                v-once
                :label="$t('mailclient::inbox.from')"
                :recipients="email.from"
              />
              <MessageRecipients
                v-once
                :label="$t('mailclient::inbox.to')"
                :recipients="email.to"
              />
              <MessageRecipients
                v-once
                :label="$t('mailclient::inbox.reply_to')"
                :recipients="email.reply_to"
                :show-when-empty="false"
              />
              <MessageRecipients
                v-once
                :label="$t('mailclient::inbox.cc')"
                :recipients="email.cc"
                :show-when-empty="false"
              />
              <MessageRecipients
                v-once
                :label="$t('mailclient::inbox.bcc')"
                :recipients="email.bcc"
                :show-when-empty="false"
              />
            </div>
          </template>
        </IPopover>
      </div>
    </div>
    <div v-once class="mail-text all-revert">
      <div class="font-sans text-sm leading-[initial] dark:text-white">
        <TextCollapse
          v-if="email.visible_text"
          :text="email.visible_text"
          lightbox
          :length="250"
          class="mt-3"
        />

        <div class="clear-both"></div>

        <HiddenText :text="email.hidden_text" />
      </div>
    </div>
    <div
      v-if="email.media.length > 0"
      v-once
      class="-mx-6 mb-3 mt-4 border-t border-neutral-200 px-6 pt-4 dark:border-neutral-700"
    >
      <dd
        v-t="'mailclient.mail.attachments'"
        class="mb-2 text-sm font-medium leading-6 text-neutral-900 dark:text-neutral-100"
      />

      <MessageAttachments :attachments="email.media" />
    </div>
    <template #footer>
      <div class="clear-both"></div>
      <div class="flex divide-x divide-neutral-200 dark:divide-neutral-700">
        <a
          href="#"
          class="link flex items-center text-sm"
          @click.prevent="reply(true)"
        >
          <Icon icon="Reply" class="mr-1.5 h-4 w-4" />
          {{ $t('mailclient::inbox.reply') }}
        </a>
        <a
          v-if="hasMoreReplyTo"
          href="#"
          class="link ml-2 flex items-center pl-2 text-sm"
          @click.prevent="replyAll"
        >
          <Icon icon="Reply" class="mr-1.5 h-4 w-4" />
          {{ $t('mailclient::inbox.reply_all') }}
        </a>
        <a
          href="#"
          class="link ml-2 flex items-center pl-2 text-sm"
          @click.prevent="forward(true)"
        >
          <Icon icon="Share" class="mr-1.5 h-4 w-4" />
          {{ $t('mailclient::inbox.forward') }}
        </a>
      </div>
    </template>
    <MessageReply
      :message="email"
      :visible="isReplying || isForwarding"
      :forward="isForwarding"
      :resource-name="viaResource"
      :resource-id="viaResourceId"
      :related-resource="relatedResource"
      :to-all="replyToAll"
      @modal-hidden="handleReplyModalHidden"
    />
  </ICard>
</template>

<script setup>
import { computed, inject, ref } from 'vue'
import { ObserveVisibility } from 'vue-observe-visibility'
import { useStore } from 'vuex'

import AssociationsPopover from '~/Core/components/AssociationsPopover.vue'
import { useDates } from '~/Core/composables/useDates'
import { useResourceable } from '~/Core/composables/useResourceable'

import MessageAttachments from './MessageAttachments.vue'
import HiddenText from './MessageHiddenText.vue'
import MessageRecipients from './MessageRecipients.vue'
import MessageReply from './MessageReply.vue'

const props = defineProps({
  viaResource: { type: String, required: true },
  viaResourceId: { type: [String, Number], required: true },
  relatedResource: { type: Object, required: true },
  email: { type: Object, required: true },
})

const synchronizeResource = inject('synchronizeResource')
const decrementResourceCount = inject('decrementResourceCount')

const vObserveVisibility = ObserveVisibility

const store = useStore()

const { syncAssociations, associationsBeingSaved, deleteResource } =
  useResourceable(Innoclapps.resourceName('emails'))

const { localizedDateTime } = useDates()

const isReplying = ref(false)
const isForwarding = ref(false)
const replyToAll = ref(false)

const hasMoreReplyTo = computed(
  () => props.email.cc && props.email.cc.length > 0
)

function handleReplyModalHidden() {
  // Allow timeout because the hidden blur sometimes is not removed
  setTimeout(() => {
    reply(false)
    forward(false)
  }, 300)
}

function handleVisibilityChanged(isVisible) {
  if (isVisible && !props.email.is_read) {
    Innoclapps.request()
      .post(`/emails/${props.email.id}/read`)
      .then(({ data }) => {
        synchronizeResource({ emails: data })
        decrementResourceCount('unread_emails_for_user_count')
        store.dispatch('emailAccounts/decrementUnreadCountUI')
      })
  }
}

async function destroy() {
  await Innoclapps.dialog().confirm()
  await deleteResource(props.email.id)

  if (!props.email.is_read) {
    decrementResourceCount('unread_emails_for_user_count')
    store.dispatch('emailAccounts/decrementUnreadCountUI')
  }

  synchronizeResource({ emails: { id: props.email.id, _delete: true } })
}

function reply(state = true) {
  isReplying.value = state
  replyToAll.value = false
}

function forward(state = true) {
  isForwarding.value = state
}

function replyAll() {
  isReplying.value = true
  replyToAll.value = true
}
</script>
