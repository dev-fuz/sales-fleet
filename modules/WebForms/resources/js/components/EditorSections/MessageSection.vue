<template>
  <ICard
    class="group"
    :class="{
      'border border-primary-400': editing,
      'border border-transparent transition duration-75 hover:border-primary-400 dark:border dark:border-neutral-700':
        !editing,
    }"
  >
    <template #header>
      <p
        v-t="'webforms::form.sections.message.message'"
        class="font-semibold text-neutral-800 dark:text-neutral-200"
      />
    </template>

    <template #actions>
      <div class="inline-flex space-x-2">
        <IButtonIcon
          v-show="!editing"
          icon="PencilAlt"
          class="block md:hidden md:group-hover:block"
          icon-class="h-4 w-4"
          @click="setEditingMode"
        />
        <IButtonIcon
          icon="Trash"
          class="block md:hidden md:group-hover:block"
          icon-class="h-4 w-4"
          @click="requestSectionRemove"
        />
      </div>
    </template>
    <!-- eslint-disable -->
    <div
      v-show="!editing"
      class="text-sm text-neutral-600 dark:text-neutral-200"
      v-html="section.message"
    />
    <!-- eslint-enable -->

    <div v-if="editing">
      <IFormGroup :label="$t('webforms::form.sections.message.message')">
        <Editor v-model="message" :with-image="false" />
      </IFormGroup>
      <div class="space-x-2 text-right">
        <IButton
          size="sm"
          variant="white"
          :text="$t('core::app.cancel')"
          @click="editing = false"
        />
        <IButton
          size="sm"
          variant="secondary"
          :text="$t('core::app.save')"
          @click="requestSectionSave"
        />
      </div>
    </div>
  </ICard>
</template>

<script setup>
import { ref } from 'vue'

const emit = defineEmits([
  'update-section-requested',
  'remove-section-requested',
])

const props = defineProps({
  index: { type: Number },
  form: { type: Object, required: true },
  section: { required: true, type: Object },
})

defineOptions({
  inheritAttrs: false,
})

const editing = ref(false)
const message = ref(null)

function requestSectionSave() {
  emit('update-section-requested', {
    message: message.value,
  })

  editing.value = false
}

function requestSectionRemove() {
  emit('remove-section-requested')
}

function setEditingMode() {
  message.value = props.section.message
  editing.value = true
}
</script>
