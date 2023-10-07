<template>
  <ISlideover
    id="editActivityModal"
    :ok-disabled="form.busy || !resource.authorizations?.update"
    :ok-title="$t('core::app.save')"
    :hide-footer="activeTabIndex === 1"
    :title="resource.title"
    :sub-title="modalDescription"
    :initial-focus="modalCloseElement"
    visible
    form
    static-backdrop
    @submit="performUpdate"
    @hidden="handleModalHidden"
    @shown="handleModalShown"
  >
    <template #title="{ title }">
      <div class="flex items-center">
        <ActivityStateChange
          v-if="componentReady"
          class="mr-1 mt-0.5"
          tooltip-placement="bottom"
          :is-completed="resource.is_completed"
          :disabled="!resource.authorizations.changeState"
          :activity-id="resource.id"
          @state-changed="synchronizeResource"
        />
        <span>
          {{ title }}
        </span>
      </div>
    </template>
    <div class="absolute -top-2 right-5 sm:top-4">
      <ActionSelector
        v-if="fields.isNotEmpty()"
        type="dropdown"
        :ids="actionId"
        :actions="actions"
        :resource-name="resourceName"
        @run="handleActionExecuted"
      />
    </div>
    <FieldsPlaceholder v-if="fields.isEmpty()" />
    <div v-else class="mt-6 sm:mt-0">
      <ITabGroup v-model="activeTabIndex">
        <ITabList>
          <ITab :title="$t('activities::activity.activity')" />
          <ITab
            :title="
              $t('comments::comment.comments') +
              ' (' +
              resource.comments_count +
              ')'
            "
            @activated.once="loadComments"
          />
        </ITabList>
        <ITabPanels>
          <ITabPanel>
            <FormFields
              :fields="fields"
              :form-id="form.formId"
              :resource-id="computedId"
              :resource-name="resourceName"
            >
              <template #after-deals-field>
                <span class="-mt-2 block text-right">
                  <a
                    href="#"
                    class="link text-sm"
                    @click.prevent="dealBeingCreated = true"
                  >
                    &plus; {{ $t('deals::deal.create') }}
                  </a>
                </span>
              </template>

              <template #after-companies-field>
                <span class="-mt-2 block text-right">
                  <a
                    href="#"
                    class="link text-sm"
                    @click.prevent="companyBeingCreated = true"
                  >
                    &plus; {{ $t('contacts::company.create') }}
                  </a>
                </span>
              </template>

              <template #after-contacts-field>
                <span class="-mt-2 block text-right">
                  <a
                    href="#"
                    class="link text-sm"
                    @click.prevent="contactBeingCreated = true"
                  >
                    &plus; {{ $t('contacts::contact.create') }}
                  </a>
                </span>
              </template>
            </FormFields>

            <MediaCard
              class="mt-5"
              :resource-name="resourceName"
              :resource-id="resource.id"
              :media="resource.media"
              :authorize-delete="resource.authorizations.update"
              @deleted="
                synchronizeResource({ media: { id: $event.id, _delete: true } })
              "
              @uploaded="synchronizeResource({ media: [$event] })"
            />
          </ITabPanel>
          <ITabPanel lazy>
            <div class="my-3 text-right">
              <CommentsAdd
                :commentable-id="resource.id"
                commentable-type="activities"
                @created="
                  incrementResourceCount('comments_count'),
                    synchronizeResource({
                      comments: [$event],
                    })
                "
              />
            </div>

            <IOverlay :show="!commentsLoaded">
              <CommentsList
                v-if="commentsLoaded"
                :comments="resource.comments || []"
                commentable-type="activities"
                :commentable-id="resource.id"
                :auto-focus-if-required="true"
                @updated="
                  synchronizeResource({
                    comments: $event,
                  })
                "
                @deleted="
                  decrementResourceCount('comments_count'),
                    synchronizeResource({
                      comments: { id: $event, _delete: true },
                    })
                "
              />
            </IOverlay>
          </ITabPanel>
        </ITabPanels>
      </ITabGroup>
    </div>
    <CreateDealModal
      v-model:visible="dealBeingCreated"
      :overlay="false"
      @created="
        fields.mergeValue('deals', $event.deal), (dealBeingCreated = false)
      "
    />
    <CreateContactModal
      v-model:visible="contactBeingCreated"
      :overlay="false"
      @created="
        fields.mergeValue('contacts', $event.contact),
          (contactBeingCreated = false)
      "
      @restored="
        fields.mergeValue('contacts', $event), (contactBeingCreated = false)
      "
    />
    <CreateCompanyModal
      v-model:visible="companyBeingCreated"
      :overlay="false"
      @created="
        fields.mergeValue('companies', $event.company),
          (companyBeingCreated = false)
      "
      @restored="
        fields.mergeValue('companies', $event), (companyBeingCreated = false)
      "
    />
  </ISlideover>
