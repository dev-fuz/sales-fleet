<template>
  <div
    v-if="swatchColor"
    class="bottom-hidden p-2"
    :style="{ background: swatchColor }"
  />
  <div
    class="bottom-hidden group px-4 py-2"
    :class="{
      'bg-danger-50/50': fallsBehindExpectedCloseDate,
      'bg-success-50/70 dark:bg-success-500/30': status == 'won',
      'opacity-50': status == 'lost',
    }"
  >
    <div class="flex">
      <div class="grow truncate">
        <a
          class="truncate text-sm font-medium text-neutral-900 hover:text-neutral-500 focus:outline-none dark:text-white"
          :href="path"
          @click.prevent="
            floatResourceInDetailMode({
              resourceName,
              resourceId: dealId,
            })
          "
          v-text="displayName"
        />
        <div class="mt-1 flex text-sm">
          <IDropdown>
            <template #toggle="{ toggle }">
              <button
                type="button"
                :class="{
                  'text-warning-500': incompleteActivitiesCount > 0,
                  'text-success-500': incompleteActivitiesCount === 0,
                }"
                @click="toggle"
                v-text="
                  $t('activities::activity.count', incompleteActivitiesCount, {
                    count: incompleteActivitiesCount,
                  })
                "
              />
            </template>

            <div
              v-if="nextActivityDate"
              class="border-b border-neutral-200 dark:border-neutral-700"
            >
              <div class="px-4 py-3 text-xs">
                <p class="text-neutral-500 dark:text-neutral-300">
                  {{ $t('activities.activity.next_activity_date') }}
                </p>
                <p class="font-medium text-neutral-700 dark:text-neutral-100">
                  {{ localizedDateTime(nextActivityDate) }}
                </p>
              </div>
            </div>

            <IDropdownItem
              icon="Clock"
              :text="$t('activities::activity.create')"
              @click="$emit('create-activity-requested', dealId)"
            />
          </IDropdown>

          <div class="relative">
            <BillableFormProductsModal
              v-if="manageProducts"
              :resource-name="resourceName"
              :resource-id="dealId"
              visible
              prefetch
              @saved="handleBillableModelSavedEvent"
              @hidden="manageProducts = false"
            />

            <IPopover v-model:visible="showAmountUpdatePopover" class="w-80">
              <a
                href="#"
                class="ml-2 text-neutral-600 hover:text-neutral-900 dark:text-neutral-300 dark:hover:text-neutral-100"
                @click.prevent.stop="handleDealAmountEdit"
                v-text="formatMoney(amount)"
              />
              <template #popper>
                <div class="p-4">
                  <IFormGroup
                    required
                    :label="$t('deals::fields.deals.amount')"
                    label-for="editDealAmount"
                  >
                    <IFormNumericInput
                      id="editDealAmount"
                      v-model="updateForm.amount"
                    />

                    <IFormError v-text="updateForm.getError('amount')" />
                  </IFormGroup>

                  <a
                    href="#"
                    class="link flex items-center text-sm"
                    @click.prevent="
                      (manageProducts = true), (showAmountUpdatePopover = false)
                    "
                  >
                    <span v-t="'billable::product.manage'"></span>
                    <Icon icon="Window" class="ml-2 mt-px h-4 w-4" />
                  </a>
                </div>
                <div
                  class="flex justify-end space-x-1 bg-neutral-100 px-4 py-3 dark:bg-neutral-900"
                >
                  <IButton
                    size="sm"
                    variant="white"
                    :disabled="updateForm.busy"
                    :text="$t('core::app.cancel')"
                    @click="showAmountUpdatePopover = false"
                  />
                  <IButton
                    size="sm"
                    variant="primary"
                    :loading="updateForm.busy"
                    :disabled="updateForm.busy"
                    :text="$t('core::app.save')"
                    @click="updateDeal"
                  />
                </div>
              </template>
            </IPopover>
          </div>
        </div>
        <p
          v-show="expectedCloseDate"
          class="mt-1 text-xs text-neutral-700 dark:text-neutral-300"
          v-text="localizedDate(expectedCloseDate)"
        />
      </div>
      <div class="mt-1 flex flex-col items-center space-y-1">
        <IMinimalDropdown>
          <IDropdownItem
            icon="Clock"
            :text="$t('activities::activity.create')"
            @click="$emit('create-activity-requested', dealId)"
          />

          <IDropdownItem
            icon="Eye"
            :text="$t('core::app.view_record')"
            @click="$router.push(path)"
          />

          <IDropdownItem
            icon="Bars3CenterLeft"
            :text="$t('core::app.preview')"
            @click="preview(dealId)"
          />
        </IMinimalDropdown>

        <IPopover class="w-56" auto-placement>
          <IButtonIcon
            icon="ColorSwatch"
            class="opacity-100 md:opacity-0 md:group-hover:opacity-100"
          />

          <template #popper>
            <div class="p-4">
              <IColorSwatch
                :model-value="swatchColor"
                :swatches="swatches"
                :allow-custom="false"
                @input="$emit('update:swatchColor', $event)"
              />
            </div>
          </template>
        </IPopover>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

import { useAccounting } from '~/Core/composables/useAccounting'
import { useDates } from '~/Core/composables/useDates'
import { useFloatingResourceModal } from '~/Core/composables/useFloatingResourceModal'
import { useForm } from '~/Core/composables/useForm'
import { useResourceable } from '~/Core/composables/useResourceable'

import BillableFormProductsModal from '~/Billable/components/BillableFormProductsModal.vue'

const emit = defineEmits([
  'create-activity-requested',
  'update:amount',
  'update:productsCount',
  'update:swatchColor',
])

const props = defineProps({
  dealId: { required: true, type: Number },
  displayName: { required: true, type: String },
  amount: { required: true, type: [String, Number] },
  path: { required: true, type: String },
  status: { required: true, type: String },
  incompleteActivitiesCount: { required: true, type: Number },
  productsCount: { required: true, type: Number },
  expectedCloseDate: String,
  nextActivityDate: String,
  swatchColor: String,
  fallsBehindExpectedCloseDate: Boolean,
})

const resourceName = Innoclapps.resourceName('deals')

const manageProducts = ref(false)
const showAmountUpdatePopover = ref(false)
const swatches = Innoclapps.config('favourite_colors')

const { localizedDate, localizedDateTime } = useDates()
const { formatMoney } = useAccounting()
const { floatResourceInDetailMode } = useFloatingResourceModal()

const { form: updateForm } = useForm({
  amount: props.amount,
})
const { updateResource } = useResourceable(resourceName)

function updateDeal() {
  updateResource(updateForm, props.dealId).then(deal => {
    emit('update:amount', deal.amount)
    updateForm.set('amount', deal.amount)
    showAmountUpdatePopover.value = false
  })
}

function handleDealAmountEdit() {
  if (props.productsCount > 0) {
    initiateProductsModal()
  } else {
    showAmountUpdatePopover.value = !showAmountUpdatePopover.value
  }
}

async function handleBillableModelSavedEvent(billable) {
  emit('update:amount', billable.total)
  updateForm.set('amount', billable.total)
  emit('update:productsCount', billable.products.length)
}

function initiateProductsModal() {
  manageProducts.value = true
}
</script>
