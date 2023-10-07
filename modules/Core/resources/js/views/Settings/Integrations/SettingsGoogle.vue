<template>
  <form
    @submit.prevent="submitGoogleIntegrationSettings"
    @input="form.errors.clear($event.target.name)"
  >
    <ICard :overlay="!componentReady">
      <template #header>
        <div class="flex items-center">
          <Icon
            v-if="isConfigured && componentReady"
            icon="CheckCircleSolid"
            class="mr-1 h-5 w-5 text-success-600"
          />
          <ICardHeading>Google</ICardHeading>
        </div>
      </template>
      <template #actions>
        <a
          href="https://console.developers.google.com/"
          class="link inline-flex items-center text-sm"
          target="_blank"
          rel="noopener noreferrer"
        >
          Console <Icon icon="ExternalLink" class="ml-1 h-4 w-4" />
        </a>
      </template>
      <div
        class="mb-6 flex justify-between rounded-md border border-neutral-200 bg-neutral-50 px-3 py-2 dark:border-neutral-600 dark:bg-neutral-800"
      >
        <div class="text-sm">
          <span class="mr-2 font-medium text-neutral-700 dark:text-neutral-200">
            Redirect Url:
          </span>
          <span
            class="select-all text-neutral-600 dark:text-white"
            v-text="redirectUri"
          />
        </div>
        <IButtonCopy
          v-i-tooltip="$t('core::app.copy')"
          class="ml-3"
          :text="redirectUri"
          :success-message="$t('core::app.copied')"
        />
      </div>
      <div class="sm:flex sm:space-x-4">
        <IFormGroup
          label="Client ID"
          label-for="google_client_id"
          class="w-full"
        >
          <IFormInput
            id="google_client_id"
            v-model="form.google_client_id"
            autocomplete="off"
            name="google_client_id"
          />
        </IFormGroup>
        <IFormGroup
          label="Client Secret"
          label-for="google_client_secret"
          class="w-full"
        >
          <IFormInput
            id="google_client_secret"
            v-model="form.google_client_secret"
            type="password"
            autocomplete="off"
            name="google_client_secret"
          />
        </IFormGroup>
      </div>
      <template #footer>
        <IButton
          type="submit"
          :disabled="form.busy"
          variant="primary"
          :text="$t('core::app.save')"
        />
      </template>
    </ICard>
  </form>
</template>

<script setup>
import { computed } from 'vue'

import { useSettings } from '../../../composables/useSettings'

const {
  form,
  submit,
  isReady: componentReady,
  originalSettings,
} = useSettings()

const redirectUri = Innoclapps.config('url') + '/google/callback'

const isConfigured = computed(
  () =>
    originalSettings.value.google_client_secret &&
    originalSettings.value.google_client_id
)

function submitGoogleIntegrationSettings() {
  submit(() => window.location.reload())
}
</script>
