<template>
  <IndexFieldsItem
    :resource-name="resourceName"
    :resource-id="resourceId"
    :row="row"
    :field="field"
  >
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
              class="link"
              :href="'tel:' + phone.number"
              @click.prevent="toggle"
              v-text="phone.number"
            />
          </template>

          <slot name="start" :phone="phone" />

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
  </IndexFieldsItem>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps([
  'column',
  'row',
  'field',
  'resourceName',
  'resourceId',
])

const totalPhoneNumbers = computed(
  () => props.row[props.column.attribute].length
)
</script>
