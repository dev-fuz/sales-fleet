<template>
  <CreateContactModal
    :title="modalTitle"
    :ok-title="
      hasSelectedExistingContact
        ? $t('core::app.associate')
        : $t('core::app.create')
    "
    :fields-visible="!hasSelectedExistingContact"
    :with-extended-submit-buttons="!hasSelectedExistingContact"
    :create-using="
      createFunc => (hasSelectedExistingContact ? associate() : createFunc())
    "
    @ready="handleReady"
  >
    <template #top="{ isReady }">
      <div
        v-if="viaResource"
        v-show="isReady"
        class="mb-4 rounded-md border border-success-400 px-4 py-3"
      >
        <FormFields
          :fields="associateField"
          :form-id="associateForm.formId"
          :resource-name="resourceName"
        />
      </div>
    </template>
  </CreateContactModal>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

import { useFieldsForm } from '~/Core/composables/useFieldsForm'
import { usePageTitle } from '~/Core/composables/usePageTitle'
import { useResourceFields } from '~/Core/composables/useResourceFields'

const emit = defineEmits(['associated'])

const props = defineProps({
  viaResource: String,
  parentResource: Object,
})

const resourceName = Innoclapps.resourceName('contacts')

const { t } = useI18n()

const { fields: associateField } = useResourceFields([
  {
    asyncUrl: '/contacts/search',
    attribute: 'contacts',
    formComponent: 'FormSelectField',
    helpText: t('contacts::contact.associate_field_info'),
    helpTextDisplay: 'text',
    label: t('contacts::contact.contact'),
    labelKey: 'display_name',
    valueKey: 'id',
    lazyLoad: { url: '/contacts', params: { order: 'created_at|desc' } },
  },
])

const { form: associateForm } = useFieldsForm(associateField)

const hasSelectedExistingContact = computed(
  () => !!associateField.value.find('contacts').currentValue
)

const modalTitle = computed(() => {
  if (!props.viaResource) {
    return t('contacts::contact.create')
  }

  if (!hasSelectedExistingContact.value) {
    return t('contacts::contact.create_with', {
      name: props.parentResource.display_name,
    })
  }

  return t('contacts::contact.associate_with', {
    name: props.parentResource.display_name,
  })
})

async function associate() {
  await associateForm
    .hydrate()
    .set({ contacts: [associateForm.contacts] }) // set the value as an array
    .put(`associations/${props.viaResource}/${props.parentResource.id}`)

  emit('associated', associateForm.contacts[0])

  Innoclapps.success(t('core::resource.associated'))
}

if (!props.viaResource) {
  usePageTitle(t('contacts::contact.create'))
}

function handleReady(fields) {
  if (props.viaResource) {
    fields.value.updateValue(props.viaResource, [props.parentResource])
  }
}
</script>
