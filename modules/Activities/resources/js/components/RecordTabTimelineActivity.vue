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
    :icon="log.type.icon"
    :heading="$t('activities::activity.timeline.heading')"
    heading-class="font-medium"
  >
    <div class="mt-3">
      <RelatedActivity
        :activity-id="log.id"
        :title="log.title"
        :comments-count="log.comments_count"
        :is-completed="log.is_completed"
        :is-reminded="log.is_reminded"
        :is-due="log.is_due"
        :type-id="log.activity_type_id"
        :user-id="log.user_id"
        :note="log.note"
        :description="log.description"
        :reminder-minutes-before="log.reminder_minutes_before"
        :due-date="log.due_date"
        :end-date="log.end_date"
        :attachments-count="log.media.length"
        :media="log.media"
        :authorizations="log.authorizations"
        :comments="log.comments || []"
        :associations="log.associations"
        :via-resource="resourceName"
        :via-resource-id="resourceId"
        :related-resource="resource"
      />
    </div>
  </TimelineEntry>
</template>

<script setup>
import propsDefinition from '~/Core/views/Timeline/props'
import TimelineEntry from '~/Core/views/Timeline/RecordTabTimelineTemplate.vue'

import RelatedActivity from './RelatedActivity.vue'

defineProps(propsDefinition)
</script>
