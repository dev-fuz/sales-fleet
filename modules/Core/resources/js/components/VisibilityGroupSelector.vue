<template>
  <div>
    <label
      v-t="'core::app.visibility_group.visible_to'"
      class="text-sm font-medium text-neutral-700 dark:text-neutral-100"
    />
    <fieldset class="mt-2">
      <legend class="sr-only">Visibility group</legend>
      <div class="space-y-3 sm:flex sm:items-center sm:space-x-4 sm:space-y-0">
        <IFormRadio
          :model-value="type"
          :disabled="disabled"
          value="all"
          @update:model-value="$emit('update:type', $event)"
          @change="$emit('update:dependsOn', [])"
        >
          {{ $t('core::app.visibility_group.all') }}
        </IFormRadio>
        <IFormRadio
          :model-value="type"
          :disabled="disabled"
          value="teams"
          @update:model-value="$emit('update:type', $event)"
          @change="$emit('update:dependsOn', [])"
        >
          {{ $t('users::team.teams') }}
        </IFormRadio>
        <IFormRadio
          :model-value="type"
          :disabled="disabled"
          value="users"
          @update:model-value="$emit('update:type', $event)"
          @change="$emit('update:dependsOn', [])"
        >
          {{ $t('users::user.users') }}
        </IFormRadio>
      </div>
    </fieldset>
    <div v-show="type === 'users'" class="mt-4">
      <ICustomSelect
        :model-value="dependsOn"
        :options="usersWithoutAdministrators"
        :placeholder="$t('users::user.select')"
        label="name"
        multiple
        :reduce="option => option.id"
        @update:model-value="$emit('update:dependsOn', $event)"
      />
      <span
        v-t="'users::user.admin_users_excluded'"
        class="mt-0.5 block text-right text-xs text-neutral-500 dark:text-neutral-300"
      />
    </div>
    <div v-show="type === 'teams'" class="mt-4">
      <ICustomSelect
        :model-value="dependsOn"
        :options="teams"
        :placeholder="$t('users::team.select')"
        label="name"
        multiple
        :reduce="option => option.id"
        @update:model-value="$emit('update:dependsOn', $event)"
      />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

import { useApp } from '~/Core/composables/useApp'

import { useTeams } from '~/Users/composables/useTeams'

defineEmits(['update:type', 'update:dependsOn'])

defineProps({ disabled: Boolean, dependsOn: Array, type: String })

const { users } = useApp()

const { teamsByName: teams } = useTeams()

const usersWithoutAdministrators = computed(() =>
  users.value.filter(user => !user.super_admin)
)
</script>
