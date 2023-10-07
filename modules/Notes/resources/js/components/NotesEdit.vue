<template>
  <ICard>
    <Editor
      v-model="form.body"
      :with-mention="true"
      @input="form.errors.clear('body')"
    />

    <IFormError v-text="form.getError('body')" />

    <template #footer>
      <div class="flex justify-end space-x-2">
        <IButton
          variant="white"
          size="sm"
          :text="$t('core::app.cancel')"
          @click="$emit('cancelled')"
        />

        <IButton
          variant="primary"
          size="sm"
          :text="$t('core::app.save')"
          :disabled="form.busy"
          @click="update"
        />
      </div>
    </template>
  </ICard>
</template>

<script setup>
import { inject } from 'vue'
import { useI18n } from 'vue-i18n'

import { useForm } from '~/Core/composables/useForm'
import { useResourceable } from '~/Core/composables/useResourceable'

const emit = defineEmits(['updated', 'cancelled'])

const props = defineProps({
  noteId: { required: true, type: Number },
  body: { required: true, type: String },
  viaResource: { required: true, type: String },
  viaResourceId: { required: true, type: [String, Number] },
})

const synchronizeResource = inject('synchronizeResource')

const { t } = useI18n()
const { updateResource } = useResourceable(Innoclapps.resourceName('notes'))
const { form } = useForm({ body: props.body })

async function update() {
  let updatedNote = await updateResource(
    form.withQueryString({
      via_resource: props.viaResource,
      via_resource_id: props.viaResourceId,
    }),
    props.noteId
  )

  synchronizeResource({ notes: updatedNote })

  emit('updated', updatedNote)

  Innoclapps.success(t('notes::note.updated'))
}
</script>
