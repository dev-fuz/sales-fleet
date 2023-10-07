<template>
  <form class="space-y-6" method="POST" @submit.prevent="submit">
    <IAlert variant="success" :show="successMessage !== null">
      {{ successMessage }}
    </IAlert>

    <IFormGroup :label="$t('auth.email_address')" label-for="email">
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

    <IFormGroup v-if="reCaptcha.validate">
      <VueRecaptcha
        ref="reCaptchaRef"
        :sitekey="reCaptcha.siteKey"
        @verify="handleReCaptchaVerified"
      />
      <IFormError v-text="form.getError('g-recaptcha-response')" />
    </IFormGroup>

    <IButton
      type="submit"
      block
      :disabled="requestInProgress || !Boolean(form.email)"
      :loading="requestInProgress"
      :text="$t('passwords.send_password_reset_link')"
      @click="sendPasswordResetEmail"
    />
  </form>
</template>

<script setup>
import { ref } from 'vue'
import { VueRecaptcha } from 'vue-recaptcha'

import { useForm } from '~/Core/composables/useForm'

const reCaptcha = Innoclapps.config('reCaptcha') || {}
const installationUrl = Innoclapps.config('url')
const reCaptchaRef = ref(null)
const requestInProgress = ref(false)
const successMessage = ref(null)

const { form } = useForm(
  {
    email: null,
    'g-recaptcha-response': null,
  },
  {
    resetOnSuccess: true,
  }
)

async function sendPasswordResetEmail() {
  requestInProgress.value = true
  successMessage.value = null

  await Innoclapps.request().get(installationUrl + '/sanctum/csrf-cookie')

  form
    .post(installationUrl + '/password/email')
    .then(data => {
      successMessage.value = data.message
    })
    .finally(() => {
      requestInProgress.value = false
      reCaptchaRef.value && reCaptchaRef.value.reset()
    })
}

function handleReCaptchaVerified(response) {
  form.fill('g-recaptcha-response', response)
}
</script>
