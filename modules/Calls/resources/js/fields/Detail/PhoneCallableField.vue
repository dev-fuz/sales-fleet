<template>
  <IModal
    v-model:visible="logCallModalIsVisible"
    :title="$t('calls::call.add')"
    size="md"
    :ok-title="$t('calls::call.add')"
    :ok-disabled="form.busy"
    :cancel-title="$t('core::app.cancel')"
    @shown="logCallModalIsVisible = true"
    @hidden="logCallModalIsVisible = false"
    @ok="logCall"
  >
    <!-- re-render the fields as it's causing issue with the tinymce editor
                 on second time the editor has no proper height -->
    <div v-if="logCallModalIsVisible">
      <IOverlay :show="callFields.isEmpty()">
        <FormFields
          :fields="callFields"
          :form-id="form.formId"
          :resource-name="callsResourceName"
        />
      </IOverlay>
      <CreateFollowUpTask v-model="form.task_date" class="mt-2" />
    </div>
  </IModal>
  <DetailFieldsItem
    :field="field"
    :resource="resource"
    :resource-name="resourceName"
    :resource-id="resourceId"
    :is-floating="isFloating"
    @updated="$emit('updated', $event)"
  >
    <template #field="{ hasValue, value }">
      <template v-if="hasValue">
        <IDropdown
          v-for="(phone, index) in value"
          :key="index"
          no-caret
          :text="phone.number + (index != totalPhoneNumbers - 1 ? ', ' : '')"
        >
          <template #toggle="{ toggle }">
            <a
              v-i-tooltip="$t('contacts::fields.phone.types.' + phone.type)"
              class="link text-sm"
              :href="'tel:' + phone.number"
              @click.prevent="toggle"
              v-text="phone.number"
            />
          </template>

          <span
            v-if="!isFloating"
            v-i-tooltip="isCallingDisabled ? callDropdownTooltip : null"
          >
            <IDropdownItem
              :disabled="isCallingDisabled"
              :text="$t('calls::call.make')"
              @click="initiateNewCall(phone.number)"
            />
          </span>

          <IButtonCopy
            :text="phone.number"
            :success-message="$t('contacts::fields.phone.copied')"
            tag="IDropdownItem"
          >
            {{ $t('core::app.copy') }}
          </IButtonCopy>

          <IDropdownItem
            :href="'tel:' + phone.number"
            :text="$t('core::app.open_in_app')"
          />
        </IDropdown>
      </template>
      <span v-if="!hasValue">&mdash;</span>
    </template>
  </DetailFieldsItem>
</template>

<script setup>
import { computed, inject, ref } from 'vue'
import { useI18n } from 'vue-i18n'

import { useFieldsForm } from '~/Core/composables/useFieldsForm'
import { useGate } from '~/Core/composables/useGate'
import { useResourceable } from '~/Core/composables/useResourceable'
import { useResourceFields } from '~/Core/composables/useResourceFields'

import CreateFollowUpTask from '~/Activities/components/CreateFollowUpTask.vue'
import { useActivities } from '~/Activities/composables/useActivities'
import { useVoip } from '~/Calls/composables/useVoip'

defineEmits(['updated'])

const props = defineProps([
  'resource',
  'resourceName',
  'resourceId',
  'field',
  'isFloating',
])

const callsResourceName = Innoclapps.resourceName('calls')
const synchronizeResource = inject('synchronizeResource', null)
const incrementResourceCount = inject('incrementResourceCount', null)

const logCallModalIsVisible = ref(false)

const { t } = useI18n()
const { gate } = useGate()
const { voip, hasVoIPClient } = useVoip()
const { fields: callFields, getCreateFields } = useResourceFields()

const { form } = useFieldsForm(callFields, {
  task_date: null,
})
const { createResource } = useResourceable(callsResourceName)
const { createFollowUpActivity } = useActivities()

const isCallingDisabled = computed(
  () => !hasVoIPClient || !gate.userCan('use voip')
)

const callDropdownTooltip = computed(() => {
  if (!hasVoIPClient) {
    return t('core::app.integration_not_configured')
  } else if (gate.userCant('use voip')) {
    return t('calls::call.no_voip_permissions')
  }

  return ''
})

const totalPhoneNumbers = computed(() => props.field.value.length)

async function handleCallCreated(call) {
  if (form.task_date) {
    let activity = await createFollowUpActivity(
      form.task_date,
      props.resourceName,
      props.resourceId,
      props.resource.display_name,
      {
        note: t('calls::call.follow_up_task_body', {
          content: call.body,
        }),
      }
    )

    if (activity) {
      if (synchronizeResource) {
        synchronizeResource({ activities: [activity] })
      }

      if (incrementResourceCount) {
        incrementResourceCount('incomplete_activities_for_user_count')
      }
    }
  }

  if (synchronizeResource) {
    synchronizeResource({ calls: [call] })
  }

  if (incrementResourceCount) {
    incrementResourceCount('calls_count')
  }

  Innoclapps.success(t('calls::call.created'))
  form.reset()
  logCallModalIsVisible.value = false
}

async function initiateNewCall(phoneNumber) {
  form.set('task_date', null)

  let call = await voip.makeCall(phoneNumber)

  call.on('Disconnect', () => {
    logCallModalIsVisible.value = true
  })

  callFields.value.set(
    await getCreateFields(callsResourceName, {
      viaResource: props.resourceName,
      viaResourceId: props.resourceId,
    })
  )
}

function logCall() {
  createResource(
    form.set(props.resourceName, [props.resourceId]).withQueryString({
      via_resource: props.resourceName,
      via_resource_id: props.resourceId,
    })
  ).then(handleCallCreated)
}
</script>
