<template>
  <CompaniesList
    :companies="companies"
    :empty-text="$t('deals::deal.no_companies_associated')"
  >
    <template #actions="{ company }">
      <IButton
        v-show="authorizeDissociate"
        v-i-tooltip.left="$t('contacts::company.dissociate')"
        size="sm"
        variant="white"
        icon="X"
        @click="dissociateCompany(company.id)"
      />
    </template>
    <template #tail>
      <IButton
        v-if="showCreateButton"
        variant="white"
        class="mt-6"
        block
        :text="$t('contacts::company.add')"
        @click="$emit('create-requested')"
      />
    </template>
  </CompaniesList>
</template>

<script setup>
import { inject } from 'vue'
import { useI18n } from 'vue-i18n'

import CompaniesList from '~/Contacts/components/CompaniesList.vue'

const emit = defineEmits(['dissociated', 'create-requested'])

defineProps({
  companies: { required: true, type: Array },
  dealId: { required: true, type: Number },
  authorizeDissociate: { required: true, type: Boolean },
  showCreateButton: { required: true, type: Boolean },
})

const { t } = useI18n()

const detachResourceAssociations = inject('detachResourceAssociations')

async function dissociateCompany(id) {
  await Innoclapps.dialog().confirm()
  await detachResourceAssociations({ companies: [id] })

  emit('dissociated', id)

  Innoclapps.success(t('core::resource.dissociated'))
}
</script>
