<template>
  <ILayout>
    <div class="m-auto max-w-7xl">
      <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1">
          <h3
            v-t="'core::app.avatar'"
            class="text-lg/6 font-medium text-neutral-900 dark:text-white"
          />
          <p
            v-t="'users::profile.avatar_info'"
            class="mt-1 text-sm text-neutral-600 dark:text-neutral-300"
          />
        </div>
        <div class="mt-5 md:col-span-2 md:mt-0">
          <ICard>
            <CropsAndUploadsImage
              name="avatar"
              :upload-url="`${$store.state.apiURL}/users/${currentUser.id}/avatar`"
              :image="currentUser.uploaded_avatar_url"
              :cropper-options="{ aspectRatio: 1 / 1 }"
              :choose-text="
                currentUser.uploaded_avatar_url
                  ? $t('core::app.change')
                  : $t('core::app.upload_avatar')
              "
              @cleared="clearAvatar"
              @success="handleAvatarUploaded"
            />
          </ICard>
        </div>
      </div>

      <div class="hidden sm:block" aria-hidden="true">
        <div class="py-5">
          <div class="border-t border-neutral-200 dark:border-neutral-600" />
        </div>
      </div>

      <div class="mt-10 sm:mt-0">
        <div class="md:grid md:grid-cols-3 md:gap-6">
          <div class="md:col-span-1">
            <h3
              v-t="'users::profile.profile'"
              class="text-lg/6 font-medium text-neutral-900 dark:text-white"
            />
            <p
              v-t="'users::profile.profile_info'"
              class="mt-1 text-sm text-neutral-600 dark:text-neutral-300"
            />
          </div>
          <div class="mt-5 md:col-span-2 md:mt-0">
            <form @submit.prevent="update" @keydown="form.onKeydown($event)">
              <ICard>
                <IFormGroup :label="$t('users::user.name')" label-for="name">
                  <IFormInput id="name" v-model="form.name" name="name" />
                  <IFormError v-text="form.getError('name')" />
                </IFormGroup>
                <IFormGroup :label="$t('users::user.email')" label-for="email">
                  <IFormInput
                    id="email"
                    v-model="form.email"
                    name="email"
                    type="email"
                  >
                  </IFormInput>
                  <IFormError v-text="form.getError('email')" />
                </IFormGroup>
                <IFormGroup
                  :label="$t('mailclient::mail.signature')"
                  label-for="mail_signature"
                  :description="$t('mailclient::mail.signature_info')"
                >
                  <Editor v-model="form.mail_signature" />
                  <IFormError v-text="form.getError('mail_signature')" />
                </IFormGroup>
                <template #footer>
                  <div class="text-right">
                    <IButton
                      :disabled="form.busy"
                      :text="$t('users::profile.update')"
                      @click="update"
                    />
                  </div>
                </template>
              </ICard>
            </form>
          </div>
        </div>
      </div>

      <div class="hidden sm:block" aria-hidden="true">
        <div class="py-5">
          <div class="border-t border-neutral-200 dark:border-neutral-600" />
        </div>
      </div>

      <div class="mt-10 sm:mt-0">
        <div class="md:grid md:grid-cols-3 md:gap-6">
          <div class="md:col-span-1">
            <h3
              v-t="'users::user.localization'"
              class="text-lg/6 font-medium text-neutral-900 dark:text-white"
            />
            <p
              v-t="'users::profile.localization_info'"
              class="mt-1 text-sm text-neutral-600 dark:text-neutral-300"
            />
          </div>
          <div class="mt-5 md:col-span-2 md:mt-0">
            <ICard>
              <LocalizationFields
                :form="form"
                @update:first-day-of-week="form.first_day_of_week = $event"
                @update:time-format="form.time_format = $event"
                @update:date-format="form.date_format = $event"
                @update:locale="form.locale = $event"
                @update:timezone="form.timezone = $event"
              />
              <template #footer>
                <div class="text-right">
                  <IButton
                    :disabled="form.busy"
                    :text="$t('core::app.save')"
                    @click="update"
                  />
                </div>
              </template>
            </ICard>
          </div>
        </div>
      </div>

      <div class="hidden sm:block" aria-hidden="true">
        <div class="py-5">
          <div class="border-t border-neutral-200 dark:border-neutral-600" />
        </div>
      </div>

      <div class="mt-10 sm:mt-0">
        <div class="md:grid md:grid-cols-3 md:gap-6">
          <div class="md:col-span-1">
            <h3
              v-t="'core::notifications.notifications'"
              class="text-lg/6 font-medium text-neutral-900 dark:text-white"
            />
            <p
              v-t="'users::profile.notifications_info'"
              class="mt-1 text-sm text-neutral-600 dark:text-neutral-300"
            />
          </div>
          <div class="mt-5 md:col-span-2 md:mt-0">
            <ICard no-body>
              <NotificationSettings :form="form" class="-mt-px" />
              <template #footer>
                <div class="text-right">
                  <IButton
                    :disabled="form.busy"
                    :text="$t('core::app.save')"
                    @click="update"
                  />
                </div>
              </template>
            </ICard>
          </div>
        </div>
      </div>

      <div class="hidden sm:block" aria-hidden="true">
        <div class="py-5">
          <div class="border-t border-neutral-200 dark:border-neutral-600" />
        </div>
      </div>

      <div class="mt-10 sm:mt-0">
        <div class="md:grid md:grid-cols-3 md:gap-6">
          <div class="md:col-span-1">
            <h3
              v-t="'auth.password'"
              class="text-lg/6 font-medium text-neutral-900 dark:text-white"
            />
            <p
              v-t="'users::profile.password_info'"
              class="mt-1 text-sm text-neutral-600 dark:text-neutral-300"
            />
          </div>
          <div class="mt-5 md:col-span-2 md:mt-0">
            <form
              @submit.prevent="updatePassword"
              @keydown="formPassword.onKeydown($event)"
            >
              <ICard>
                <IFormGroup
                  :label="$t('auth.current_password')"
                  label-for="old_password"
                >
                  <IFormInput
                    id="old_password"
                    v-model="formPassword.old_password"
                    name="old_password"
                    type="password"
                    autocomplete="current-password"
                  >
                  </IFormInput>
                  <IFormError v-text="formPassword.getError('old_password')" />
                </IFormGroup>
                <IFormGroup>
                  <template #label>
                    <div class="flex">
                      <IFormLabel
                        class="mb-1 grow"
                        for="password"
                        :label="$t('auth.new_password')"
                      />

                      <a
                        v-i-toggle="'generate-password'"
                        v-t="'core::app.password_generator.heading'"
                        class="link text-sm"
                        href="#"
                      />
                    </div>
                  </template>

                  <IFormInput
                    id="password"
                    v-model="formPassword.password"
                    name="password"
                    type="password"
                    autocomplete="new-password"
                  >
                  </IFormInput>

                  <IFormError v-text="formPassword.getError('password')" />
                </IFormGroup>
                <IFormGroup
                  :label="$t('auth.confirm_password')"
                  label-for="password_confirmation"
                >
                  <IFormInput
                    id="password_confirmation"
                    v-model="formPassword.password_confirmation"
                    name="password_confirmation"
                    type="password"
                    autocomplete="new-password"
                  >
                  </IFormInput>
                  <IFormError
                    v-text="formPassword.getError('password_confirmation')"
                  />
                </IFormGroup>
                <div id="generate-password" style="display: none">
                  <PasswordGenerator />
                </div>
                <template #footer>
                  <div class="text-right">
                    <IButton
                      type="submit"
                      :disabled="formPassword.busy"
                      :text="$t('auth.change_password')"
                    />
                  </div>
                </template>
              </ICard>
            </form>
          </div>
        </div>
      </div>

      <div
        v-if="managedTeams.length > 0"
        class="hidden sm:block"
        aria-hidden="true"
      >
        <div class="py-5">
          <div class="border-t border-neutral-200 dark:border-neutral-600" />
        </div>
      </div>

      <div v-if="managedTeams.length > 0" class="mt-10 sm:mt-0">
        <div class="md:grid md:grid-cols-3 md:gap-6">
          <div class="md:col-span-1">
            <h3 class="text-lg/6 font-medium text-neutral-900 dark:text-white">
              {{ $t('users::team.your_teams', managedTeams.length) }}
            </h3>
            <p
              v-t="'users::team.managing_teams'"
              class="mt-1 text-sm text-neutral-600 dark:text-neutral-300"
            />
          </div>
          <div class="mt-5 md:col-span-2 md:mt-0">
            <ICard>
              <ul
                role="list"
                class="space-y-4 divide-y divide-neutral-200 dark:divide-neutral-700"
              >
                <li
                  v-for="team in managedTeams"
                  :key="team.id"
                  class="pt-4 first:pt-0"
                >
                  <p
                    class="truncate text-base font-medium text-neutral-800 dark:text-neutral-100"
                    v-text="team.name"
                  />
                  <p
                    v-t="'users::team.members'"
                    class="mb-2 mt-2 text-sm font-medium text-neutral-500 dark:text-neutral-300"
                  />
                  <div
                    v-for="member in team.members"
                    :key="'info-' + member.email"
                    class="mb-1 flex items-center space-x-1.5 last:mb-0"
                  >
                    <IAvatar
                      :alt="member.name"
                      size="xs"
                      :src="member.avatar_url"
                    />
                    <p
                      class="text-sm font-medium text-neutral-700 dark:text-neutral-300"
                      v-text="member.name"
                    />
                  </div>
                </li>
              </ul>
            </ICard>
          </div>
        </div>
      </div>
    </div>
  </ILayout>
