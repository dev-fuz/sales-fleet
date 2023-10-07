<template>
  <ICard :overlay="fields.isEmpty()">
    <FormFields
      :fields="fields"
      :form-id="form.formId"
      :resource-name="resourceName"
      :resource-id="callId"
    />
    <template #footer>
      <div class="flex justify-end space-x-2">
        <IButton
          variant="white"
          size="sm"
          :text="$t('core::app.cancel')"
          @click="$emit('cancelled')"
        />
        <IButton
          variant="primary"
          size="sm"
          :disabled="form.busy"
          :text="$t('core::app.save')"
          @click="update"
        />
      </div>
    </template>
  </ICard>
</template>

<script setup>
import { inject } from 'vue'
import { useI18n } from 'vue-i18n'

import { useFieldsForm } from '~/Core/composables/useFieldsForm'
import { useResourceable } from '~/Core/composables/useResourceable'
import { useResourceFields } from '~/Core/composables/useResourceFields'

const emit = defineEmits(['updated', 'cancelled'])

const props = defineProps({
  callId: { required: true, type: Number },
  viaResource: { required: true, type: String },
  viaResourceId: { required: true, type: [String, Number] },
})

const synchronizeResource = inject('synchronizeResource')

const resourceName = Innoclapps.resourceName('calls')

const { t } = useI18n()

const { fields, getUpdateFields } = useResourceFields()
const { form } = useFieldsForm(fields)
const { updateResource, retrieveResource } = useResourceable(resourceName)

function update() {
  updateResource(
    form.withQueryString({
      via_resource: props.viaResource,
      via_resource_id: props.viaResourceId,
    }),
    props.callId
  ).then(handleCallUpdated)
}

function handleCallUpdated(updatedCall) {
  synchronizeResource({ calls: updatedCall })

  emit('updated', updatedCall)

  Innoclapps.success(t('calls::call.updated'))
}

async function prepareComponent() {
  const call = await retrieveResource(props.callId)

  fields.value
    .set(
      await getUpdateFields(resourceName, props.callId, {
        viaResource: props.viaResource,
        viaResourceId: props.viaResourceId,
      })
    )
    .populate(call)
}

prepareComponent()
</script>
