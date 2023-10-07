<template>
  <div class="sm:flex sm:items-start">
    <div
      v-if="!hasFields"
      class="mx-auto flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-danger-100 sm:mx-0 sm:h-10 sm:w-10"
    >
      <Icon icon="ExclamationTriangle" class="h-6 w-6 text-danger-600" />
    </div>
    <div
      :class="[
        { 'text-center sm:ml-4': !hasFields },
        'mt-3 w-full sm:mt-0 sm:text-left',
      ]"
    >
      <DialogTitle
        as="h3"
        :class="{ 'mt-2': !showMessage }"
        class="text-lg/6 font-medium text-neutral-600 dark:text-white"
      >
        {{ dialog.title }}
      </DialogTitle>

      <div v-if="showMessage" class="mt-2">
        <p class="text-sm text-neutral-500 dark:text-neutral-300">
          {{ dialog.message }}
        </p>
      </div>

      <FormFields
        v-if="hasFields"
        class="mt-4"
        :fields="dialog.fields"
        :form-id="form.formId"
        is-floating
      />
    </div>
  </div>

  <div class="mt-5 sm:mt-4 sm:flex" :class="{ 'sm:ml-10 sm:pl-4': !hasFields }">
    <IButton
      :variant="dialog.action.destroyable ? 'danger' : 'secondary'"
      :disabled="form.busy"
      :loading="form.busy"
      :text="$t('core::app.confirm')"
      class="w-full sm:w-auto"
      @click="runAction"
    />
    <IButton
      variant="white"
      class="mt-2 w-full sm:ml-3 sm:mt-0 sm:w-auto"
      :text="$t('core::app.cancel')"
      @click="cancel"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { DialogTitle } from '@headlessui/vue'

import { useFieldsForm } from '~/Core/composables/useFieldsForm'

const props = defineProps({
  close: Function,
  cancel: Function,
  dialog: { type: Object, required: true },
})

const { form } = useFieldsForm([], { ids: [] })

const hasFields = computed(
  () => props.dialog.fields && props.dialog.fields.isNotEmpty()
)

const showMessage = computed(() => !hasFields.value && props.dialog.message)

async function runAction() {
  hasFields.value && props.dialog.fields.fill(form)

  const data = await form
    .fill('ids', props.dialog.ids)
    .post(`${props.dialog.endpoint}`, {
      params: props.dialog.queryString,
    })

  props.dialog.resolve({
    form: form,
    response: data,
  })

  props.close()
}
</script>
