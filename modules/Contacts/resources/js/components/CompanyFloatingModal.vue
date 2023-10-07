<template>
  <div>
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
      <CompanyContactsList
        :float-mode="mode"
        :company-id="resource.id"
        :contacts="resource.contacts"
        :authorize-dissociate="resource.authorizations.update"
        :show-create-button="resource.authorizations.update"
        @dissociated="
          fields.sync({ contacts: resource.contacts }),
            synchronizeAndEmitUpdatedEvent({
              contacts: { id: $event, _delete: true },
            })
        "
        @create-requested="contactBeingCreated = true"
      />

      <CompanyDealsList
        :float-mode="mode"
        :company-id="resource.id"
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
  </div>

  <CreateContactModal
    v-model:visible="contactBeingCreated"
    :overlay="false"
    :companies="[resource]"
    @created="handleContactCreated"
    @restored="handleContactCreated"
  />

  <CreateDealModal
    v-model:visible="dealBeingCreated"
    :overlay="false"
    :companies="[resource]"
    @created="handleDealCreated"
    @restored="handleDealCreated"
  />
</template>

<script setup>
import { inject, nextTick, ref } from 'vue'

import MediaCard from '~/Core/components/Media/ResourceRecordMediaCard.vue'
import Fields from '~/Core/fields/Fields'
import FieldsCollapseButton from '~/Core/fields/FieldsButtonCollapse.vue'

import CompanyContactsList from './CompanyContactsList.vue'
import CompanyDealsList from './CompanyDealsList.vue'

const props = defineProps({
  resource: Object,
  form: Object,
  fields: Fields,
  mode: String,
})

const resourceName = Innoclapps.resourceName('companies')

const fieldsCollapsed = ref(true)
const contactBeingCreated = ref(false)
const dealBeingCreated = ref(false)

const synchronizeAndEmitUpdatedEvent = inject('synchronizeAndEmitUpdatedEvent')

function handleContactCreated(data) {
  contactBeingCreated.value = false
  synchronizeAndEmitUpdatedEvent({ contacts: data.contact })
  nextTick(() => props.fields.sync({ contacts: props.resource.contacts }))
}

function handleDealCreated(data) {
  dealBeingCreated.value = false
  synchronizeAndEmitUpdatedEvent({ deals: data.deal })
  nextTick(() => props.fields.sync({ deals: props.resource.deals }))
}
</script>