</template>

<script setup>
import { computed, provide, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute, useRouter } from 'vue-router'
import { computedWithControl } from '@vueuse/shared'

import ActionSelector from '~/Core/components/Actions/ActionSelector.vue'
import MediaCard from '~/Core/components/Media/ResourceRecordMediaCard.vue'
import { useDates } from '~/Core/composables/useDates'
import { useFieldsForm } from '~/Core/composables/useFieldsForm'
import { useResource } from '~/Core/composables/useResource'
import { useResourceFields } from '~/Core/composables/useResourceFields'

import { useComments } from '~/Comments/composables/useComments'

import ActivityStateChange from '../components/ActivityStateChange.vue'

const props = defineProps({
  onHidden: Function,
  onActionExecuted: Function,
  id: Number,
})

const resourceName = Innoclapps.resourceName('activities')

const { t } = useI18n()
const router = useRouter()
const route = useRoute()
const { fields, getUpdateFields } = useResourceFields()
const { form } = useFieldsForm(fields)
const { localizedDateTime } = useDates()

// TODO, should use comments loaded from useComments?
const commentsLoaded = ref(false)

const dealBeingCreated = ref(false)
const contactBeingCreated = ref(false)
const companyBeingCreated = ref(false)

// Provide initial focus element as the modal can be nested and it's not
// finding an element for some reason when the second modal is closed
// showing error "There are no focusable elements inside the <FocusTrap />"
const modalCloseElement = computedWithControl(
  () => null,
  () => document.querySelector('#modalClose-editActivityModal')
)

const activeTabIndex = ref(route.query.comment_id ? 1 : 0)

const modalDescription = computed(() => {
  if (!componentReady.value) {
    return null
  }

  return `${t('core::app.created_at')}: ${localizedDateTime(
    resource.value.created_at
  )} - ${resource.value.creator.name}`
})

const actionId = computed(() => resource.value.id || [])
const actions = computed(() => resource.value.actions || [])
const computedId = computed(() => Number(props.id || route.params.id))

const {
  resource,
  fetchResource,
  synchronizeResource,
  incrementResourceCount,
  decrementResourceCount,
  updateResource,
} = useResource(resourceName, computedId)

// For comments
provide('synchronizeResource', synchronizeResource)
provide('incrementResourceCount', incrementResourceCount)
provide('decrementResourceCount', decrementResourceCount)

const componentReady = computed(() => fields.value.isNotEmpty())

const { getAllComments } = useComments(computedId.value, resourceName)

function loadComments() {
  getAllComments().then(comments => {
    synchronizeResource({ comments })
    commentsLoaded.value = true
  })
}

function handleActionExecuted(action) {
  if (props.onActionExecuted) {
    props.onActionExecuted(action)

    return
  }

  // Reload the record data on any action executed except delete
  // If we try to reload on delete will throw 404 error
  if (!action.destroyable) {
    prepareComponent()
  } else {
    router.push({ name: 'activity-index' })
  }
}

function handleModalShown() {
  modalCloseElement.trigger()
}

function handleModalHidden() {
  props.onHidden ? props.onHidden() : router.back()
}

async function performUpdate() {
  await updateResource(form)

  Innoclapps.success(t('core::resource.updated'))
}

async function prepareComponent() {
  await fetchResource()

  fields.value
    .set(await getUpdateFields(resourceName, computedId.value))
    .populate(resource.value)
}

prepareComponent()
</script>
