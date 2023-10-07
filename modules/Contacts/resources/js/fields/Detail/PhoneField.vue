<template>
  <DetailFieldItem :field="field">
    <template #field="{ hasValue, value }">
      <template v-if="hasValue">
        <IDropdown
          v-for="(phone, index) in value"
          :key="index"
          no-caret
          :text="phone.number + (index != totalPhoneNumbers - 1 ? ', ' : '')"
        >
          <template #toggle="{ toggle }">
            <a
              v-i-tooltip="$t('contacts::fields.phone.types.' + phone.type)"
              class="link text-sm"
              :href="'tel:' + phone.number"
              @click.prevent="toggle"
              v-text="phone.number"
            />
          </template>
          <IButtonCopy
            :text="phone.number"
            :success-message="$t('contacts::fields.phone.copied')"
            tag="IDropdownItem"
          >
            {{ $t('core::app.copy') }}
          </IButtonCopy>
          <IDropdownItem
            :href="'tel:' + phone.number"
            :text="$t('core::app.open_in_app')"
          />
        </IDropdown>
      </template>
      <span v-else>&mdash;</span>
    </template>
  </DetailFieldItem>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps(['resource', 'resourceName', 'resourceId', 'field'])

const totalPhoneNumbers = computed(() => props.field.value.length)
</script>
