<template>
  <ILayout>
    <CardList v-if="showCards" :resource-name="resourceName" />

    <template #actions>
      <NavbarSeparator class="hidden lg:block" />

      <div class="inline-flex items-center">
        <div class="mr-3 lg:mr-6">
          <IMinimalDropdown type="horizontal">
            <IDropdownItem
              icon="DocumentAdd"
              :to="{
                name: 'import-deal',
              }"
              :text="$t('core::import.import')"
            />
            <IDropdownItem
              v-if="$gate.userCan('export deals')"
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
        <IButtonGroup class="mr-5">
          <IButton
            v-i-tooltip.bottom="$t('core::app.list_view')"
            size="sm"
            class="relative bg-neutral-100 focus:z-10"
            :to="{ name: 'deal-index' }"
            variant="white"
            icon="Bars3"
            icon-class="w-4 h-4 text-neutral-700 dark:text-neutral-100"
          />
          <IButton
            v-i-tooltip.bottom="$t('deals::board.board')"
            size="sm"
            class="relative focus:z-10"
            :to="{ name: 'deal-board' }"
            variant="white"
            icon="ViewColumns"
            icon-class="w-4 h-4 text-neutral-500 dark:text-neutral-400"
          />
        </IButtonGroup>
        <IButton
          :to="{ name: 'create-deal' }"
          icon="Plus"
          size="sm"
          :text="$t('deals::deal.create')"
        />
      </div>
    </template>

    <DealsTable
      :table-id="tableId"
      :initialize="initialize"
      :filter-id="
        $route.query.filter_id ? Number($route.query.filter_id) : undefined
      "
      @loaded="tableEmpty = $event.empty"
      @deleted="refreshIndex"
    />

    <DealsExport
      url-path="/deals/export"
      :resource-name="resourceName"
      :filters-view="tableId"
      :title="$t('deals::deal.export')"
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
import DealsExport from '~/Core/components/Export'
import { useTable } from '~/Core/composables/useTable'

import DealsTable from '../components/DealsTable.vue'

const resourceName = Innoclapps.resourceName('deals')
const tableId = 'deals'

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
 * For all cases intialize index to be true
 * This helps when intially "initialize" was false
 * But now when the user actually sees the index, it should be updated to true
 */
onBeforeRouteUpdate((to, from, next) => {
  initialize.value = true

  next()
})
</script>
