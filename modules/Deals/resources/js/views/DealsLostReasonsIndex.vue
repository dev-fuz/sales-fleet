<template>
  <RelatedFieldResource
    :resource-name="resourceName"
    :with-cancel="false"
    @created="fetchLostReasons"
    @updated="fetchLostReasons"
    @deleted="fetchLostReasons"
  >
    <template #header>
      <ICardHeading>
        {{ $t('deals::deal.lost_reasons.lost_reasons') }}
      </ICardHeading>
    </template>
  </RelatedFieldResource>
</template>

<script setup>
import RelatedFieldResource from '~/Core/components/SimpleResourceCRUD.vue'

import { useLostReasons } from '../composables/useLostReasons'

const resourceName = Innoclapps.resourceName('lost-reasons')

const { fetchLostReasons } = useLostReasons()
</script>
