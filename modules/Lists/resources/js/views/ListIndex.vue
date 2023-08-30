<template>
    <ICard
      header="Lists"
      :overlay="listsAreBeingFetched"
      no-body
    >
      <template #actions>
        <IButton
          :to="{ name: 'create-list' }"
          icon="plus"
          size="sm"
          text="Create List"
        />
      </template>
      <ITable class="-mt-px">
        <thead>
          <tr>
            <th class="text-left" v-t="'core::app.id'" width="5%"></th>
            <th class="text-left">List</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="list in listsByName" :key="list.id">
            <td v-text="list.id"></td>
            <td>
              <router-link
                class="link"
                :to="{ name: 'edit-list', params: { id: list.id } }"
              >
                {{ list.name }}
              </router-link>
            </td>
            <td class="flex justify-end">
              <IMinimalDropdown>
                <IDropdownItem
                  :to="{ name: 'edit-list', params: { id: list.id } }"
                  :text="$t('core::app.edit')"
                />
  
                <IDropdownItem
                  @click="destroy(list.id)"
                  :text="$t('core::app.delete')"
                />
              </IMinimalDropdown>
            </td>
          </tr>
        </tbody>
      </ITable>
    </ICard>
  </template>
  <script setup>
  import { useLists } from '../composables/useLists'
  
  const { listsByName, listsAreBeingFetched, deleteList } = useLists()
  
  async function destroy(id) {
    await Innoclapps.dialog().confirm()
    deleteList(id)
  }
  </script>
  