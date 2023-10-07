<template>
  <ILayout>
    <template #actions>
      <NavbarSeparator class="hidden lg:block" />
      <div class="inline-flex items-center">
        <div class="mr-3 lg:mr-6">
          <IMinimalDropdown type="horizontal">
            <IDropdownItem
              icon="DocumentAdd"
              :to="{
                name: 'import-resource',
                params: { resourceName },
              }"
              :text="$t('core::import.import')"
            />
            <IDropdownItem
              v-if="$gate.userCan('export contacts')"
              icon="DocumentDownload"
              :text="$t('core::app.export.export')"
              @click="$iModal.show('export-modal')"
            />
            <IDropdownItem
              icon="Trash"
              :to="{
                name: 'trashed-resource-records',
                params: { resourceName },
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
          :to="{ name: 'create-contact' }"
          icon="Plus"
          size="sm"
          :text="$t('contacts::contact.create')"
        />
      </div>
    </template>

    <CardList v-if="showCards" :resource-name="resourceName" />

    <ContactsTable
      :table-id="tableId"
      :initialize="initialize"
      @loaded="tableEmpty = $event.empty"
      @deleted="refreshIndex"
    />

    <ContactsExport
      url-path="/contacts/export"
      :resource-name="resourceName"
      :filters-view="tableId"
      :title="$t('contacts::contact.export')"
    />

    <!-- Create -->
    <router-view
      name="create"
      :redirect-to-view="true"
      @created="
        ({ isRegularAction }) => (!isRegularAction ? refreshIndex() : undefined)
      "
      @modal-hidden="$router.back"
    />
  </ILayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { onBeforeRouteUpdate, useRoute } from 'vue-router'

import CardList from '~/Core/components/Cards/CardList.vue'
import ContactsExport from '~/Core/components/Export'
import { useTable } from '~/Core/composables/useTable'

import ContactsTable from '../components/ContactsTable.vue'

const resourceName = Innoclapps.resourceName('contacts')
const tableId = 'contacts'

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
