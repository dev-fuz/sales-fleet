<template>
  <form class="space-y-6" method="POST" @submit.prevent="submit">
    <IFormGroup :label="$t('auth.login')" label-for="email">
      <IFormInput
        id="email"
        v-model="form.email"
        type="email"
        name="email"
        autocomplete="email"
        autofocus
        required
      />
      <IFormError v-text="form.getError('email')" />
    </IFormGroup>

    <IFormGroup :label="$t('auth.password')" label-for="password">
      <IFormInput
        id="password"
        ref="passwordRef"
        v-model="form.password"
        type="password"
        name="password"
        required
        autocomplete="current-password"
      />
      <IFormError v-text="form.getError('password')" />
    </IFormGroup>

    <IFormGroup v-if="reCaptcha.validate">
      <VueRecaptcha
        ref="reCaptchaRef"
        :sitekey="reCaptcha.siteKey"
        @verify="handleReCaptchaVerified"
      />
      <IFormError v-text="form.getError('g-recaptcha-response')" />
    </IFormGroup>

    <div class="flex items-center justify-between">
      <div class="flex items-center">
        <IFormCheckbox
          id="remember"
          v-model="form.remember"
          name="remember"
          :label="$t('auth.remember_me')"
        />
      </div>

      <a
        v-if="!setting('disable_password_forgot')"
        v-t="'auth.forgot_password'"
        :href="installationUrl + '/password/reset'"
        class="link text-sm"
      />
    </div>

    <IButton
      type="submit"
      block
      :disabled="submitButtonIsDisabled"
      :loading="requestInProgress"
      :text="$t('auth.login')"
      @click="login"
    />
  </form>
</template>

<script setup>
import { computed, ref } from 'vue'
import { VueRecaptcha } from 'vue-recaptcha'

import { useApp } from '~/Core/composables/useApp'
import { useForm } from '~/Core/composables/useForm'

const { setting } = useApp()

const reCaptcha = Innoclapps.config('reCaptcha') || {}
const installationUrl = Innoclapps.config('url')
const passwordRef = ref(null)
const reCaptchaRef = ref(null)
const requestInProgress = ref(false)

const { form } = useForm({
  email: null,
  password: null,
  remember: null,
  'g-recaptcha-response': null,
})

const submitButtonIsDisabled = computed(() => requestInProgress.value)

async function login() {
  requestInProgress.value = true
  passwordRef.value.blur()

  await Innoclapps.request().get(installationUrl + '/sanctum/csrf-cookie')

  form
    .post(installationUrl + '/login')
    .then(data => (window.location.href = data.redirect_path))
    .finally(() => reCaptchaRef.value && reCaptchaRef.value.reset())
    .catch(() => (requestInProgress.value = false))
}

function handleReCaptchaVerified(response) {
  form.fill('g-recaptcha-response', response)
}
</script>
