<template>
  <form @keydown="form.onKeydown($event)" @submit.prevent="accept">
    <ITabGroup>
      <ITabList class="-mt-5">
        <ITab :title="$t('users::profile.profile')" />
        <ITab :title="$t('auth.password')" />
        <ITab :title="$t('users::user.localization')" />
      </ITabList>
      <ITabPanels>
        <ITabPanel>
          <IFormGroup :label="$t('users::user.name')" label-for="name" required>
            <IFormInput id="name" ref="name" v-model="form.name" type="text" />
            <IFormError v-text="form.getError('name')" />
          </IFormGroup>
          <IFormGroup :label="$t('users::user.email')" label-for="email">
            <IFormInput
              id="email"
              v-model="form.email"
              name="email"
              disabled
              type="email"
            />
            <IFormError v-text="form.getError('email')" />
          </IFormGroup>
        </ITabPanel>
        <ITabPanel>
          <IFormGroup
            :label="$t('auth.password')"
            label-for="password"
            required
          >
            <IFormInput
              id="password"
              v-model="form.password"
              name="password"
              type="password"
            />
            <IFormError v-text="form.getError('password')" />
          </IFormGroup>
          <IFormGroup
            :label="$t('auth.confirm_password')"
            label-for="password_confirmation"
            required
          >
            <IFormInput
              id="password_confirmation"
              v-model="form.password_confirmation"
              name="password_confirmation"
              type="password"
            />
            <IFormError v-text="form.getError('password_confirmation')" />
          </IFormGroup>
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
        <IButton
          type="submit"
          class="mt-2"
          :disabled="requestInProgress"
          :loading="requestInProgress"
          :text="$t('users::user.accept_invitation')"
        />
      </ITabPanels>
    </ITabGroup>
  </form>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useStore } from 'vuex'

import { useForm } from '~/Core/composables/useForm'
import LocalizationFields from '~/Core/views/Settings/LocalizationFields.vue'

const props = defineProps({
  invitation: { type: Object, required: true },
  timezones: { type: Array, required: true },
  dateFormat: String,
  timeFormat: String,
  firstDayOfWeek: String,
})

const { t } = useI18n()
const store = useStore()
const requestInProgress = ref(false)

const { form } = useForm({
  name: null,
  password: null,
  timezone: moment.tz.guess(),
  locale: 'en',
  date_format: props.dateFormat,
  time_format: props.timeFormat,
  first_day_of_week: props.firstDayOfWeek,
  password_confirmation: null,
  email: props.invitation.email,
})

function accept() {
  requestInProgress.value = true

  form
    .post(props.invitation.link)
    .then(() => (window.location.href = Innoclapps.config('url')))
    .catch(e => {
      if (e.isValidationError()) {
        Innoclapps.error(
          t('core::app.form_validation_failed_with_sections'),
          3000
        )
      }
    })
    .finally(() => (requestInProgress.value = false))
}

store.commit('SET_TIMEZONES', props.timezones)
</script>
