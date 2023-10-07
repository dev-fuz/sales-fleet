<template>
  <ICard
    v-show="!activityBeingEdited"
    v-bind="$attrs"
    condensed
    :class="'activity-' + activityId"
    footer-class="inline-flex flex-col w-full"
    no-body
  >
    <template #header>
      <div class="flex">
        <div class="mr-2 mt-px flex shrink-0 self-start">
          <StateChange
            v-memo="[isCompleted]"
            class="ml-px md:mt-px"
            :activity-id="activityId"
            :is-completed="isCompleted"
            :disabled="!authorizations.changeState"
            @state-changed="handleActivityStateChanged"
          />
        </div>
        <div
          class="flex grow flex-col space-y-1 md:flex-row md:space-x-3 md:space-y-0"
        >
          <div class="flex grow flex-col items-start">
            <h3
              class="-mb-1 truncate whitespace-normal text-base/6 font-medium text-neutral-700 dark:text-white"
              :class="{ 'line-through': isCompleted }"
              v-text="title"
            />

            <AssociationsPopover
              :disabled="associationsBeingSaved"
              :model-value="associations"
              :initial-associateables="relatedResource"
              :associateables="associations"
              :primary-record="relatedResource"
              :primary-resource-name="viaResource"
              @change="
                syncAssociations(activityId, $event).then(updatedActivity =>
                  synchronizeResource({ activities: updatedActivity })
                )
              "
            />
          </div>
          <TextBackground
            :color="type.swatch_color"
            class="inline-flex shrink-0 items-center self-start rounded-md py-0.5 dark:!text-white sm:rounded-full"
          >
            <DropdownSelectInput
              v-if="authorizations.update"
              :items="typesForDropdown"
              :model-value="typeId"
              label-key="name"
              value-key="id"
              @change="updateActivity({ activity_type_id: $event.id })"
            >
              <template #default="{ toggle }">
                <button
                  type="button"
                  class="flex w-full items-center justify-between rounded-md px-2.5 text-xs leading-5 focus:outline-none"
                  @click="toggle"
                >
                  <div class="flex items-center">
                    <Icon :icon="type.icon" class="mr-1.5 h-4 w-4" />
                    {{ type.name }}

                    <Icon icon="ChevronDown" class="ml-1 h-4 w-4" />
                  </div>
                </button>
              </template>
            </DropdownSelectInput>
            <span v-else class="flex items-center px-1 text-xs">
              <Icon :icon="type.icon" class="mr-1.5 h-4 w-4" />
              {{ type.name }}
            </span>
          </TextBackground>
        </div>
        <div class="ml-2 mt-px inline-flex self-start md:ml-5">
          <IMinimalDropdown class="mt-1 md:mt-0.5">
            <IDropdownItem
              v-if="authorizations.update"
              :text="$t('core::app.edit')"
              @click="toggleEdit"
            />
            <IDropdownItem
              :text="$t('activities::activity.download_ics')"
              @click="downloadICS"
            />
            <IDropdownItem
              v-if="authorizations.delete"
              :text="$t('core::app.delete')"
              @click="destroy(activityId)"
            />
          </IMinimalDropdown>
        </div>
      </div>
    </template>

    <IAlert
      v-if="isDue"
      variant="warning"
      icon="Clock"
      :rounded="false"
      :class="[
        '-mt-px border-warning-100',
        Boolean(note) ? 'border-t' : 'border-y',
      ]"
      wrapper-class="-ml-px sm:ml-1.5"
    >
      {{
        $t('activities::activity.activity_was_due', {
          date: dueDate.time
            ? localizedDateTime(dueDate.date + ' ' + dueDate.time)
            : localizedDate(dueDate.date),
        })
      }}
    </IAlert>

    <div @dblclick="toggleEdit">
      <div v-if="note" class="-mt-px border border-warning-100 bg-warning-50">
        <TextCollapse
          :text="note"
          :length="100"
          class="wysiwyg-text px-4 py-2.5 text-warning-700 sm:px-6"
        >
          <template #action="{ collapsed, toggle }">
            <div
              v-show="collapsed"
              class="absolute bottom-1 h-1/2 w-full cursor-pointer bg-gradient-to-t from-warning-50 to-transparent dark:from-warning-100"
              @click="toggle"
            />

            <a
              v-show="!collapsed"
              v-t="'core::app.show_less'"
              href="#"
              class="my-2.5 inline-block px-4 text-sm font-medium text-warning-800 hover:text-warning-900 sm:px-6"
              @click.prevent="toggle"
            />
          </template>
        </TextCollapse>
      </div>

      <ICardBody condensed>
        <div class="space-y-4 sm:space-y-6">
          <div v-if="description" class="mb-8">
            <p
              class="mb-2 inline-flex text-sm font-medium text-neutral-800 dark:text-white"
            >
              <Icon icon="Bars3BottomLeft" class="mr-3 h-5 w-5 text-current" />
              <span v-t="'activities::activity.description'"></span>
            </p>
            <TextCollapse
              :text="description"
              :length="200"
              class="wysiwyg-text ml-8 dark:text-neutral-300 sm:mb-0"
            />
          </div>
          <div
            class="flex flex-col flex-wrap space-x-0 space-y-2 align-baseline lg:flex-row lg:space-x-4 lg:space-y-0"
          >
            <div
              v-if="user"
              v-i-tooltip.top="$t('activities::activity.owner')"
              class="self-start sm:self-auto"
            >
              <DropdownSelectInput
                v-if="authorizations.update"
                :items="usersForDropdown"
                :model-value="userId"
                value-key="id"
                label-key="name"
                @change="updateActivity({ user_id: $event.id })"
              >
                <template #label="{ label }">
                  <IAvatar
                    size="xs"
                    class="-ml-1 mr-3"
                    :src="user.avatar_url"
                  />
                  <span
                    class="text-neutral-800 hover:text-neutral-500 dark:text-neutral-200 dark:hover:text-neutral-400"
                    v-text="label"
                  />
                </template>
              </DropdownSelectInput>
              <p
                v-else
                class="flex items-center text-sm font-medium text-neutral-800 dark:text-neutral-200"
              >
                <IAvatar size="xs" class="mr-3" :src="user.avatar_url" />
                {{ user.name }}
              </p>
            </div>

            <ActivityDateDisplay
              class="font-medium"
              :due-date="dueDate"
              :end-date="endDate"
              :is-due="isDue"
            />
          </div>
        </div>
        <p
          v-if="reminderMinutesBefore && !isReminded"
          class="mt-2 flex items-center text-sm text-neutral-800 dark:text-neutral-200 sm:mt-3"
        >
          <Icon
            icon="Bell"
            class="mr-3 h-5 w-5 text-neutral-800 dark:text-white"
          />

          <span>
            {{ reminderText }}
          </span>
        </p>
      </ICardBody>
    </div>

    <div
      class="border-y border-neutral-100 px-4 py-2.5 dark:border-neutral-800 sm:px-6"
    >
      <MediaCard
        :card="false"
        :show="attachmentsAreVisible"
        :wrapper-class="[
          'ml-8',
          {
            'py-4': attachmentsCount === 0,
            'mb-4': attachmentsCount > 0,
          },
        ]"
        class="mt-1"
        :resource-name="resourceName"
        :resource-id="activityId"
        :media="media"
        :authorize-delete="authorizations.update"
        @deleted="handleActivityMediaDeleted"
        @uploaded="handleActivityMediaUploaded"
      >
        <template #heading>
          <p
            class="inline-flex items-center text-sm font-medium text-neutral-800 dark:text-white"
          >
            <Icon icon="PaperClip" class="mr-3 h-5 w-5 text-current" />
            <a
              href="#"
              class="inline-flex items-center focus:outline-none"
              @click.prevent="attachmentsAreVisible = !attachmentsAreVisible"
            >
              <span>
                {{ $t('core::app.attachments') }} ({{ attachmentsCount }})
              </span>
              <Icon
                :icon="attachmentsAreVisible ? 'ChevronDown' : 'ChevronRight'"
                class="ml-3 h-4 w-4"
              />
            </a>
          </p>
        </template>
      </MediaCard>
    </div>

    <div
      v-show="commentsCount"
      class="border-b border-neutral-100 px-4 py-2.5 dark:border-neutral-800 sm:px-6"
    >
      <CommentsCollapse
        :via-resource="viaResource"
        :via-resource-id="viaResourceId"
        :commentable-id="activityId"
        commentable-type="activities"
        :count="commentsCount"
        :comments="comments"
        list-wrapper-class="ml-8"
        class="mt-1"
        @updated="
          synchronizeResource({
            activities: { id: activityId, comments: $event },
          })
        "
        @deleted="
          synchronizeResource({
            activities: {
              id: activityId,
              comments: { id: $event, _delete: true },
            },
          })
        "
        @update:comments="
          synchronizeResource({
            activities: { id: activityId, comments: $event },
          })
        "
        @update:count="
          synchronizeResource({
            activities: { id: activityId, comments_count: $event },
          })
        "
      />
    </div>

    <template #footer>
      <CommentsAdd
        class="self-end"
        :via-resource="viaResource"
        :via-resource-id="viaResourceId"
        :commentable-id="activityId"
        commentable-type="activities"
        @created="
          (commentsAreVisible = true),
            synchronizeResource({
              activities: {
                id: activityId,
                comments: [$event],
              },
            })
        "
      />
    </template>
  </ICard>

  <EditActivity
    v-if="activityBeingEdited"
    :activity-id="activityId"
    :via-resource="viaResource"
    :via-resource-id="viaResourceId"
    :related-resource="relatedResource"
    @cancelled="activityBeingEdited = false"
    @updated="activityBeingEdited = false"
  />
