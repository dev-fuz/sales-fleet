<template>
    <IModal
      size="sm"
      @hidden="$router.back"
      @shown="() => $refs.inputNameRef.focus()"
      :ok-title="$t('core::app.create')"
      :ok-disabled="form.busy"
      :visible="true"
      title="Create List"
      form
      @submit="create"
      @keydown="form.onKeydown($event)"
    >
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

    </IModal>
  </template>
  <script setup>
  import { useI18n } from 'vue-i18n'
  import { useRouter } from 'vue-router'
  import { useLists } from '../composables/useLists.js'
  import { useForm } from '~/Core/resources/js/composables/useForm'
  
  const { t } = useI18n()
  const router = useRouter()
  
  const { addList } = useLists()
  
  
  const { form } = useForm({
    name: '',
    description: '',
  })
  
  function create() {

    form.post('/lists').then(list => {
      
      addList(list)
  
      Innoclapps.success("List created succussfully!")
  
      router.push({
        name: 'edit-list',
        params: {
          id: list.id,
        },
      })
    })

  }
  </script>
  