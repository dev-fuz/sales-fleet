<template>
  <IDropdown
    ref="dropdownRef"
    placement="bottom-end"
    items-class="max-w-xs sm:max-w-sm"
    :full="false"
  >
    <template #toggle="{ toggle }">
      <IButton
        variant="white"
        :rounded="false"
        :size="false"
        class="relative rounded-full p-1"
        @click="toggle(), markAllRead()"
      >
        <Icon icon="Bell" class="h-6 w-6" />

        <div v-if="hasUnread" class="relative">
          <div
            v-i-tooltip.bottom="totalUnread"
            class="absolute -right-2 -top-5"
          >
            <span class="relative flex h-3 w-3">
              <span
                class="absolute inline-flex h-full w-full animate-ping rounded-full bg-primary-400 opacity-75"
              />
              <span
                class="relative inline-flex h-3 w-3 rounded-full bg-primary-500"
              />
            </span>
          </div>
        </div>
      </IButton>
    </template>

    <div
      :class="[
        'flex items-center px-4 py-3 sm:p-4',
        { 'border-b border-neutral-200 dark:border-neutral-700': total > 0 },
      ]"
    >
      <div
        v-t="
          total > 0
            ? 'core::notifications.notifications'
            : 'core::notifications.no_notifications'
        "
        :class="[
          'grow text-neutral-800 dark:text-white',
          { 'font-medium': total > 0, 'sm:text-sm': total === 0 },
        ]"
      />
      <router-link
        v-i-tooltip="$t('core::settings.settings')"
        :to="{ name: 'profile', hash: '#notifications' }"
        class="link ml-2"
        @click="() => $refs.dropdownRef.hide()"
      >
        <Icon icon="Cog" class="h-5 w-5" />
      </router-link>
    </div>

    <div
      class="max-h-96 divide-y divide-neutral-200 overflow-y-auto dark:divide-neutral-700"
    >
      <IDropdownItem
        v-for="notification in notifications"
        :key="notification.id"
        :title="localize(notification)"
        :to="notification.data.path"
      >
        <p
          class="truncate text-neutral-800 dark:text-neutral-100"
          v-text="localize(notification)"
        />
        <span
          class="text-xs text-neutral-500 dark:text-neutral-300"
          v-text="localizedDateTime(notification.created_at)"
        />
      </IDropdownItem>
    </div>

    <div
      v-show="total > 0"
      class="flex items-center justify-end border-t border-neutral-200 bg-neutral-50 px-4 py-2 text-sm dark:border-neutral-600 dark:bg-neutral-700"
    >
      <router-link
        v-t="'core::app.see_all'"
        :to="{ name: 'notifications' }"
        class="link"
        @click="() => $refs.dropdownRef.hide()"
      />
    </div>
  </IDropdown>
</template>

<script setup>
import { computed } from 'vue'
import { useStore } from 'vuex'

import { useApp } from '~/Core/composables/useApp'
import { useDates } from '~/Core/composables/useDates'

const { currentUser } = useApp()
const { localizedDateTime } = useDates()

const store = useStore()

const total = computed(() => store.getters['users/totalNotifications'])

const hasUnread = computed(() => store.getters['users/hasUnreadNotifications'])

const localize = store.getters['users/localizeNotification']

const notifications = computed(() => currentUser.value.notifications.latest)

const totalUnread = computed(() => currentUser.value.notifications.unread_count)

function markAllRead() {
  store.dispatch('users/markAllNotificationsAsRead')
}
</script>
