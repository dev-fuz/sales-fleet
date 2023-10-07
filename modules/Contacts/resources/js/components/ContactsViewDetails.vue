<template>
  <IOverlay
    :show="!componentReady"
    :class="{
      'rounded-lg bg-white p-6 shadow dark:bg-neutral-900': !componentReady,
    }"
  >
    <div
      v-show="componentReady"
      class="overflow-hidden rounded-lg border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900"
    >
      <div class="flex items-center justify-between px-6 py-3">
        <div class="inline-flex items-center">
          <span
            v-if="fields.requiredFieldsMissingValues"
            class="mr-2 block h-2 w-2 rounded-full bg-danger-500"
          />

          <h2
            v-t="'core::app.record_view.sections.details'"
            class="font-medium text-neutral-800 dark:text-white"
          />
        </div>

        <div class="flex items-center space-x-3">
          <IButtonIcon
            size="sm"
            icon="PencilAlt"
            icon-class="h-[1.03rem] w-[1.03rem]"
            @click="floatResourceInEditMode({ resourceName, resourceId })"
          />

          <IButtonIcon
            v-if="$gate.isSuperAdmin()"
            v-i-tooltip="$t('core::fields.manage')"
            size="sm"
            :to="{
              name: 'resource-fields',
              params: { resourceName },
              query: { view: fieldsViewName },
            }"
            icon="AdjustmentsVertical"
            icon-class="h-[1.1rem] w-[1.1rem]"
          />

          <FieldsCollapseButton
            v-if="fields.hasCollapsable"
            v-model:collapsed="fieldsCollapsed"
            :total="fields.totalCollapsable"
          />
        </div>
      </div>

      <DetailFields
        v-if="componentReady"
        v-bind="$attrs"
        class="overflow-y-auto px-3 py-4"
        resizeable
        initial-height-class="h-[10rem]"
        :collapsed="fieldsCollapsed"
        :fields="fields"
        :resource-name="resourceName"
        :resource-id="resourceId"
        :resource="resource"
      />
    </div>
  </IOverlay>
</template>

<script setup>
import { computed, ref } from 'vue'

import { useFloatingResourceModal } from '~/Core/composables/useFloatingResourceModal'
import { useResourceFields } from '~/Core/composables/useResourceFields'
import FieldsCollapseButton from '~/Core/fields/FieldsButtonCollapse.vue'

const props = defineProps({
  resourceName: { required: true, type: String },
  resourceId: { required: true, type: [String, Number] },
  resource: { required: true, type: Object },
})

defineOptions({ inheritAttrs: false })

const fieldsViewName = Innoclapps.config('fields.views.detail')
const fieldsCollapsed = ref(true)

const { fields, getDetailFields } = useResourceFields()
const { floatResourceInEditMode } = useFloatingResourceModal()

getDetailFields(props.resourceName, props.resource.id).then(detailFields =>
  fields.value.set(detailFields).populate(props.resource)
)

const componentReady = computed(() => fields.value.isNotEmpty())
</script>
