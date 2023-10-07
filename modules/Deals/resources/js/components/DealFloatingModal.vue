<template>
  <div>
    <div
      class="mb-7 rounded-md border px-4 py-3 md:px-5"
      :class="{
        'border-primary-200': resource.status === 'open',
        'border-danger-200 bg-danger-50': resource.status === 'lost',
        'border-success-200 bg-success-50': resource.status === 'won',
      }"
    >
      <div class="flex flex-col items-center md:flex-row">
        <DealStagePopover
          class="shrink-0 text-neutral-800 hover:text-neutral-600 dark:text-neutral-200 dark:hover:text-neutral-400 md:mr-3"
          :deal-id="resource.id"
          :pipeline="resource.pipeline"
          :stage-id="resource.stage_id"
          :status="resource.status"
          :authorized-to-update="resource.authorizations.update"
          @updated="synchronizeAndEmitUpdatedEvent($event, true)"
        />
        <p
          class="block text-sm text-neutral-500 md:hidden"
          v-text="beenInStageText"
        />
        <DealStatusChange
          class="my-2 md:my-0 md:ml-auto"
          :deal-id="resource.id"
          :deal-status="resource.status"
          @updated="synchronizeAndEmitUpdatedEvent($event, true)"
        />
      </div>
      <p
        class="-mt-1.5 hidden text-center text-sm text-neutral-500 dark:text-neutral-300 md:block md:text-left"
        v-text="beenInStageText"
      />
    </div>

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
      <DealContactsList
        :float-mode="mode"
        :deal-id="resource.id"
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

      <DealCompaniesList
        :float-mode="mode"
        :deal-id="resource.id"
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
    :deals="[resource]"
    @created="handleContactCreated"
    @restored="handleContactCreated"
  />

  <CreateCompanyModal
    v-model:visible="companyBeingCreated"
    :overlay="false"
    :deals="[resource]"
    @created="handleCompanyCreated"
    @restored="handleCompanyCreated"
  />
</template>

<script setup>
import { computed, inject, nextTick, onBeforeUnmount, ref } from 'vue'
import { useI18n } from 'vue-i18n'

import MediaCard from '~/Core/components/Media/ResourceRecordMediaCard.vue'
import { useDates } from '~/Core/composables/useDates'
import Fields from '~/Core/fields/Fields'
import FieldsCollapseButton from '~/Core/fields/FieldsButtonCollapse.vue'

import DealCompaniesList from './DealCompaniesList.vue'
import DealContactsList from './DealContactsList.vue'
import DealStagePopover from './DealStagePopover.vue'
import DealStatusChange from './DealStatusChange.vue'

const props = defineProps({
  resource: Object,
  form: Object,
  fields: Fields,
  mode: String,
})

const resourceName = Innoclapps.resourceName('deals')

const fieldsCollapsed = ref(true)
const companyBeingCreated = ref(false)
const contactBeingCreated = ref(false)

const setModalSubTitle = inject('setModalSubTitle')
const synchronizeAndEmitUpdatedEvent = inject('synchronizeAndEmitUpdatedEvent')

const { t } = useI18n()
const { localizedDateTime } = useDates()

setModalSubTitle(
  `${t('core::app.created_at')} ${localizedDateTime(props.resource.created_at)}`
)

onBeforeUnmount(() => {
  setModalSubTitle(null)
})

const beenInStageText = computed(() => {
  const duration = moment.duration({
    seconds: props.resource.time_in_stages[props.resource.stage.id],
  })

  return t('deals::deal.been_in_stage_time', {
    time: duration.humanize(),
  })
})

function handleCompanyCreated(data) {
  companyBeingCreated.value = false
  synchronizeAndEmitUpdatedEvent({ companies: data.company })
  nextTick(() => props.fields.sync({ companies: props.resource.companies }))
}

function handleContactCreated(data) {
  contactBeingCreated.value = false
  synchronizeAndEmitUpdatedEvent({ contacts: data.contact })
  nextTick(() => props.fields.sync({ contacts: props.resource.contacts }))
}
</script>
