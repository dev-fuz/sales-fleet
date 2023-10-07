<template>
  <ILayout :overlay="!componentReady">
    <div class="mx-auto max-w-7xl">
      <IAlert
        v-if="componentReady && $gate.denies('view', resource)"
        class="mb-6"
        variant="warning"
      >
        {{ $t('core::role.view_non_authorized_after_record_create') }}
      </IAlert>

      <div v-if="componentReady" class="relative">
        <div
          class="overflow-hidden rounded-lg border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900"
        >
          <div class="bg-white px-3 py-4 dark:bg-neutral-900 sm:p-6">
            <div class="flex grow flex-col lg:flex-row lg:items-center">
              <div
                class="overflow-hidden text-center lg:flex lg:grow lg:items-center lg:space-x-4 lg:text-left"
              >
                <div class="overflow-hidden">
                  <div class="lg:flex lg:items-center lg:space-x-4">
                    <h1 class="relative truncate">
                      <span
                        :class="[
                          'text-2xl font-bold text-neutral-900 dark:text-white',
                          {
                            'cursor-pointer rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-700':
                              resource.authorizations.update,
                          },
                        ]"
                        @click="
                          resource.authorizations.update &&
                            $refs.namePopoverRef.show()
                        "
                        v-text="resource.display_name"
                      />

                      <!-- Use popover separately so the truncate class works fine on the h1 tag -->
                      <IPopover
                        v-if="resource.authorizations.update"
                        ref="namePopoverRef"
                        class="w-72"
                        @show="nameForm.name = resource.name"
                        @hide="nameForm.errors.clear()"
                      >
                        <!-- Absolute centering, a tricky way to center the dropdown -->
                        <button
                          type="button"
                          class="hide absolute left-1/2 top-1/2 mt-3 h-0 w-0 -translate-x-1/2 -translate-y-1/2 transform"
                        />
                        <template #popper>
                          <div class="p-4">
                            <IFormGroup
                              required
                              :label="$t('deals::fields.deals.name')"
                              label-for="editDealName"
                            >
                              <component
                                :is="
                                  resource.name.length <= 60
                                    ? 'IFormInput'
                                    : 'IFormTextarea'
                                "
                                id="editDealName"
                                v-model="nameForm.name"
                                class="font-normal"
                                @keydown.enter="updateName"
                                @keydown="nameForm.errors.clear('name')"
                              />

                              <IFormError v-text="nameForm.getError('name')" />
                            </IFormGroup>
                          </div>
                          <div
                            class="flex justify-end space-x-1 bg-neutral-100 px-4 py-3 dark:bg-neutral-900"
                          >
                            <IButton
                              size="sm"
                              variant="white"
                              :disabled="nameForm.busy"
                              :text="$t('core::app.cancel')"
                              @click="() => $refs.namePopoverRef.hide()"
                            />
                            <IButton
                              size="sm"
                              variant="primary"
                              :loading="nameForm.busy"
                              :disabled="nameForm.busy || !nameForm.name"
                              :text="$t('core::app.save')"
                              @click="updateName"
                            />
                          </div>
                        </template>
                      </IPopover>
                    </h1>
                    <div class="inline-block lg:shrink-0">
                      <TagsSelectInput
                        :simple="true"
                        :disabled="!resource.authorizations.update"
                        class="mt-0.5"
                        type="deals"
                        :model-value="resource.tags"
                        @update:model-value="updateResource({ tags: $event })"
                      />
                    </div>
                  </div>
                  <BillableFormProductsModal
                    v-if="resource.authorizations.update"
                    :resource-name="resourceName"
                    :resource-id="resource.id"
                    :visible="manageProducts"
                    prefetch
                    @hidden="manageProducts = false"
                    @saved="
                      synchronizeResource({
                        _sync_timestamp: new Date(),
                        billable: $event,
                        amount: $event.total,
                        products_count: $event.products.length,
                      })
                    "
                  />
                  <div
                    class="my-1 flex flex-col justify-center space-y-2 lg:mb-1 lg:flex-row lg:justify-start lg:space-x-3 lg:space-y-0"
                  >
                    <a
                      v-if="resource.authorizations.update"
                      v-t="{
                        path: 'billable::product.count',
                        args: { count: resource.products_count },
                      }"
                      href="#"
                      class="link order-2 shrink-0 text-sm font-normal sm:order-1"
                      @click.prevent="showProductsDialog"
                    />

                    <span
                      v-else
                      v-t="{
                        path: 'billable::product.count',
                        args: { count: resource.products_count },
                      }"
                      class="order-2 shrink-0 text-sm text-neutral-700 dark:text-neutral-200 sm:order-1"
                    />

                    <DealStagePopover
                      :deal-id="resource.id"
                      :pipeline="resource.pipeline"
                      :stage-id="resource.stage_id"
                      :status="resource.status"
                      :authorized-to-update="resource.authorizations.update"
                      class="order-1 shrink-0 justify-center text-sm font-medium text-neutral-800 hover:text-neutral-600 dark:text-neutral-200 dark:hover:text-neutral-400 sm:order-2 lg:justify-start"
                      @updated="synchronizeResource($event, true)"
                    />
                  </div>
                  <p
                    v-once
                    class="text-sm text-neutral-600 dark:text-neutral-300"
                  >
                    {{ $t('core::app.created_at') }}
                    {{ localizedDateTime(resource.created_at) }}
                  </p>
                </div>
              </div>
              <div
                class="ml-6 flex shrink-0 flex-col items-center lg:flex-row lg:space-x-3"
              >
                <DealStatusChange
                  v-if="resource.authorizations.update"
                  class="mr-3 mt-5 lg:mt-0 lg:shrink-0"
                  :deal-id="resource.id"
                  :deal-status="resource.status"
                  @updated="synchronizeResource($event, true)"
                />

                <div
                  class="mt-5 flex shrink-0 justify-center space-x-3 lg:mt-0 lg:items-center lg:justify-normal"
                >
                  <UserOwnerDropdown
                    :owner="resource.user"
                    :authorize-update="resource.authorizations.update"
                    @change="updateResource({ user_id: $event.id })"
                  />

                  <ActionSelector
                    type="dropdown"
                    :ids="resource.id || []"
                    :actions="resource.actions || []"
                    :resource-name="resourceName"
                    @run="handleActionExecuted"
                  />
                </div>
              </div>
            </div>

            <DealMiniPipeline
              :stages="stagesForMiniPipeline"
              :time-in-stages="resource.time_in_stages"
              :deal-status="resource.status"
              :deal-stage-id="resource.stage_id"
              :deal-id="resource.id"
              class="mt-5"
              @stage-updated="synchronizeResource"
            />
          </div>
        </div>

        <div
          v-if="$gate.isSuperAdmin()"
          v-once
          class="absolute -top-5 right-2 rotate-90 lg:-right-6 lg:top-1.5 lg:rotate-0"
        >
          <IMinimalDropdown placement="bottom-end">
            <IDropdownItem
              :text="$t('core::app.record_view.manage_sidebar')"
              @click="sidebarBeingManaged = true"
            />
          </IMinimalDropdown>
        </div>
      </div>

      <div v-if="componentReady" class="mt-8">
        <div class="lg:grid lg:grid-cols-12 lg:gap-8">
          <div class="col-span-4 space-y-3">
            <div
              v-for="section in enabledSections"
              v-show="!sidebarBeingManaged"
              :key="section.id"
            >
              <component
                :is="
                  Object.hasOwn(sectionComponents, section.component)
                    ? sectionComponents[section.component]
                    : section.component
                "
                :resource-name="resourceName"
                :resource-id="resource.id"
                :resource="resource"
                @updated="synchronizeResource($event, true)"
              />
            </div>
            <ManageViewSections
              v-model:sections="template.sections"
              v-model:show="sidebarBeingManaged"
              :identifier="resourceSingularName"
              class="-mt-3 inline"
              @saved="sidebarBeingManaged = false"
            />
          </div>

          <div class="col-span-8 mt-4 lg:mt-0">
            <ITabGroup :default-index="defaultTabIndex">
              <ITabList
                centered
                :bordered="false"
                list-wrapper-class="rounded-md border border-neutral-200 bg-white py-0.5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900"
              >
                <component
                  :is="
                    Object.hasOwn(tabComponents, tab.component)
                      ? tabComponents[tab.component]
                      : tab.component
                  "
                  v-for="tab in template.tabs"
                  :key="tab.id"
                  :resource-name="resourceName"
                  :resource-id="resource.id"
                  :resource="resource"
                />
              </ITabList>
              <ITabPanels>
                <component
                  :is="
                    Object.hasOwn(tabComponents, tab.panelComponent)
                      ? tabComponents[tab.panelComponent]
                      : tab.panelComponent
                  "
                  v-for="tab in template.tabs"
                  :id="'tabPanel-' + tab.id"
                  :key="tab.id"
                  :resource-name="resourceName"
                  :resource-id="resource.id"
                  :resource="resource"
                  scroll-element="#main"
                />
              </ITabPanels>
            </ITabGroup>
          </div>
        </div>
      </div>
    </div>

    <!-- Contact, Company Create -->
    <router-view
      v-if="componentReady"
      :via-resource="resourceName"
      :parent-resource="resource"
      :go-to-list="false"
      @associated="fetchResource(), $router.back()"
      @created="
        ({ isRegularAction }) => (
          isRegularAction ? $router.back() : '', fetchResource()
        )
      "
      @modal-hidden="$router.back"
    />
  </ILayout>
