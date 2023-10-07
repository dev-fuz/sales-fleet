<template>
  <TimelineEntry
    :resource-name="resourceName"
    :resource-id="resourceId"
    :created-at="log.created_at"
    :is-pinned="log.is_pinned"
    :timelineable-id="log.id"
    :timeline-relationship="log.timeline_relation"
    :timeline-subject-key="resource.timeline_subject_key"
    :timelineable-key="log.timeline_key"
    icon="Mail"
    :heading="$t('mailclient::inbox.message')"
    heading-class="font-medium"
  >
    <template #date>
      {{ localizedDateTime(log.date) }}
    </template>
    <div class="mt-3">
      <RelatedMessage
        :email="log"
        :via-resource="resourceName"
        :via-resource-id="resourceId"
        :related-resource="resource"
      />
    </div>
  </TimelineEntry>
</template>

<script setup>
import { useDates } from '~/Core/composables/useDates'
import propsDefinition from '~/Core/views/Timeline/props'
import TimelineEntry from '~/Core/views/Timeline/RecordTabTimelineTemplate.vue'

import RelatedMessage from '../views/Emails/RelatedMessage.vue'

defineProps(propsDefinition)

const { localizedDateTime } = useDates()
</script>
