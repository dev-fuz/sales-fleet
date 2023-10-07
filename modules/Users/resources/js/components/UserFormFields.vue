<template>
  <ITabGroup>
    <ITabList fill>
      <ITab :title="$t('users::user.user')" />
      <ITab :title="$t('auth.password')" />
      <ITab :title="$t('users::user.localization')" />
      <ITab :title="$t('core::notifications.notifications')" />
      <ITab :title="$t('core::app.advanced')" />
    </ITabList>
    <ITabPanels>
      <ITabPanel>
        <IFormGroup :label="$t('users::user.name')" label-for="name" required>
          <IFormInput
            id="name"
            ref="name"
            v-model="form.name"
            type="text"
            autocomplete="off"
          >
          </IFormInput>
          <IFormError v-text="form.getError('name')" />
        </IFormGroup>
        <IFormGroup :label="$t('users::user.email')" label-for="email" required>
          <IFormInput
            id="email"
            v-model="form.email"
            name="email"
            type="email"
            autocomplete="off"
          >
          </IFormInput>
          <IFormError v-text="form.getError('email')" />
        </IFormGroup>
        <IFormGroup :label="$t('core::role.roles')" label-for="roles">
          <ICustomSelect
            v-model="form.roles"
            input-id="roles"
            :placeholder="$t('users::user.roles')"
            :options="rolesNames"
            :multiple="true"
          />
        </IFormGroup>
      </ITabPanel>
      <ITabPanel>
        <IFormGroup
          :label="$t('auth.password')"
          label-for="password"
          :required="!isEdit"
        >
          <IFormInput
            id="password"
            v-model="form.password"
            name="password"
            type="password"
            autocomplete="new-password"
          >
          </IFormInput>
          <IFormError v-text="form.getError('password')" />
        </IFormGroup>
        <IFormGroup
          :label="$t('auth.confirm_password')"
          label-for="password_confirmation"
          :required="!isEdit || Boolean(form.password)"
        >
          <IFormInput
            id="password_confirmation"
            v-model="form.password_confirmation"
            name="password_confirmation"
            autocomplete="new-password"
            type="password"
          >
          </IFormInput>
          <IFormError v-text="form.getError('password_confirmation')" />
        </IFormGroup>
        <PasswordGenerator />
      </ITabPanel>
      <ITabPanel>
        <LocalizationFields
          :form="form"
          @update:first-day-of-week="form.first_day_of_week = $event"
          @update:time-format="form.time_format = $event"
          @update:date-format="form.date_format = $event"
          @update:locale="form.locale = $event"
          @update:timezone="form.timezone = $event"
        />
      </ITabPanel>
    </ITabPanels>
    <ITabPanel>
      <NotificationSettings
        v-if="form.notifications_settings"
        class="overflow-hidden rounded-md border-x border-b border-neutral-200 dark:border-neutral-800"
        :form="form"
      />
    </ITabPanel>
    <ITabPanel>
      <div
        :class="[
          'flex items-center rounded-md border-2 px-5 py-4 shadow-sm',
          form.super_admin
            ? 'border-primary-400'
            : 'border-neutral-200 dark:border-neutral-400',
        ]"
      >
        <div class="grow">
          <p
            v-t="'users::user.super_admin'"
            class="text-sm text-neutral-900 dark:text-neutral-200"
          />
          <small
            v-t="'users::user.as_super_admin_info'"
            class="text-neutral-700 dark:text-neutral-400"
          />
        </div>
        <div class="ms-3">
          <IFormToggle
            v-model="form.super_admin"
            :disabled="currentUserIsSuperAdmin"
            @change="handleSuperAdminChange"
          />
        </div>
      </div>
      <div
        :class="[
          'mt-3 flex items-center rounded-md border-2 px-5 py-4 shadow-sm',
          form.access_api
            ? 'border-primary-400'
            : 'border-neutral-200 dark:border-neutral-400',
        ]"
      >
        <div class="grow">
          <p
            v-t="'users::user.enable_api'"
            class="text-sm text-neutral-900 dark:text-neutral-200"
          />

          <small
            v-t="'users::user.allow_api_info'"
            class="text-neutral-700 dark:text-neutral-400"
          />
        </div>
        <div class="ms-3">
          <IFormToggle
            v-model="form.access_api"
            :disabled="currentUserIsSuperAdmin || form.super_admin"
          />
        </div>
      </div>
    </ITabPanel>
  </ITabGroup>
</template>

<script setup>
import { computed } from 'vue'

import PasswordGenerator from '~/Core/components/PasswordGenerator.vue'
import { useApp } from '~/Core/composables/useApp'
import { useRoles } from '~/Core/composables/useRoles'
import LocalizationFields from '~/Core/views/Settings/LocalizationFields.vue'

import NotificationSettings from '../components/UserNotificationSettings.vue'

const props = defineProps({
  isEdit: Boolean,
  form: { required: true, type: Object },
})

const { currentUser } = useApp()
const { rolesNames } = useRoles()

/**
 * Check whether the current logged in user is super admin
 * Checks the actual id, as if the user can access this component,
 * means that is admin as this component is intended only for admins
 */
const currentUserIsSuperAdmin = computed(
  () => currentUser.value.id === props.form.id
)

function handleSuperAdminChange(val) {
  if (val) {
    props.form.access_api = true
  }
}
</script>
