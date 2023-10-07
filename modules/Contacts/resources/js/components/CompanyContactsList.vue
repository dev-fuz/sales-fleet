<template>
  <ContactsList
    :contacts="contacts"
    :empty-text="$t('contacts::company.no_contacts_associated')"
  >
    <template #actions="{ contact }">
      <IButton
        v-show="authorizeDissociate"
        v-i-tooltip.left="$t('contacts::contact.dissociate')"
        size="sm"
        variant="white"
        icon="X"
        @click="dissociateContact(contact.id)"
      />
    </template>
    <template #tail>
      <IButton
        v-if="showCreateButton"
        variant="white"
        class="mt-6"
        block
        :text="$t('contacts::contact.add')"
        @click="$emit('create-requested')"
      />
    </template>
  </ContactsList>
</template>

<script setup>
import { inject } from 'vue'
import { useI18n } from 'vue-i18n'

import ContactsList from './ContactsList.vue'

const emit = defineEmits(['dissociated', 'create-requested'])

defineProps({
  contacts: { required: true, type: Array },
  companyId: { required: true, type: Number },
  authorizeDissociate: { required: true, type: Boolean },
  showCreateButton: { required: true, type: Boolean },
})

const detachResourceAssociations = inject('detachResourceAssociations')

const { t } = useI18n()

async function dissociateContact(id) {
  await Innoclapps.dialog().confirm()
  await detachResourceAssociations({ contacts: [id] })

  emit('dissociated', id)

  Innoclapps.success(t('core::resource.dissociated'))
}
</script>
