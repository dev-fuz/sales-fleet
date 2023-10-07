<template>
  <TimelineEntry
    :resource-name="resourceName"
    :resource-id="resourceId"
    :created-at="log.created_at"
    :is-pinned="log.is_pinned || false"
    :timelineable-id="log.id"
    :timeline-relationship="log.timeline_relation"
    :timeline-subject-key="resource.timeline_subject_key"
    :timelineable-key="log.timeline_key"
    icon="Phone"
    :heading="$t('calls::call.timeline.heading')"
    heading-class="font-medium"
  >
    <div class="mt-3">
      <CallsView
        :call-id="log.id"
        :comments-count="log.comments_count"
        :call-date="log.date"
        :body="log.body"
        :user-id="log.user_id"
        :outcome-id="log.call_outcome_id"
        :authorizations="log.authorizations"
        :comments="log.comments || []"
        :via-resource="resourceName"
        :via-resource-id="resourceId"
        collapsable
      />
    </div>
  </TimelineEntry>
</template>

<script setup>
import propsDefinition from '~/Core/views/Timeline/props'
import TimelineEntry from '~/Core/views/Timeline/RecordTabTimelineTemplate.vue'

import CallsView from './CallsView.vue'

defineProps(propsDefinition)
</script>