</template>

<script setup>
import { computed, unref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useStore } from 'vuex'
import cloneDeep from 'lodash/cloneDeep'
import reduce from 'lodash/reduce'

import CropsAndUploadsImage from '~/Core/components/CropsAndUploadsImage.vue'
import PasswordGenerator from '~/Core/components/PasswordGenerator.vue'
import { useApp } from '~/Core/composables/useApp'
import { useForm } from '~/Core/composables/useForm'
import LocalizationFields from '~/Core/views/Settings/LocalizationFields.vue'

import NotificationSettings from '../components/UserNotificationSettings.vue'
import { useTeams } from '../composables/useTeams'

const { t } = useI18n()
const store = useStore()
const { currentUser, resetStoreState } = useApp()

const user = unref(currentUser)

const { form } = useForm()
const { form: formPassword } = useForm({}, { resetOnSuccess: true })

const { teams } = useTeams()

const managedTeams = computed(() =>
  teams.value.filter(team => team.manager.id === currentUser.value.id)
)

let originalLocale = null

function handleAvatarUploaded(updatedUser) {
  store.commit('users/UPDATE', {
    id: updatedUser.id,
    item: updatedUser,
  })

  user.avatar = updatedUser.avatar
  user.avatar_url = updatedUser.avatar_url
  // Update form avatar with new value
  // to prevent using the old value if the user saves the profile
  form.avatar = user.avatar
}

function clearAvatar() {
  if (!user.avatar) {
    return
  }

  store
    .dispatch('users/removeAvatar', user.id)
    .then(data => (form.avatar = data.avatar))
}

function update() {
  store.dispatch('users/updateProfile', form).then(() => {
    Innoclapps.success(t('users::profile.updated'))

    if (originalLocale !== form.locale) {
      window.location.reload()
    } else {
      resetStoreState()
    }
  })
}

function updatePassword() {
  formPassword.put('/profile/password').then(() => {
    Innoclapps.success(t('users::profile.password_updated'))
  })
}

function prepareComponent() {
  originalLocale = user.locale

  form.set({
    name: user.name,
    email: user.email,
    mail_signature: user.mail_signature,
    date_format: user.date_format,
    time_format: user.time_format,
    first_day_of_week: user.first_day_of_week,
    timezone: user.timezone,
    locale: user.locale,
    notifications_settings: reduce(
      cloneDeep(user.notifications.settings),
      (obj, val) => {
        obj[val.key] = val.availability

        return obj
      },
      {}
    ),
  })

  formPassword.set({
    old_password: null,
    password: null,
    password_confirmation: null,
  })
}

prepareComponent()
</script>
