<template>
  <ISlideover
    :ok-disabled="
      form.busy || (fields.isNotEmpty() && !product.authorizations.update)
    "
    :ok-loading="form.busy"
    :ok-title="$t('core::app.save')"
    :title="$t('billable::product.edit')"
    visible
    static-backdrop
    form
    @hidden="$router.back()"
    @submit="update"
  >
    <FieldsPlaceholder v-if="fields.isEmpty()" />

    <FormFields
      :fields="fields"
      :form-id="form.formId"
      :resource-name="resourceName"
      :resource-id="$route.params.id"
    />
  </ISlideover>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute, useRouter } from 'vue-router'

import { useFieldsForm } from '~/Core/composables/useFieldsForm'
import { useResourceable } from '~/Core/composables/useResourceable'
import { useResourceFields } from '~/Core/composables/useResourceFields'

import { useProducts } from '../composables/useProducts'

const emit = defineEmits(['updated'])

const resourceName = Innoclapps.resourceName('products')

const { t } = useI18n()
const router = useRouter()
const route = useRoute()
const { fetchProduct } = useProducts()

const { fields, getUpdateFields } = useResourceFields()
const { form } = useFieldsForm(fields)
const { updateResource } = useResourceable(resourceName)

const product = ref(null)

async function update() {
  let product = await updateResource(form, route.params.id)

  emit('updated', product)

  Innoclapps.success(t('billable::product.updated'))
  router.back()
}

function prepareComponent() {
  Promise.all([
    fetchProduct(route.params.id),
    getUpdateFields(resourceName, route.params.id),
  ]).then(values => {
    fields.value.set(values[1]).populate(values[0])
    product.value = values[0]
  })
}

prepareComponent()
</script>
