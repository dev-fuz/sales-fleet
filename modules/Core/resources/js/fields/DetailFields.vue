<template>
  <ResizeableFieldsWrapper
    :disabled="!resizeable"
    :collapsed="collapsed"
    :resource-name="resizeable ? resourceName : undefined"
    v-bind="$attrs"
  >
    <div class="grid grid-cols-12 gap-y-1">
      <div
        v-for="field in fields.all()"
        :key="field.attribute"
        :class="[
          field.width === 'half' ? 'col-span-12 sm:col-span-12' : 'col-span-12',
          field.displayNone || (collapsed && field.collapsed)
            ? 'pointer-events-none hidden'
            : '',
        ]"
      >
        <component
          :is="field.detailComponent"
          :field="field"
          :resource-name="resourceName"
          :resource-id="resourceId"
          :resource="resource"
          :is-floating="isFloating"
          @updated="$emit('updated', $event), fields.sync($event)"
        />
      </div>
    </div>
  </ResizeableFieldsWrapper>
</template>

<script setup>
import { watch } from 'vue'

import Fields from '~/Core/fields/Fields'

import ResizeableFieldsWrapper from './ResizeableFieldsWrapper.vue'

defineEmits(['updated'])

const props = defineProps({
  fields: { required: true, type: Fields },
  collapsed: Boolean,
  isFloating: Boolean,
  resizeable: Boolean,
  resourceName: { required: true, type: String },
  resourceId: { required: true, type: Number },
  resource: { required: true, type: Object },
})

defineOptions({
  inheritAttrs: false,
})

watch(
  () => props.resource._sync_timestamp,
  () => {
    props.fields.sync(props.resource)
  }
)
</script>
