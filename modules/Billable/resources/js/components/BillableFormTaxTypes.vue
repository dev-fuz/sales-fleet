<template>
  <div>
    <IFormRadio
      v-for="taxType in formattedTaxTypes"
      :id="key + '-' + taxType.value"
      :key="taxType.value"
      :value="taxType.value"
      :model-value="modelValue"
      name="tax_type"
      :label="taxType.text"
      @change="$emit('update:modelValue', $event)"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

import { randomString } from '@/utils'

defineEmits(['update:modelValue'])

defineProps(['modelValue'])

const { t } = useI18n()
const taxTypes = Innoclapps.config('taxes.types')

// In case included in modal, make sure unique ID is given so the label click works properly
const key = randomString()

const formattedTaxTypes = computed(() => {
  return taxTypes.map(type => ({
    value: type,
    text: t('billable::billable.tax_types.' + type),
  }))
})
</script>
