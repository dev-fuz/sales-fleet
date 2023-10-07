<template>
  <RelatedFieldResource
    :resource-name="resourceName"
    :with-cancel="false"
    @created="fetchActivityTypes"
    @updated="fetchActivityTypes"
    @deleted="fetchActivityTypes"
  >
    <template #header>
      <ICardHeading>{{ $t('activities::activity.type.types') }}</ICardHeading>
    </template>
  </RelatedFieldResource>
</template>

<script setup>
import RelatedFieldResource from '~/Core/components/SimpleResourceCRUD.vue'

import { useActivityTypes } from '../composables/useActivityTypes'

const resourceName = Innoclapps.resourceName('activity-types')

const { fetchActivityTypes } = useActivityTypes()
</script>
