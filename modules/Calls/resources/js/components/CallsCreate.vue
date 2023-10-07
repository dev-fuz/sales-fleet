<template>
  <ICard
    tag="form"
    method="POST"
    :overlay="fields.isEmpty()"
    @submit.prevent="create"
  >
    <FormFields
      :form-id="form.formId"
      :fields="fields"
      :resource-name="resourceName"
    />
    <template #footer>
      <div class="flex flex-col sm:flex-row sm:items-center">
        <CreateFollowUpTask
          ref="createFollowUpTaskRef"
          v-model="form.task_date"
          class="grow"
        />

        <div class="mt-2 space-y-2 sm:mt-0 sm:space-x-2 sm:space-y-0">
          <IButton
            class="w-full sm:w-auto"
            variant="white"
            size="sm"
            :text="$t('core::app.cancel')"
            @click="$emit('cancel')"
          />
          <IButton
            class="w-full sm:w-auto"
            size="sm"
            :disabled="form.busy"
            :text="$t('calls::call.add')"
            @click="create"
          />
        </div>
      </div>
    </template>
  </ICard>
</template>

<script setup>
import { inject, ref } from 'vue'
import { useI18n } from 'vue-i18n'

import { useFieldsForm } from '~/Core/composables/useFieldsForm'
import { useResourceable } from '~/Core/composables/useResourceable'
import { useResourceFields } from '~/Core/composables/useResourceFields'

import CreateFollowUpTask from '~/Activities/components/CreateFollowUpTask.vue'
import { useActivities } from '~/Activities/composables/useActivities'

defineEmits(['cancel'])

const props = defineProps({
  viaResource: { required: true, type: String },
  viaResourceId: { required: true, type: [String, Number] },
  relatedResourceDisplayName: { required: true, type: String },
})

const synchronizeResource = inject('synchronizeResource')
const incrementResourceCount = inject('incrementResourceCount')

const resourceName = Innoclapps.resourceName('calls')

const { t } = useI18n()

const { fields, getCreateFields } = useResourceFields()
const { createFollowUpActivity } = useActivities()
const { createResource } = useResourceable(resourceName)

const { form } = useFieldsForm(fields, {
  task_date: null,
})

const createFollowUpTaskRef = ref(null)

async function handleCallCreated(call) {
  if (form.task_date) {
    let activity = await createFollowUpActivity(
      form.task_date,
      props.viaResource,
      props.viaResourceId,
      props.relatedResourceDisplayName,
      {
        note: t('calls::call.follow_up_task_body', {
          content: call.body,
        }),
      }
    )

    createFollowUpTaskRef.value.reset()

    if (activity) {
      synchronizeResource({ activities: [activity] })
      incrementResourceCount('incomplete_activities_for_user_count')
    }
  }

  synchronizeResource({ calls: [call] })
  incrementResourceCount('calls_count')

  Innoclapps.success(t('calls::call.created'))
  form.reset()
}

function create() {
  createResource(
    form.set(props.viaResource, [props.viaResourceId]).withQueryString({
      via_resource: props.viaResource,
      via_resource_id: props.viaResourceId,
    })
  ).then(handleCallCreated)
}

async function prepareComponent() {
  fields.value.set(
    await getCreateFields(resourceName, {
      viaResource: props.viaResource,
      viaResourceId: props.viaResourceId,
    })
  )
}

prepareComponent()
</script>