</template>

<script setup>
import { computed, provide, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import ActionSelector from '~/Core/components/Actions/ActionSelector.vue'
import ManageViewSections from '~/Core/components/ManageViewSections.vue'
import TagsSelectInput from '~/Core/components/TagsSelectInput.vue'
import { usePrivateChannel } from '~/Core/composables/useBroadcast'
import { useDates } from '~/Core/composables/useDates'
import { useForm } from '~/Core/composables/useForm'
import { useGlobalEventListener } from '~/Core/composables/useGlobalEventListener'
import { usePageTitle } from '~/Core/composables/usePageTitle'
import { useResource } from '~/Core/composables/useResource'
import TimelineTab from '~/Core/views/Timeline/RecordTabTimeline.vue'
import TimelineTabPanel from '~/Core/views/Timeline/RecordTabTimelinePanel.vue'

import BillableFormProductsModal from '~/Billable/components/BillableFormProductsModal.vue'
import UserOwnerDropdown from '~/Users/components/UserOwnerDropdown.vue'

import DealStagePopover from '../components/DealStagePopover.vue'
import DealStatusChange from '../components/DealStatusChange.vue'
import CompaniesSection from '../components/DealsViewCompanies.vue'
import ContactsSection from '../components/DealsViewContacts.vue'
import DetailsSection from '../components/DealsViewDetails.vue'
import MediaSection from '../components/DealsViewMedia.vue'
import DealMiniPipeline from '../components/MiniPipeline/DealMiniPipeline.vue'

const tabComponents = {
  'timeline-tab': TimelineTab,
  'timeline-tab-panel': TimelineTabPanel,
}

const sectionComponents = {
  'details-section': DetailsSection,
  'contacts-section': ContactsSection,
  'companies-section': CompaniesSection,
  'media-section': MediaSection,
}

const resourceName = Innoclapps.resourceName('deals')

const router = useRouter()
const route = useRoute()
const { localizedDateTime } = useDates()
const { form: nameForm } = useForm({ name: null })

const namePopoverRef = ref(null)
const sidebarBeingManaged = ref(false)
const manageProducts = ref(false)
const template = ref(Innoclapps.resource('deals').pages.detail)

const defaultTabIndex = route.query.section
  ? template.value.tabs.findIndex(tab => tab.id === route.query.section)
  : 0

let dealId = computed(() => route.params.id)

const {
  resource,
  resourceSingularName,
  synchronizeResource,
  detachResourceAssociations,
  incrementResourceCount,
  decrementResourceCount,
  fetchResource,
  updateResource,
  resourceReady: componentReady,
} = useResource(resourceName, dealId)

const channelName = computed(() =>
  resource.value?.authorizations?.view
    ? `Modules.Deals.Models.Deal.${dealId.value}`
    : null
)

useGlobalEventListener('refresh-details-view', () => fetchResource())

useGlobalEventListener('floating-resource-updated', () => fetchResource())

const enabledSections = computed(() =>
  template.value.sections.filter(section => section.enabled === true)
)

const stagesForMiniPipeline = computed(() =>
  resource.value.pipeline.stages.map(({ id, name }) => ({ id, name }))
)

provide('synchronizeResource', synchronizeResource)
provide('detachResourceAssociations', detachResourceAssociations)
provide('incrementResourceCount', incrementResourceCount)
provide('decrementResourceCount', decrementResourceCount)

usePageTitle(computed(() => resource.value.display_name))
usePrivateChannel(channelName, '.DealUpdated', () => fetchResource())

function showProductsDialog() {
  manageProducts.value = true
}

function handleActionExecuted(action) {
  if (!action.destroyable) {
    fetchResource()
  } else {
    router.push({ name: 'deal-index' })
  }
}

function updateName() {
  updateResource(nameForm).then(() => namePopoverRef.value.hide())
}

fetchResource()
</script>
