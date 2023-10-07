<template>
  <ICard :header="$t('deals::deal.pipeline.pipelines')" no-body>
    <template #actions>
      <IButton
        :to="{ name: 'create-pipeline' }"
        icon="plus"
        size="sm"
        :text="$t('deals::deal.pipeline.create')"
      />
    </template>
    <ITable class="-mt-px overflow-y-hidden">
      <thead>
        <tr>
          <th v-t="'core::app.id'" class="text-left" width="5%"></th>
          <th v-t="'deals::deal.pipeline.pipeline'" class="text-left"></th>
          <th></th>
        </tr>
      </thead>
      <draggable
        v-bind="draggableOptions"
        v-model="pipelines"
        tag="tbody"
        item-key="id"
      >
        <template #item="{ element: pipeline }">
          <tr>
            <td v-text="pipeline.id"></td>
            <td>
              <router-link
                class="link"
                :to="{ name: 'edit-pipeline', params: { id: pipeline.id } }"
              >
                {{ pipeline.name }}
              </router-link>
            </td>
            <td class="flex justify-end">
              <IMinimalDropdown>
                <IDropdownItem
                  :to="{ name: 'edit-pipeline', params: { id: pipeline.id } }"
                  :text="$t('core::app.edit')"
                />

                <IDropdownItem
                  :text="$t('core::app.delete')"
                  @click="destroy(pipeline.id)"
                />
              </IMinimalDropdown>
            </td>
          </tr>
        </template>
      </draggable>
      <tbody></tbody>
    </ITable>
  </ICard>
  <router-view></router-view>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import draggable from 'vuedraggable'

import { useDraggable } from '~/Core/composables/useDraggable'

import { usePipelines } from '../composables/usePipelines'

const { t } = useI18n()

const { orderedPipelinesForDraggable: pipelines, deletePipeline } =
  usePipelines()

const { draggableOptions } = useDraggable()

async function destroy(id) {
  await Innoclapps.dialog().confirm()
  await deletePipeline(id)

  Innoclapps.success(t('deals::deal.pipeline.deleted'))
}
</script>
