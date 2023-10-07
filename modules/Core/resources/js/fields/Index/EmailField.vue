<template>
  <IndexFieldsItem
    :resource-name="resourceName"
    :resource-id="resourceId"
    :row="row"
    :field="field"
  >
    <template #field="{ hasValue, value }">
      <IDropdown
        v-if="hasValue"
        ref="dropdownRef"
        no-caret
        @show="$emit('show')"
      >
        <template #toggle="{ toggle }">
          <a
            class="link"
            :href="'mailto:' + value"
            @click.prevent="toggle"
            v-text="value"
          />
        </template>
        <slot name="start" :email="value" />
        <IButtonCopy
          :success-message="$t('core::fields.email_copied')"
          :text="value"
          tag="IDropdownItem"
        >
          {{ $t('core::app.copy') }}
        </IButtonCopy>
        <IDropdownItem
          :href="'mailto:' + value"
          :text="$t('core::app.open_in_app')"
        />
      </IDropdown>
      <span v-else>&mdash;</span>
    </template>
  </IndexFieldsItem>
</template>

<script setup>
import { ref } from 'vue'

defineEmits(['show'])

defineProps(['column', 'row', 'field', 'resourceName', 'resourceId'])

const dropdownRef = ref(null)

defineExpose({
  dropdownRef,
})
</script>
