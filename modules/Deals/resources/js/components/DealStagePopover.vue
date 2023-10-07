<template>
  <div class="flex items-center">
    <div v-if="authorizedToUpdate">
      <IPopover
        v-if="status !== 'lost'"
        v-model:visible="popoverVisible"
        class="w-72"
        @show="handleStagePopoverShowEvent"
      >
        <button
          type="button"
          class="flex flex-wrap items-center justify-center focus:outline-none md:flex-nowrap md:justify-start"
        >
          <span>{{ dealPipeline.name }}</span>
          <Icon icon="ChevronRight" class="h-4 w-4" /> {{ dealStage.name }}
          <Icon icon="ChevronDown" class="ml-1.5 hidden h-4 w-4 md:block" />
        </button>

        <template #popper>
          <div class="p-4">
            <ICustomSelect
              v-model="selectPipeline"
              :options="pipelines"
              :clearable="false"
              class="mb-2"
              label="name"
              @option:selected="handlePipelineChangedEvent"
              @update:model-value="
                form.errors.clear('pipeline_id'), form.errors.clear('stage_id')
              "
            />

            <IFormError v-text="form.getError('pipeline_id')" />

            <ICustomSelect
              v-model="selectPipelineStage"
              :options="selectPipeline ? selectPipeline.stages : []"
              :clearable="false"
              label="name"
              @update:model-value="form.errors.clear('stage_id')"
            />

            <IFormError v-text="form.getError('stage_id')" />
          </div>
          <div
            class="flex justify-end space-x-1 bg-neutral-100 px-6 py-3 dark:bg-neutral-900"
          >
            <IButton
              size="sm"
              variant="white"
              :disabled="form.busy"
              :text="$t('core::app.cancel')"
              @click="popoverVisible = false"
            />
            <IButton
              size="sm"
              variant="primary"
              :text="$t('core::app.save')"
              :loading="form.busy"
              :disabled="form.busy || !selectPipelineStage"
              @click="saveStageChange"
            />
          </div>
        </template>
      </IPopover>
      <div
        v-else
        class="flex items-center text-neutral-800 dark:text-neutral-200"
      >
        {{ dealPipeline.name }} <Icon icon="ChevronRight" class="h-4 w-4" />
        {{ dealStage.name }}
      </div>
    </div>
    <div
      v-else
      class="flex items-center text-neutral-800 dark:text-neutral-200"
    >
      {{ dealPipeline.name }} <Icon icon="ChevronRight" class="h-4 w-4" />
      {{ dealStage.name }}
    </div>
    <slot></slot>
  </div>
</template>

<script setup>
import { computed, ref, shallowRef } from 'vue'

import { useForm } from '~/Core/composables/useForm'
import { useResourceable } from '~/Core/composables/useResourceable'

import { usePipelines } from '../composables/usePipelines'

const emit = defineEmits(['updated'])

const props = defineProps({
  dealId: { required: true, type: Number },
  pipeline: { required: true, type: Object }, // use directly from deal in case the pipeline is hidden from the current user
  stageId: { required: true, type: Number },
  status: { required: true, type: String },
  authorizedToUpdate: { required: true, type: Boolean },
})

const { orderedPipelines: pipelines } = usePipelines()
const { form } = useForm()
const { updateResource } = useResourceable(Innoclapps.resourceName('deals'))

const dealPipeline = computed(() => props.pipeline)

const dealStage = computed(
  () => props.pipeline.stages.filter(stage => stage.id == props.stageId)[0]
)

const popoverVisible = ref(false)
const selectPipeline = shallowRef(null)
const selectPipelineStage = shallowRef(null)

async function saveStageChange() {
  let updatedDeal = await updateResource(
    form.fill({
      pipeline_id: selectPipeline.value.id,
      stage_id: selectPipelineStage.value.id,
    }),
    props.dealId
  )

  emit('updated', updatedDeal)
  popoverVisible.value = false
}

function handleStagePopoverShowEvent() {
  selectPipeline.value = dealPipeline.value
  selectPipelineStage.value = dealStage.value
}

function handlePipelineChangedEvent(value) {
  if (value.id != props.pipeline.id) {
    // Use the first stage selected from the new pipeline
    selectPipelineStage.value = value.stages[0] || null
  } else if (value.id === props.pipeline.id) {
    // revent back to the original stage after the user select new stage
    // and goes back to the original without saving
    selectPipelineStage.value = dealStage.value
  }
}
</script>