</template>

<script setup>
import { computed, inject, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import FileDownload from 'js-file-download'

import {
  determineReminderTypeBasedOnMinutes,
  determineReminderValueBasedOnMinutes,
} from '@/utils'

import AssociationsPopover from '~/Core/components/AssociationsPopover.vue'
import MediaCard from '~/Core/components/Media/ResourceRecordMediaCard.vue'
import TextBackground from '~/Core/components/TextBackground.vue'
import { useApp } from '~/Core/composables/useApp'
import { useDates } from '~/Core/composables/useDates'
import { useResourceable } from '~/Core/composables/useResourceable'

import { useComments } from '~/Comments/composables/useComments'

import { useActivityTypes } from '../composables/useActivityTypes'

import ActivityDateDisplay from './ActivityDateDisplay.vue'
import StateChange from './ActivityStateChange.vue'
import EditActivity from './RelatedActivityEdit.vue'

const props = defineProps({
  activityId: { required: true, type: Number },
  title: { required: true, type: String },
  commentsCount: { required: true, type: Number },
  isCompleted: { required: true, type: Boolean },
  isReminded: { required: true, type: Boolean },
  isDue: { required: true, type: Boolean },
  typeId: { required: true, type: Number },
  userId: { required: true, type: Number },
  note: { required: true },
  description: { required: true },
  reminderMinutesBefore: { required: true },
  dueDate: { required: true },
  endDate: { required: true },
  attachmentsCount: { required: true, type: Number },
  media: { required: true, type: Array },
  authorizations: { required: true, type: Object },
  comments: { required: true, type: Array },
  associations: { required: true, type: Object },
  viaResource: { required: true, type: String },
  viaResourceId: { required: true, type: [String, Number] },
  relatedResource: { required: true, type: Object },
})

defineOptions({
  inheritAttrs: false,
})

const resourceName = Innoclapps.resourceName('activities')

const synchronizeResource = inject('synchronizeResource')
const incrementResourceCount = inject('incrementResourceCount')
const decrementResourceCount = inject('decrementResourceCount')

const { t } = useI18n()

const { localizedDateTime, localizedDate } = useDates()

const {
  syncAssociations,
  associationsBeingSaved,
  updateResource,
  deleteResource,
} = useResourceable(resourceName)

const { users, findUserById } = useApp()

const usersForDropdown = computed(() =>
  users.value.map(user => ({ id: user.id, name: user.name }))
)

const { commentsAreVisible } = useComments(props.activityId, 'activities')

const activityBeingEdited = ref(false)
const attachmentsAreVisible = ref(false)

const { typesByName: types, findTypeById } = useActivityTypes()

const typesForDropdown = computed(() =>
  types.value.map(type => ({
    id: type.id,
    name: type.name,
    icon: type.icon,
  }))
)

const type = computed(() => findTypeById(props.typeId))

const user = computed(() => findUserById(props.userId))

const reminderText = computed(() => {
  return t('core::app.reminder_set_for', {
    value: determineReminderValueBasedOnMinutes(props.reminderMinutesBefore),
    type: t(
      'core::dates.' +
        determineReminderTypeBasedOnMinutes(props.reminderMinutesBefore)
    ),
  })
})

/**
 * Download ICS file for the activity.
 */
function downloadICS() {
  Innoclapps.request()
    .get(`/activities/${props.activityId}/ics`, {
      responseType: 'blob',
    })
    .then(response => {
      FileDownload(
        response.data,
        response.headers['content-disposition'].split('filename=')[1]
      )
    })
}

/**
 * Update the current activity.
 */
function updateActivity(payload = {}) {
  updateResource(
    {
      via_resource: props.viaResource,
      via_resource_id: props.viaResourceId,
      ...payload,
    },
    props.activityId
  ).then(updatedActivity =>
    synchronizeResource({ activities: updatedActivity })
  )
}

/**
 * Delete activity from storage.
 */
async function destroy(id) {
  await Innoclapps.dialog().confirm()
  await deleteResource(id)

  synchronizeResource({ activities: { id, _delete: true } })
  decrementResourceCount('incomplete_activities_for_user_count')

  Innoclapps.success(t('activities::activity.deleted'))
}

/**
 * Activity state changed.
 */
function handleActivityStateChanged(activity) {
  synchronizeResource({ activities: activity })

  if (activity.is_completed) {
    decrementResourceCount('incomplete_activities_for_user_count')
  } else {
    incrementResourceCount('incomplete_activities_for_user_count')
  }
}

/**
 * Toggle edit.
 */
function toggleEdit(e) {
  // The double click to edit should not work while in edit mode
  if (e.type == 'dblclick' && activityBeingEdited.value) return
  // For double click event
  if (!props.authorizations.update) return

  activityBeingEdited.value = !activityBeingEdited.value
}

/**
 * Handle activity media uploaded.
 */
function handleActivityMediaUploaded(media) {
  synchronizeResource({
    activities: {
      id: props.activityId,
      media: [media],
    },
  })
}

/**
 * Handle activity media deleted.
 */
function handleActivityMediaDeleted(media) {
  synchronizeResource({
    activities: {
      id: props.activityId,
      media: { id: media.id, _delete: true },
    },
  })
}
</script>
