<template>
  <div>
    <IModal
      id="createRecord"
      size="sm"
      static-backdrop
      :ok-title="$t('core::app.create')"
      :ok-disabled="form.busy"
      form
      :title="$t('core::resource.create', { resource: singularLabel })"
      @submit="create"
      @keydown="form.onKeydown($event)"
      @hidden="handleModalHiddenEvent"
    >
      <FormFields
        :fields="fields"
        :form-id="form.formId"
        :resource-name="resourceName"
        is-floating
        focus-first
      />
    </IModal>
    <IModal
      id="updateRecord"
      size="sm"
      static-backdrop
      :ok-title="$t('core::app.save')"
      :ok-disabled="form.busy"
      form
      :title="$t('core::resource.edit', { resource: singularLabel })"
      @hidden="handleModalHiddenEvent"
      @submit="update"
      @keydown="form.onKeydown($event)"
    >
      <FormFields
        :fields="fields"
        :form-id="form.formId"
        :resource-name="resourceName"
        :resource-id="form.id"
        is-floating
      />
    </IModal>
    <ICard no-body>
      <template #header>
        <IButtonMinimal
          v-show="withCancel"
          variant="info"
          :text="$t('core::app.go_back')"
          @click="requestCancel"
        />

        <slot name="header"></slot>
      </template>
      <template #actions>
        <IButton
          icon="plus"
          size="sm"
          :text="$t('core::resource.create', { resource: singularLabel })"
          @click="prepareCreate"
        />
      </template>
      <TableSimple
        ref="tableRef"
        :table-props="{ shadow: false, ...tableProps }"
        :table-id="resourceName"
        :request-uri="resourceName"
        sort-by="name"
        :fields="columns"
      >
        <template #name="{ row }">
          <div class="flex justify-between">
            <a
              href="#"
              class="link"
              @click.prevent="prepareEdit(row.id)"
              v-text="row.name"
            />
            <IMinimalDropdown>
              <IDropdownItem
                :text="$t('core::app.edit')"
                icon="PencilAlt"
                @click="prepareEdit(row.id)"
              />

              <span
                v-i-tooltip="
                  row.is_primary
                    ? $t('core::resource.primary_record_delete_info', {
                        resource: singularLabel,
                      })
                    : null
                "
              >
                <IDropdownItem
                  :disabled="row.is_primary"
                  icon="Trash"
                  :text="$t('core::app.delete')"
                  @click="destroy(row.id)"
                />
              </span>
            </IMinimalDropdown>
          </div>
        </template>
      </TableSimple>
    </ICard>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'

import TableSimple from '~/Core/components/Table/Simple/TableSimple.vue'
import { useApp } from '~/Core/composables/useApp'
import { useFieldsForm } from '~/Core/composables/useFieldsForm'
import { useResourceFields } from '~/Core/composables/useResourceFields'

import { useResourceable } from '../composables/useResourceable'

const emit = defineEmits(['cancel', 'updated', 'created', 'deleted'])

const props = defineProps({
  resourceName: { required: true, type: String },
  withCancel: { type: Boolean, default: true },
  tableProps: {
    type: Object,
    default() {
      return {}
    },
  },
})

const { t } = useI18n()
const { resetStoreState } = useApp()

const { fields, getCreateFields, getUpdateFields } = useResourceFields()
const { form } = useFieldsForm(fields)

const { updateResource, createResource, retrieveResource, deleteResource } =
  useResourceable(props.resourceName)

const columns = ref([
  {
    key: 'id',
    label: t('core::app.id'),
    sortable: true,
  },
  {
    key: 'name',
    label: t('core::fields.label'),
    sortable: true,
  },
])

const tableRef = ref(null)

const singularLabel = Innoclapps.config(
  `resources.${props.resourceName}.singularLabel`
)

function handleModalHiddenEvent() {
  form.reset()
  fields.value.set([])
}

/**
 * Request cancel edit
 */
function requestCancel() {
  emit('cancel')
}

/**
 * Prepare resource record create
 */
async function prepareCreate() {
  let createFields = await getCreateFields(props.resourceName)
  columns.value[1].key = createFields[0].attribute

  fields.value.set(createFields)

  Innoclapps.modal().show('createRecord')
}

/**
 * Prepare the resource record edit
 */
async function prepareEdit(id) {
  let updateFields = await getUpdateFields(props.resourceName, id)
  let resource = await retrieveResource(id)

  columns.value[1].key = updateFields[0].attribute
  form.fill('id', id)

  fields.value.set(updateFields).populate(resource)

  Innoclapps.modal().show('updateRecord')
}

/**
 * Store resource record in storage
 */
async function create() {
  await createResource(form)

  actionExecuted('created')
  Innoclapps.modal().hide('createRecord')
}

/**
 * Update resource record in storage
 */
async function update() {
  await updateResource(form, form.id)

  actionExecuted('updated')
  Innoclapps.modal().hide('updateRecord')
}

/**
 * Remove resource record from storage
 */
async function destroy(id) {
  await Innoclapps.dialog().confirm()
  await deleteResource(id)

  actionExecuted('deleted')
}

/**
 * Handle action executed
 */
function actionExecuted(action) {
  Innoclapps.success(t('core::resource.' + action))
  tableRef.value.reload()
  resetStoreState()
  emit(action)
}
</script>

<style scoped>
::v-deep(table thead th:first-child) {
  width: 7%;
}

::v-deep(table thead th:first-child a) {
  justify-content: center;
}

::v-deep(table tbody td:first-child) {
  width: 7%;
  text-align: center;
}
</style>
