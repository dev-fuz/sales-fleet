<template>
  <ICard
    tag="form"
    :header="$t('brands::brand.update')"
    @submit.prevent="save"
    @keydown="form.onKeydown($event)"
    no-body
    :overlay="!componentReady"
  >
    <div v-if="componentReady" class="p-8">
      <IFormGroup
        label-for="name"
        label="List Name"
        required
      >
        <IFormInput
          v-model="form.name"
          id="name"
          ref="inputNameRef"
        />
        <IFormError v-text="form.getError('name')" />
      </IFormGroup>
      <IFormGroup
        label-for="description"
        label="Description"
        required
      >
        <IFormTextarea rows="4" v-model="form.description"
          id="description"
        />
        <IFormError v-text="form.getError('description')" />
      </IFormGroup>
    </div>
    <template #footer>
      <IButton
        type="submit"
        :disabled="form.busy"
        :text="$t('core::app.save')"
      />
    </template>
  </ICard>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute } from 'vue-router'
import { useLists } from '../composables/useLists'
import { useForm } from '~/Core/resources/js/composables/useForm'

const { t } = useI18n()
const route = useRoute()
const { setList, fetchList } = useLists()

const componentReady = ref(false)
const list = ref(null)

const { form } = useForm({})

function prepareComponent(id) {
  fetchList(id)
    .then(data => {
      let listBeingEdited = data

      form.set({
        name: listBeingEdited.name,
        description: listBeingEdited.description,
      })

      list.value = listBeingEdited
    })
    .finally(() => (componentReady.value = true))
}

function save() {
  form
    .put(`/lists/${list.value.id}`)
    .then(updatedList => {
      list.value = updatedList
      setList(updatedList.id, updatedList)
      Innoclapps.success("List updated successfully!")
    })
}


prepareComponent(route.params.id)
</script>
