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
    icon="User"
  >
    <template #heading>
      <i18n-t v-once scope="global" keypath="core::timeline.deleted">
        <template #causer>
          <span class="font-medium" v-text="log.causer_name"></span>
        </template>
      </i18n-t>
    </template>
  </TimelineEntry>
</template>

<script setup>
import propsDefinition from './props'
import TimelineEntry from './RecordTabTimelineTemplate.vue'

defineProps(propsDefinition)
</script>
