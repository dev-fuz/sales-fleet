<template>
  <CreateDealModal
    :title="modalTitle"
    :ok-title="
      hasSelectedExistingDeal
        ? $t('core::app.associate')
        : $t('core::app.create')
    "
    :fields-visible="!hasSelectedExistingDeal"
    :with-extended-submit-buttons="!hasSelectedExistingDeal"
    :create-using="
      createFunc => (hasSelectedExistingDeal ? associate() : createFunc())
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
  </CreateDealModal>
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

const resourceName = Innoclapps.resourceName('deals')

const { t } = useI18n()

const { fields: associateField } = useResourceFields([
  {
    asyncUrl: '/deals/search',
    attribute: 'deals',
    formComponent: 'FormSelectField',
    helpText: t('deals::deal.associate_field_info'),
    helpTextDisplay: 'text',
    label: t('deals::deal.deal'),
    labelKey: 'name',
    valueKey: 'id',
    lazyLoad: { url: '/deals', params: { order: 'created_at|desc' } },
  },
])

const { form: associateForm } = useFieldsForm(associateField)

const hasSelectedExistingDeal = computed(
  () => !!associateField.value.find('deals').currentValue
)

const modalTitle = computed(() => {
  if (!props.viaResource) {
    return t('deals::deal.create')
  }

  if (!hasSelectedExistingDeal.value) {
    return t('deals::deal.create_with', {
      name: props.parentResource.display_name,
    })
  }

  return t('deals::deal.associate_with', {
    name: props.parentResource.display_name,
  })
})

async function associate() {
  await associateForm
    .hydrate()
    .set({ deals: [associateForm.deals] }) // set the value as an array
    .put(`associations/${props.viaResource}/${props.parentResource.id}`)

  emit('associated', associateForm.deals[0])

  Innoclapps.success(t('core::resource.associated'))
}

if (!props.viaResource) {
  usePageTitle(t('deals::deal.create'))
}

function handleReady(fields) {
  if (props.viaResource) {
    fields.value.updateValue(props.viaResource, [props.parentResource])
  }
}
</script>
