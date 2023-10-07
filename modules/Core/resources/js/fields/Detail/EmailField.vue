<template>
  <DetailFieldsItem
    :field="field"
    :is-floating="isFloating"
    :resource="resource"
    :resource-name="resourceName"
    :resource-id="resourceId"
  >
    <template #field="{ hasValue, value }">
      <IDropdown v-if="hasValue" no-caret>
        <template #toggle="{ toggle }">
          <a
            class="link text-sm"
            :href="'mailto:' + value"
            @click.prevent="toggle"
            v-text="value"
          />
        </template>

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
  </DetailFieldsItem>
</template>

<script setup>
defineProps(['resource', 'resourceName', 'resourceId', 'field', 'isFloating'])
</script>
