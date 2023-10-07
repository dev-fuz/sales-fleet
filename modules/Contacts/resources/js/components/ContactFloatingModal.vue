<template>
  <FieldsCollapseButton
    v-if="fields.hasCollapsable"
    v-model:collapsed="fieldsCollapsed"
    :total="fields.totalCollapsable"
    class="mb-2 ml-auto"
  />

  <FormFields
    v-if="mode === 'edit'"
    :fields="fields"
    :form-id="form.formId"
    :resource-name="resourceName"
    :resource-id="resource.id"
    :collapsed="fieldsCollapsed"
    is-floating
  />

  <DetailFields
    v-else
    :fields="fields"
    :resource-name="resourceName"
    :resource-id="resource.id"
    :resource="resource"
    :collapsed="fieldsCollapsed"
    is-floating
    @updated="synchronizeAndEmitUpdatedEvent($event, true)"
  />

  <div
    class="mt-8 space-y-6 border-t border-neutral-200 pt-8 dark:border-neutral-700 sm:space-y-8"
    :class="{ 'px-10': mode === 'detail' }"
  >
    <ContactCompaniesList
      :float-mode="mode"
      :contact-id="resource.id"
      :companies="resource.companies"
      :authorize-dissociate="resource.authorizations.update"
      :show-create-button="resource.authorizations.update"
      @dissociated="
        fields.sync({ companies: resource.companies }),
          synchronizeAndEmitUpdatedEvent({
            companies: { id: $event, _delete: true },
          })
      "
      @create-requested="companyBeingCreated = true"
    />

    <ContactDealsList
      :float-mode="mode"
      :contact-id="resource.id"
      :deals="resource.deals"
      :authorize-dissociate="resource.authorizations.update"
      :show-create-button="resource.authorizations.update"
      @dissociated="
        fields.sync({ deals: resource.deals }),
          synchronizeAndEmitUpdatedEvent({
            deals: { id: $event, _delete: true },
          })
      "
      @create-requested="dealBeingCreated = true"
    />

    <MediaCard
      :card="false"
      :resource-name="resourceName"
      :resource-id="resource.id"
      :media="resource.media"
      :authorize-delete="resource.authorizations.update"
      is-floating
      @uploaded="synchronizeAndEmitUpdatedEvent({ media: [$event] })"
      @deleted="
        synchronizeAndEmitUpdatedEvent({
          media: { id: $event.id, _delete: true },
        })
      "
    />
  </div>

  <CreateCompanyModal
    v-model:visible="companyBeingCreated"
    :overlay="false"
    :contacts="[resource]"
    @created="handleCompanyCreated"
    @restored="handleCompanyCreated"
  />

  <CreateDealModal
    v-model:visible="dealBeingCreated"
    :overlay="false"
    :contacts="[resource]"
    @created="handleDealCreated"
    @restored="handleDealCreated"
  />
</template>

<script setup>
import { inject, nextTick, ref } from 'vue'

import MediaCard from '~/Core/components/Media/ResourceRecordMediaCard.vue'
import Fields from '~/Core/fields/Fields'
import FieldsCollapseButton from '~/Core/fields/FieldsButtonCollapse.vue'

import ContactCompaniesList from './ContactCompaniesList.vue'
import ContactDealsList from './ContactDealsList.vue'

const props = defineProps({
  resource: Object,
  form: Object,
  fields: Fields,
  mode: String,
})

const resourceName = Innoclapps.resourceName('contacts')

const fieldsCollapsed = ref(true)
const companyBeingCreated = ref(false)
const dealBeingCreated = ref(false)

const synchronizeAndEmitUpdatedEvent = inject('synchronizeAndEmitUpdatedEvent')

function handleCompanyCreated(data) {
  companyBeingCreated.value = false
  synchronizeAndEmitUpdatedEvent({ companies: data.company })
  nextTick(() => props.fields.sync({ companies: props.resource.companies }))
}

function handleDealCreated(data) {
  dealBeingCreated.value = false
  synchronizeAndEmitUpdatedEvent({ deals: data.deal })
  nextTick(() => props.fields.sync({ deals: props.resource.deals }))
}
</script>
