<template>
  <ICard
    v-show="!noteBeingEdited"
    v-bind="$attrs"
    condensed
    :class="'note-' + noteId"
    footer-class="inline-flex flex-col w-full"
  >
    <template #header>
      <div class="flex space-x-1.5">
        <a
          v-if="hasTextToCollapse"
          href="#"
          class="mr-2 mt-0.5 text-neutral-500 dark:text-neutral-300"
          @click="collapsed = !collapsed"
        >
          <Icon
            :icon="collapsed ? 'ChevronRight' : 'ChevronDown'"
            class="h-4 w-4"
          />
        </a>

        <IAvatar v-once size="xs" :src="user.avatar_url" />

        <div v-once class="flex grow items-center">
          <i18n-t
            scope="global"
            keypath="notes::note.info_created"
            tag="span"
            class="text-sm text-neutral-700 dark:text-white"
          >
            <template #user>
              <span class="font-medium">
                {{ user.name }}
              </span>
            </template>
            <template #date>
              <span class="font-medium" v-text="localizedDateTime(createdAt)" />
            </template>
          </i18n-t>
        </div>

        <IMinimalDropdown
          v-if="authorizations.update && authorizations.delete"
          class="ml-2 self-start md:ml-5"
        >
          <IDropdownItem
            v-show="authorizations.update"
            :text="$t('core::app.edit')"
            @click="toggleEdit"
          />
          <IDropdownItem
            v-show="authorizations.delete"
            :text="$t('core::app.delete')"
            @click="destroy(noteId)"
          />
        </IMinimalDropdown>
      </div>
    </template>

    <TextCollapse
      v-if="collapsable"
      v-model:collapsed="collapsed"
      :text="body"
      :length="250"
      class="wysiwyg-text"
      @has-text-to-collapse="hasTextToCollapse = $event"
      @dblclick="toggleEdit"
    />

    <!-- eslint-disable-next-line vue/no-v-html -->
    <div v-else class="wysiwyg-text" @dblclick="toggleEdit" v-html="body" />

    <CommentsCollapse
      class="mt-6"
      :via-resource="viaResource"
      :via-resource-id="viaResourceId"
      :commentable-id="noteId"
      commentable-type="notes"
      :count="commentsCount"
      :comments="comments"
      @updated="
        synchronizeResource({
          notes: { id: noteId, comments: $event },
        })
      "
      @deleted="
        synchronizeResource({
          notes: { id: noteId, comments: { id: $event, _delete: true } },
        })
      "
      @update:comments="
        synchronizeResource({
          notes: { id: noteId, comments: $event },
        })
      "
      @update:count="
        synchronizeResource({
          notes: { id: noteId, comments_count: $event },
        })
      "
    />

    <template #footer>
      <CommentsAdd
        class="self-end"
        :via-resource="viaResource"
        :via-resource-id="viaResourceId"
        :commentable-id="noteId"
        commentable-type="notes"
        @created="
          (commentsAreVisible = true),
            synchronizeResource({
              notes: {
                id: noteId,
                comments: [$event],
              },
            })
        "
      />
    </template>
  </ICard>

  <NotesEdit
    v-if="noteBeingEdited"
    :via-resource="viaResource"
    :via-resource-id="viaResourceId"
    :note-id="noteId"
    :body="body"
    @cancelled="noteBeingEdited = false"
    @updated="noteBeingEdited = false"
  />
</template>

<script setup>
import { computed, inject, ref } from 'vue'
import { useI18n } from 'vue-i18n'

import { useApp } from '~/Core/composables/useApp'
import { useDates } from '~/Core/composables/useDates'
import { useResourceable } from '~/Core/composables/useResourceable'

import { useComments } from '~/Comments/composables/useComments'

import NotesEdit from './NotesEdit.vue'

const props = defineProps({
  noteId: { required: true, type: Number },
  commentsCount: { required: true, type: Number },
  createdAt: { required: true, type: String },
  body: { required: true, type: String },
  userId: { required: true, type: Number },
  authorizations: { required: true, type: Object },
  comments: { required: true, type: Array },
  viaResource: { required: true, type: String },
  viaResourceId: { required: true, type: [String, Number] },
  collapsable: Boolean,
})

defineOptions({
  inheritAttrs: false,
})

const resourceName = Innoclapps.resourceName('notes')

const synchronizeResource = inject('synchronizeResource')
const decrementResourceCount = inject('decrementResourceCount')

const { t } = useI18n()
const { localizedDateTime } = useDates()
const { findUserById } = useApp()
const { deleteResource } = useResourceable(resourceName)
const { commentsAreVisible } = useComments(props.noteId, 'notes')

const user = computed(() => findUserById(props.userId))

const noteBeingEdited = ref(false)
const hasTextToCollapse = ref(false)
const collapsed = ref(true)

async function destroy(id) {
  await Innoclapps.dialog().confirm()
  await deleteResource(id)

  synchronizeResource({ notes: { id, _delete: true } })
  decrementResourceCount('notes_count')

  Innoclapps.success(t('notes::note.deleted'))
}

function toggleEdit(e) {
  // The double click to edit should not work while in edit mode
  if (e.type == 'dblclick' && noteBeingEdited.value) return
  // For double click event
  if (!props.authorizations.update) return

  noteBeingEdited.value = !noteBeingEdited.value
}
</script>
