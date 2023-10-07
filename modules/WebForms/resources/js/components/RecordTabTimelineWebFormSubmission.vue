<template>
  <timeline-entry
    :resource-name="resourceName"
    :resource-id="resourceId"
    :created-at="log.created_at"
    :is-pinned="log.is_pinned"
    :timelineable-id="log.id"
    :timeline-relationship="log.timeline_relation"
    :timeline-subject-key="resource.timeline_subject_key"
    :timelineable-key="log.timeline_key"
    icon="Bars3BottomLeft"
    :heading="$t('webforms::form.submission')"
    heading-class="font-medium"
  >
    <ICard v-once class="mt-2">
      <div class="space-y-2">
        <div v-for="(property, index) in log.properties" :key="index">
          <div
            class="flex justify-start space-x-1 text-sm font-semibold text-neutral-800 dark:text-neutral-200"
          >
            <span>{{ resources[property.resourceName].singularLabel }} /</span>
            <!-- eslint-disable-next-line vue/no-v-html -->
            <span class="font-medium" v-html="property.label" />
          </div>

          <div class="text-sm text-neutral-600 dark:text-neutral-400">
            <span v-if="property.value === null" v-text="'/'" />
            <span
              v-else
              v-text="maybeFormatDateValue(property.value) || property.value"
            />
          </div>
        </div>
      </div>
    </ICard>
  </timeline-entry>
</template>

<script setup>
import { useDates } from '~/Core/composables/useDates'
import propsDefinition from '~/Core/views/Timeline/props'
import TimelineEntry from '~/Core/views/Timeline/RecordTabTimelineTemplate.vue'

defineProps(propsDefinition)

const { maybeFormatDateValue } = useDates()
const resources = Innoclapps.resources()
</script>
