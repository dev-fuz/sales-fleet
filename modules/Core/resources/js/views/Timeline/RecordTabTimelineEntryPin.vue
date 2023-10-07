<template>
  <a
    v-if="!isPinned"
    v-t="'core::timeline.pin'"
    href="#"
    class="text-xs text-neutral-800 hover:text-neutral-500 focus:outline-none dark:text-neutral-200 dark:hover:text-neutral-400"
    @click.prevent="pin"
  />
  <a
    v-else
    v-t="'core::timeline.unpin'"
    href="#"
    class="text-xs text-neutral-800 hover:text-neutral-500 focus:outline-none dark:text-neutral-200 dark:hover:text-neutral-400"
    @click.prevent="unpin"
  />
</template>

<script setup>
import { inject } from 'vue'

import { useDates } from '~/Core/composables/useDates'

const props = defineProps({
  resourceName: { type: String, required: true },
  resourceId: { type: [String, Number], required: true },
  isPinned: { type: Boolean, required: true },

  timelineSubjectKey: { type: String, required: true },
  timelineRelationship: { type: String, required: true },

  timelineableKey: { type: String, required: true },
  timelineableId: { type: [Number, String], required: true },
})

const synchronizeResource = inject('synchronizeResource')

const { appMoment } = useDates()

function pin() {
  Innoclapps.request()
    .post('timeline/pin', {
      subject_id: Number(props.resourceId),
      subject_type: props.timelineSubjectKey,
      timelineable_id: Number(props.timelineableId),
      timelineable_type: props.timelineableKey,
    })
    .then(() => {
      synchronizeResource({
        [props.timelineRelationship]: {
          id: props.timelineableId,
          is_pinned: true,
          pinned_date: appMoment().toISOString(), // toISOString allowing consistency with the back-end dates
        },
      })
    })
}

function unpin() {
  Innoclapps.request()
    .post('timeline/unpin', {
      subject_id: Number(props.resourceId),
      subject_type: props.timelineSubjectKey,
      timelineable_id: Number(props.timelineableId),
      timelineable_type: props.timelineableKey,
    })
    .then(() => {
      synchronizeResource({
        [props.timelineRelationship]: {
          id: props.timelineableId,
          is_pinned: false,
          pinned_date: null,
        },
      })
    })
}
</script>
