<template>
  <DealsList
    :deals="deals"
    :empty-text="$t('contacts::company.no_deals_associated')"
  >
    <template #actions="{ deal }">
      <IButton
        v-show="authorizeDissociate"
        v-i-tooltip.left="$t('deals::deal.dissociate')"
        size="sm"
        variant="white"
        icon="X"
        @click="dissociateDeal(deal.id)"
      />
    </template>

    <template #tail>
      <IButton
        v-if="showCreateButton"
        variant="white"
        class="mt-6"
        block
        :text="$t('deals::deal.add')"
        @click="$emit('create-requested')"
      />
    </template>
  </DealsList>
</template>

<script setup>
import { inject } from 'vue'
import { useI18n } from 'vue-i18n'

import DealsList from '~/Deals/components/DealsList.vue'

const emit = defineEmits(['dissociated', 'create-requested'])

defineProps({
  deals: { required: true, type: Array },
  companyId: { required: true, type: Number },
  authorizeDissociate: { required: true, type: Boolean },
  showCreateButton: { required: true, type: Boolean },
})

const detachResourceAssociations = inject('detachResourceAssociations')

const { t } = useI18n()

async function dissociateDeal(id) {
  await Innoclapps.dialog().confirm()
  await detachResourceAssociations({ deals: [id] })

  emit('dissociated', id)

  Innoclapps.success(t('core::resource.dissociated'))
}
</script>
