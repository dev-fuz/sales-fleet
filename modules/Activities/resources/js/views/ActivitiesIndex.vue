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
              v-if="$gate.userCan('export activities')"
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
            :to="{ name: 'activity-index' }"
            icon="Bars3"
            icon-class="w-4 h-4 text-neutral-700 dark:text-neutral-100"
            variant="white"
          />
          <IButton
            v-i-tooltip.bottom="$t('activities::calendar.calendar')"
            size="sm"
            class="relative focus:z-10"
            :to="{ name: 'activity-calendar' }"
            variant="white"
            icon="Calendar"
            icon-class="w-4 h-4 text-neutral-500 dark:text-neutral-400"
          />
        </IButtonGroup>

        <IButton
          :to="{ name: 'create-activity' }"
          icon="Plus"
          size="sm"
          :text="$t('activities::activity.create')"
        />
      </div>
    </template>

    <ActivitiesTable
      :table-id="tableId"
      :initialize="initialize"
      :filter-id="
        $route.query.filter_id ? Number($route.query.filter_id) : undefined
      "
    />

    <ActivitiesExport
      url-path="/activities/export"
      :resource-name="resourceName"
      :filters-view="tableId"
      :title="$t('activities::activity.export')"
    />

    <!-- Create -->
    <router-view
      name="create"
      @created="
        ({ isRegularAction, action }) => (
          recordCreated(),
          isRegularAction || action === 'go-to-list'
            ? $router.back()
            : undefined
        )
      "
      @modal-hidden="$router.back"
    />
  </ILayout>
</template>

<script setup>
import { ref } from 'vue'
import { onBeforeRouteUpdate, useRoute } from 'vue-router'

import ActivitiesExport from '~/Core/components/Export'
import { useTable } from '~/Core/composables/useTable'

import ActivitiesTable from '../components/ActivitiesTable.vue'

const resourceName = Innoclapps.resourceName('activities')
const tableId = 'activities'

const route = useRoute()
const { reloadTable, customizeTable } = useTable()

const initialize = ref(route.meta.initialize)

function recordCreated() {
  if (initialize.value) {
    reloadTable(tableId)
  }
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
