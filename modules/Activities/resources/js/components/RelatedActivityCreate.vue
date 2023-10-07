<template>
  <ICard
    tag="form"
    method="POST"
    :overlay="fields.isEmpty()"
    @submit.prevent="create"
  >
    <FormFields
      :fields="fields"
      :form-id="form.formId"
      :resource-name="resourceName"
      focus-first
    />

    <template #footer>
      <div class="flex w-full flex-wrap items-center justify-between sm:w-auto">
        <div>
          <AssociationsPopover
            v-model="form.associations"
            :primary-record="relatedResource"
            :primary-resource-name="viaResource"
            :primary-record-disabled="true"
            :initial-associateables="relatedResource"
          />
        </div>
        <div
          class="mt-sm-0 mt-2 flex w-full flex-col sm:w-auto sm:flex-row sm:items-center sm:justify-end sm:space-x-2"
        >
          <IFormToggle
            v-model="form.is_completed"
            class="mb-4 mr-4 pr-4 sm:mb-0 sm:border-r sm:border-neutral-200 sm:dark:border-neutral-700"
            :label="$t('activities::activity.mark_as_completed')"
          />
          <IButton
            class="mb-2 sm:mb-0"
            variant="white"
            size="sm"
            :text="$t('core::app.cancel')"
            @click="$emit('cancel')"
          />
          <IButton
            type="submit"
            size="sm"
            :disabled="form.busy"
            :text="$t('activities::activity.add')"
          />
        </div>
      </div>
    </template>

    <IAlert
      :show="form.recentlySuccessful"
      class="mt-4 border border-success-300"
      variant="success"
    >
      {{ $t('activities::activity.created') }}
    </IAlert>
  </ICard>
</template>

<script setup>
import { computed, inject } from 'vue'
import { useI18n } from 'vue-i18n'

import AssociationsPopover from '~/Core/components/AssociationsPopover.vue'
import { useFieldsForm } from '~/Core/composables/useFieldsForm'
import { useResourceable } from '~/Core/composables/useResourceable'
import { useResourceFields } from '~/Core/composables/useResourceFields'

defineEmits(['cancel'])

const props = defineProps({
  viaResource: { type: String, required: true },
  viaResourceId: { type: [String, Number], required: true },
  relatedResource: { required: true, type: Object },
})

const synchronizeResource = inject('synchronizeResource')
const incrementResourceCount = inject('incrementResourceCount')

const resourceName = Innoclapps.resourceName('activities')

const { t } = useI18n()

const { fields, getCreateFields } = useResourceFields()

const { form } = useFieldsForm(
  fields,
  {
    is_completed: false,
    associations: {
      [props.viaResource]: [props.viaResourceId],
    },
  },
  {
    resetOnSuccess: true,
  }
)

const { createResource } = useResourceable(resourceName)

const contactsForGuestsSelectField = computed(() =>
  props.viaResource === 'contacts'
    ? [props.relatedResource]
    : props.relatedResource.contacts || []
)

async function prepareComponent() {
  fields.value
    .set(
      await getCreateFields(resourceName, {
        viaResource: props.viaResource,
        viaResourceId: props.viaResourceId,
      })
    )
    .update('guests', { contacts: contactsForGuestsSelectField })
}

/**
 * Store the activity in storage
 *
 * @return {Void}
 */
async function create() {
  let activity = await createResource(
    form.withQueryString({
      via_resource: props.viaResource,
      via_resource_id: props.viaResourceId,
    })
  )

  Innoclapps.success(t('activities::activity.created'))

  if (!activity.is_completed) {
    incrementResourceCount('incomplete_activities_for_user_count')
  }

  synchronizeResource({ activities: [activity] })
}

prepareComponent()
</script>
