<template>
  <div class="sm:flex sm:items-start">
    <div class="mt-3 w-full sm:mt-0 sm:text-left">
      <DialogTitle
        as="h3"
        class="text-lg/6 font-medium text-neutral-600 dark:text-white"
        v-text="dialog.title"
      />

      <div class="mt-4">
        <FieldsCollapseButton
          v-if="fields.hasCollapsable"
          v-model:collapsed="fieldsCollapsed"
          :total="fields.totalCollapsable"
          class="mb-3 ml-auto"
        />

        <FormFields
          :fields="fields"
          :collapsed="fieldsCollapsed"
          :form-id="form.formId"
          is-floating
        >
          <template
            v-for="field in fields.all()"
            #[field.beforeFieldSlotName]
            :key="field.attribute"
          >
            <div
              v-if="field.attribute"
              :class="[
                field.displayNone || (field.collapsed && fieldsCollapsed)
                  ? 'hidden'
                  : '',
                fieldsBeingUpdated[field.attribute] === replaceKey
                  ? '-mb-2'
                  : '',
              ]"
            >
              <div class="mb-3">
                <IFormLabel
                  class="mb-1"
                  :for="field.attribute"
                  :required="
                    fieldsBeingUpdated[field.attribute] === replaceKey &&
                    field.isRequired
                  "
                  :label="field.label"
                />

                <ICustomSelect
                  v-model="fieldsBeingUpdated[field.attribute]"
                  :reduce="option => option.value"
                  :input-id="field.attribute"
                  :clearable="false"
                  :options="[
                    {
                      value: keepKey,
                      label: $t('core::fields.keep_existing_value'),
                    },
                    {
                      value: replaceKey,
                      label: $t('core::fields.replace_existing_value'),
                    },
                  ]"
                />
              </div>
            </div>
          </template>
        </FormFields>
      </div>
    </div>
  </div>

  <div class="mt-5 sm:mt-4 sm:flex">
    <IButton
      variant="secondary"
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
import { reactive, ref, watch } from 'vue'
import { DialogTitle } from '@headlessui/vue'

import { useFieldsForm } from '~/Core/composables/useFieldsForm'
import FieldsCollapseButton from '~/Core/fields/FieldsButtonCollapse.vue'

const props = defineProps({
  close: Function,
  cancel: Function,
  dialog: { type: Object, required: true },
})

const keepKey = 'keep'
const replaceKey = 'replace'
const fieldsCollapsed = ref(true)
const fieldsBeingUpdated = reactive({})
const fields = ref(props.dialog.fields)

prepareFieldsForBulkEdit()

const { form } = useFieldsForm(fields, { ids: [] })

watch(
  fieldsBeingUpdated,
  newVal => {
    Object.entries(newVal).forEach(([attribute, value]) => {
      fields.value.update(attribute, { hidden: value === keepKey })
    })
  },
  {
    deep: true,
  }
)

async function runAction() {
  try {
    const data = await form
      .clear()
      .hydrate()
      .fill('ids', props.dialog.ids)
      .post(`${props.dialog.endpoint}`, {
        params: props.dialog.queryString,
      })

    props.dialog.resolve({
      form: form,
      response: data,
    })

    props.close()
  } catch (e) {
    // Show any fields that the user choosed to keep
    // but there are validation errors related to them.
    showHiddenFieldsWithErrors()

    throw e
  }
}

function prepareFieldsForBulkEdit() {
  fields.value.forEach(field => {
    // field.collapsed = false
    field.hidden = true
    field.hideLabel = true
    field.toggleable = false
    field.value = null // no default value for bulk edit fields
    field.beforeFieldSlotName = 'before-' + field.attribute + '-field'

    if (field.attribute) {
      fieldsBeingUpdated[field.attribute] = keepKey
    }
  })
}

function showHiddenFieldsWithErrors() {
  fields.value.forEach(field => {
    if (
      form.errors.has(field.attribute) &&
      fieldsBeingUpdated[field.attribute] === keepKey
    ) {
      fieldsBeingUpdated[field.attribute] = replaceKey
    }
  })
}
</script>
