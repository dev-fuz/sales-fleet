<template>
  <ILayout>
    <import
      :resource-name="resourceName"
      :request-query-string="{ pipeline_id: pipeline.id }"
    >
      <template #header>
        <ICardHeading class="flex items-center">
          <i18n-t scope="global" keypath="deals::deal.import_in">
            <template #pipeline>
              <div class="ml-3 sm:w-1/5">
                <ICustomSelect
                  v-model="pipeline"
                  :clearable="false"
                  label="name"
                  :options="pipelines"
                />
              </div>
            </template>
          </i18n-t>
        </ICardHeading>
      </template>
    </import>
  </ILayout>
</template>

<script setup>
import { shallowRef } from 'vue'

import Import from '~/Core/components/Import'

import { usePipelines } from '../composables/usePipelines'

const resourceName = Innoclapps.resourceName('deals')

const { orderedPipelines: pipelines } = usePipelines()

const pipeline = shallowRef(pipelines.value[0])
</script>
