<template>
  <CreateCompanyModal
    :title="modalTitle"
    :ok-title="
      hasSelectedExistingCompany
        ? $t('core::app.associate')
        : $t('core::app.create')
    "
    :fields-visible="!hasSelectedExistingCompany"
    :with-extended-submit-buttons="!hasSelectedExistingCompany"
    :create-using="
      createFunc => (hasSelectedExistingCompany ? associate() : createFunc())
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
  </CreateCompanyModal>
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

const resourceName = Innoclapps.resourceName('companies')

const { t } = useI18n()

const { fields: associateField } = useResourceFields([
  {
    asyncUrl: '/companies/search',
    attribute: 'companies',
    formComponent: 'FormSelectField',
    helpText: t('contacts::company.associate_field_info'),
    helpTextDisplay: 'text',
    label: t('contacts::company.company'),
    labelKey: 'name',
    valueKey: 'id',
    lazyLoad: { url: '/companies', params: { order: 'created_at|desc' } },
  },
])

const { form: associateForm } = useFieldsForm(associateField)

const hasSelectedExistingCompany = computed(
  () => !!associateField.value.find('companies').currentValue
)

const modalTitle = computed(() => {
  if (!props.viaResource) {
    return t('contacts::company.create')
  }

  if (!hasSelectedExistingCompany.value) {
    return t('contacts::company.create_with', {
      name: props.parentResource.display_name,
    })
  }

  return t('contacts::company.associate_with', {
    name: props.parentResource.display_name,
  })
})

async function associate() {
  await associateForm
    .hydrate()
    .set({ companies: [associateForm.companies] }) // set the value as an array
    .put(`associations/${props.viaResource}/${props.parentResource.id}`)

  emit('associated', associateForm.companies[0])

  Innoclapps.success(t('core::resource.associated'))
}

if (!props.viaResource) {
  usePageTitle(t('contacts::company.create'))
}

function handleReady(fields) {
  if (props.viaResource) {
    fields.value.updateValue(props.viaResource, [props.parentResource])
  }
}
</script>
