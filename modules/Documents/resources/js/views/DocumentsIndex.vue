<template>
  <ILayout>
    <template #actions>
      <NavbarSeparator class="hidden lg:block" />
      <div class="inline-flex items-center">
        <div class="mr-3 lg:mr-6">
          <IMinimalDropdown type="horizontal">
            <IDropdownItem
              icon="Bars3CenterLeft"
              :to="{
                name: 'document-templates-index',
              }"
              :text="$t('documents::document.template.manage')"
            />

            <IDropdownItem
              icon="Trash"
              :to="{
                name: 'trashed-resource-records',
                params: { resourceName: resourceName },
              }"
              :text="$t('core::app.soft_deletes.trashed')"
            />

            <IDropdownItem
              icon="Cog"
              :text="$t('core::table.list_settings')"
              @click="customizeTable(tableId)"
            />
          </IMinimalDropdown>
        </div>

        <IButton
          :to="{ name: 'create-document' }"
          icon="Plus"
          size="sm"
          :text="$t('documents::document.create')"
        />
      </div>
    </template>

    <CardList v-if="showCards" :resource-name="resourceName" />

    <DocumentsTable
      :table-id="tableId"
      :initialize="initialize"
      @loaded="tableEmpty = $event.empty"
      @deleted="refreshIndex"
    />

    <!-- Create router view -->
    <router-view name="create" @created="refreshIndex" @sent="refreshIndex" />

    <!-- Edit router view -->
    <router-view
      name="edit"
      :exit-using="() => $router.push({ name: 'document-index' })"
      @changed="refreshIndex"
      @deleted="refreshIndex"
      @cloned="refreshIndex"
    />
  </ILayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { onBeforeRouteUpdate, useRoute } from 'vue-router'

import CardList from '~/Core/components/Cards/CardList.vue'
import { useTable } from '~/Core/composables/useTable'

import DocumentsTable from '../components/DocumentsTable.vue'

const resourceName = Innoclapps.resourceName('documents')
const tableId = 'documents'

const route = useRoute()
const { reloadTable, customizeTable } = useTable()

const initialize = ref(route.meta.initialize)
const tableEmpty = ref(true)

const showCards = computed(() => initialize.value && !tableEmpty.value)

function refreshIndex() {
  Innoclapps.$emit('refresh-cards')

  reloadTable(tableId)
}

/**
 * Before the cached route is updated
 * For all cases set that intialize index to be true
 * This helps when intially "initialize" was false
 * But now when the user actually sees the index, it should be updated to true
 */
onBeforeRouteUpdate((to, from, next) => {
  initialize.value = true

  next()
})
</script>
