<template>
  <div
    style="bottom: 0; right: 0"
    class="h-dropper fixed left-0 border border-neutral-300 bg-white shadow-sm dark:border-neutral-900 dark:bg-neutral-900 sm:left-56"
  >
    <IModal
      id="markAsLostModal"
      size="sm"
      :title="$t('deals::deal.actions.mark_as_lost')"
      form
      :ok-disabled="changeStatusForm.busy"
      :ok-title="$t('deals::deal.actions.mark_as_lost')"
      ok-variant="danger"
      @submit="markAsLost(markingAsLostID)"
      @hidden="markAsLostModalHidden"
    >
      <IFormGroup
        :label="$t('deals::deal.lost_reasons.lost_reason')"
        label-for="lost_reason"
        :optional="!setting('lost_reason_is_required')"
        :required="setting('lost_reason_is_required')"
      >
        <LostReasonField v-model="changeStatusForm.lost_reason" />
        <IFormError v-text="changeStatusForm.getError('lost_reason')" />
      </IFormGroup>
    </IModal>
    <div class="flex justify-end">
      <div
        class="h-dropper relative w-1/3 border-t-2 border-neutral-800 sm:w-1/5"
      >
        <draggable
          :model-value="[]"
          :item-key="item => item.id"
          class="h-dropper dropper-delete dropper"
          :group="{ name: 'delete', put: true, pull: false }"
          async
          @change="movedToDelete"
        >
          <template #item><div></div></template>
          <template #header>
            <div
              v-t="'core::app.delete'"
              class="dropper-header h-dropper absolute inset-0 flex place-content-center items-center font-medium text-neutral-800 dark:text-neutral-200"
            />
          </template>
        </draggable>
      </div>
      <div
        class="h-dropper relative w-1/3 border-t-2 border-danger-500 sm:w-1/5"
      >
        <draggable
          :model-value="[]"
          :item-key="item => item.id"
          class="h-dropper dropper-lost dropper"
          :group="{ name: 'lost', put: true, pull: false }"
          @change="movedToLost"
        >
          <template #item><div></div></template>
          <template #header>
            <div
              v-t="'deals::deal.status.lost'"
              class="dropper-header h-dropper absolute inset-0 flex place-content-center items-center font-medium text-neutral-800 dark:text-neutral-200"
            />
          </template>
        </draggable>
      </div>
      <div
        class="h-dropper relative w-1/3 border-t-2 border-success-500 sm:w-1/5"
      >
        <draggable
          :model-value="[]"
          :item-key="item => item.id"
          class="h-dropper dropper-won dropper"
          :group="{ group: 'won', put: true, pull: false }"
          @change="movedToWon"
        >
          <template #item><div></div></template>
          <template #header>
            <div
              v-t="'deals::deal.status.won'"
              class="dropper-header h-dropper absolute inset-0 flex place-content-center items-center font-medium text-neutral-800 dark:text-neutral-200"
            />
          </template>
        </draggable>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
// https://stackoverflow.com/questions/51619243/vue-draggable-delete-item-by-dragging-into-designated-region
import draggable from 'vuedraggable'

import { throwConfetti } from '@/utils'

import { useApp } from '~/Core/composables/useApp'
import { useForm } from '~/Core/composables/useForm'
import { useResourceable } from '~/Core/composables/useResourceable'

import LostReasonField from './DealLostReasonField.vue'

const emit = defineEmits(['deleted', 'won', 'refresh-requested'])

defineProps({
  resourceId: { required: true },
})

const { setting } = useApp()

const markingAsLostID = ref(null)

const { form: changeStatusForm } = useForm(
  { lost_reason: null },
  { resetOnSuccess: true }
)

const { updateResource, deleteResource } = useResourceable(
  Innoclapps.resourceName('deals')
)

/**
 * Handle deal moved to delete dropper
 */
async function movedToDelete(e) {
  if (e.added) {
    try {
      await Innoclapps.dialog().confirm()
      await deleteResource(e.added.element.id)
      emit('deleted', e.added.element)
    } catch {
      requestRefresh()
    }
  }
}

/**
 * Request board refresh
 */
function requestRefresh() {
  emit('refresh-requested')
}

/**
 * Handle deal moved to lost dropper
 */
function movedToLost(e) {
  if (e.added) {
    markingAsLostID.value = e.added.element.id
    Innoclapps.modal().show('markAsLostModal')
  }
}

/**
 * Handle the mark as lost modal hidden event
 */
function markAsLostModalHidden() {
  changeStatusForm.reset()
  changeStatusForm.errors.clear()
  markingAsLostID.value = null
  requestRefresh()
}

/**
 * Mark the deal as lost
 */
function markAsLost(id) {
  updateResource(changeStatusForm.fill({ status: 'lost' }), id).then(() =>
    Innoclapps.modal().hide('markAsLostModal')
  )
}

/**
 * Mark the deal as lost
 */
function markAsWon(id) {
  updateResource(changeStatusForm.fill({ status: 'won' }), id)
    .then(deal => {
      throwConfetti()
      emit('won', deal)
      requestRefresh()
    })
    .catch(() => requestRefresh())
}

/**
 * Handle deal moved to won dropper
 */
function movedToWon(e) {
  if (e.added) {
    markAsWon(e.added.element.id)
  }
}
</script>

<style>
.h-dropper {
  height: 75px;
}

.dropper .bottom-hidden {
  display: none;
}

.dropper-delete .sortable-chosen.sortable-ghost::before {
  background: black;
  content: ' ';
  min-height: 55px;
  min-width: 100%;
  display: block;
}

.dropper-lost .sortable-chosen.sortable-ghost::before {
  background: red;
  content: ' ';
  min-height: 55px;
  min-width: 100%;
  display: block;
}

.dropper-won .sortable-chosen.sortable-ghost::before {
  background: green;
  content: ' ';
  min-height: 55px;
  min-width: 100%;
  display: block;
}
</style>
