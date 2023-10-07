<template>
  <ISlideover
    :title="$t('billable::product.create')"
    visible
    static-backdrop
    form
    @submit="create"
    @hidden="$router.back()"
  >
    <FieldsPlaceholder v-if="fields.isEmpty()" />

    <FormFields
      :fields="fields"
      :form-id="form.formId"
      :resource-name="resourceName"
      is-floating
      focus-first
    >
      <template v-if="trashedProduct !== null" #after-name-field>
        <IAlert
          dismissible
          class="mb-3"
          @dismissed="
            ;(recentlyRestored.byName = false), (trashedProduct = null)
          "
        >
          {{ $t('billable::product.exists_in_trash_by_name') }}

          <div class="mt-4">
            <div class="-mx-2 -my-1.5 flex">
              <IButtonMinimal
                v-show="!recentlyRestored.byName"
                variant="info"
                :text="$t('core::app.soft_deletes.restore')"
                @click="restoreTrashed(trashedProduct.id, 'byName')"
              />
              <IButtonMinimal
                v-show="recentlyRestored.byName"
                variant="info"
                :text="$t('core::app.view_record')"
                @click="
                  $router.replace({
                    name: 'view-product',
                    params: { id: trashedProduct.id },
                  })
                "
              />
            </div>
          </div>
        </IAlert>
      </template>
    </FormFields>

    <template #modal-ok>
      <IDropdownButtonGroup
        type="submit"
        :disabled="form.busy"
        :loading="form.busy"
        :text="$t('core::app.create')"
      >
        <IDropdownItem
          :text="$t('core::app.create_and_add_another')"
          @click="createAndAddAnother"
        />
        <IDropdownItem
          :text="$t('core::app.create_and_go_to_list')"
          @click="createAndGoToList"
        />
      </IDropdownButtonGroup>
    </template>
  </ISlideover>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import { watchDebounced } from '@vueuse/core'

import { useFieldsForm } from '~/Core/composables/useFieldsForm'
import { useResourceable } from '~/Core/composables/useResourceable'
import { useResourceFields } from '~/Core/composables/useResourceFields'

const emit = defineEmits(['created', 'restored'])

const resourceName = Innoclapps.resourceName('products')

const { t } = useI18n()
const router = useRouter()

const { fields, getCreateFields } = useResourceFields()
const { form } = useFieldsForm(fields)
const { createResource } = useResourceable(resourceName)

const trashedProduct = ref(null)
const nameField = ref(null)

const recentlyRestored = ref({
  byName: false,
})

watchDebounced(
  () => nameField.value?.currentValue,
  newVal => {
    if (!newVal) {
      trashedProduct.value = null

      return
    }

    Innoclapps.request()
      .get('/trashed/products/search', {
        params: {
          q: newVal,
          search_fields: 'name:=',
        },
      })
      .then(({ data: products }) => {
        trashedProduct.value = products.length > 0 ? products[0] : null
      })
  },
  { debounce: 500 }
)

function create() {
  makeCreateRequest().then(() => router.back())
}

function createAndAddAnother() {
  makeCreateRequest().then(() => form.reset())
}

function createAndGoToList() {
  makeCreateRequest().then(() => router.push('/products'))
}

async function makeCreateRequest() {
  try {
    let product = await createResource(form)

    emit('created', product)

    Innoclapps.success(t('billable::product.created'))

    return product
  } catch (e) {
    if (e.isValidationError()) {
      Innoclapps.error(t('core::app.form_validation_failed'), 3000)
    }

    return Promise.reject(e)
  }
}

function restoreTrashed(id, type) {
  Innoclapps.request()
    .post(`/trashed/products/${id}`)
    .then(() => {
      recentlyRestored.value[type] = true
      emit('restored', trashedProduct)
    })
}

function prepareComponent() {
  getCreateFields(resourceName)
    .then(createFields => fields.value.set(createFields))
    .then(() => {
      nameField.value = fields.value.find('name')
    })
}

prepareComponent()
</script>
