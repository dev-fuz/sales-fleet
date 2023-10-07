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
        class="font-semibold text-neutral-800 dark:text-neutral-200"
        v-text="sectionHeading"
      />
    </template>
    <template #actions>
      <div class="inline-flex space-x-2">
        <IButtonIcon
          v-show="canEditSection"
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
    <div
      v-show="!editing"
      class="text-sm text-neutral-900 dark:text-neutral-300"
    >
      <!-- eslint-disable-next-line vue/no-v-html -->
      <p v-html="section.label"></p>
    </div>
    <div v-if="editing">
      <IFormGroup
        :label="$t('webforms::form.sections.field.resourceName')"
        label-for="resourceName"
      >
        <ICustomSelect
          v-model="resourceName"
          label="label"
          field-id="resourceName"
          :clearable="false"
          :options="availableResources"
          :reduce="resource => resource.id"
        />
      </IFormGroup>
      <IFormGroup :label="$t('core::fields.label')">
        <Editor
          v-model="label"
          :with-image="false"
          default-tag="div"
          toolbar="bold italic underline link removeformat"
        />
      </IFormGroup>
      <IFormGroup>
        <IFormCheckbox
          id="is-required"
          v-model:checked="isRequired"
          name="is-required"
          :label="$t('core::fields.is_required')"
        />
        <IFormCheckbox
          id="file-multiple"
          v-model:checked="multiple"
          name="file-multiple"
          :label="$t('webforms::form.sections.file.multiple')"
        />
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
          :disabled="saveIsDisabled"
          :text="$t('core::app.save')"
          variant="secondary"
          @click="requestSectionSave"
        />
      </div>
    </div>
  </ICard>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import find from 'lodash/find'

const emit = defineEmits([
  'update-section-requested',
  'remove-section-requested',
])

const props = defineProps({
  index: { type: Number },
  form: { type: Object, required: true },
  section: { required: true, type: Object },
  availableResources: { required: true },
})

const { t } = useI18n()

const editing = ref(false)
const label = ref(null)
const isRequired = ref(false)
const resourceName = ref(null)
const multiple = ref(false)

const canEditSection = computed(() => !editing.value)

const resourceSingularLabel = computed(() => {
  return find(props.availableResources, {
    id: props.section.resourceName,
  }).label
})

const sectionHeading = computed(
  () =>
    resourceSingularLabel.value +
    ' | ' +
    (props.section.multiple
      ? t('webforms::form.sections.file.files')
      : t('webforms::form.sections.file.file')) +
    (!props.section.isRequired ? ' ' + t('core::fields.optional') : '')
)

const saveIsDisabled = computed(() => label.value === null || label.value == '')

function requestSectionSave() {
  emit('update-section-requested', {
    resourceName: resourceName.value,
    label: label.value,
    isRequired: isRequired.value,
    multiple: multiple.value,
  })

  editing.value = false
}

function requestSectionRemove() {
  emit('remove-section-requested')
}

function setEditingMode() {
  resourceName.value = props.section.resourceName
  label.value = props.section.label
  isRequired.value = props.section.isRequired
  multiple.value = props.section.multiple

  editing.value = true
}
</script>
