<template>
  <IModal
    id="readCallOutcomeModal"
    :title="$t('calls.call.outcome.call_outcome')"
    hide-footer
  >
    <!-- eslint-disable-next-line vue/no-v-html -->
    <div v-html="outcomeBeingRead"></div>
  </IModal>
  <CardTableAsync :card="card">
    <template #date="{ formatted, row }">
      <p class="text-neutral-600 dark:text-neutral-300">
        {{ row.user.name }} -
        <span class="font-normal" v-text="formatted"></span>
      </p>
      <div
        v-for="(associations, resourceName) in row.associations"
        :key="resourceName"
      >
        <div
          v-for="association in associations"
          :key="association.id"
          class="flex space-x-2"
        >
          <RouterLink :to="association.path" class="link text-sm font-normal">
            {{ association.display_name }}
          </RouterLink>
        </div>
      </div>
      <a
        class="link mt-1 inline-flex items-center text-sm"
        href="#"
        @click="readOutcome(row.body)"
      >
        <span v-t="'calls::call.read_outcome'"></span>
        <Icon icon="Window" class="ml-2 mt-px h-4 w-4" />
      </a>
    </template>
    <!-- eslint-disable-next-line vue/valid-v-slot -->
    <template #outcome.name="{ row, formatted }">
      <TextBackground
        :color="row.outcome.swatch_color"
        class="inline-flex shrink-0 items-center self-start rounded px-2 py-0.5 font-normal dark:!text-white"
      >
        {{ formatted }}
      </TextBackground>
    </template>
  </CardTableAsync>
</template>

<script setup>
import { ref } from 'vue'

import TextBackground from '~/Core/components/TextBackground.vue'

defineProps({ card: Object })

const outcomeBeingRead = ref(null)

function readOutcome(outcome) {
  outcomeBeingRead.value = outcome
  Innoclapps.modal().show('readCallOutcomeModal')
}
</script>
