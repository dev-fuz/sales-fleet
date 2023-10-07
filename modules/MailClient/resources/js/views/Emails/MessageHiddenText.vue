<template>
  <div v-show="text">
    <IButton
      v-i-tooltip="$t('mailclient::mail.trimmed_content')"
      v-i-toggle="textId"
      :size="false"
      variant="white"
      class="mt-4 inline-flex rounded-md px-1.5 py-0"
      icon="DotsHorizontal"
    />
    <div :id="textId" class="mt-5 hidden">
      <HtmlableLightbox :html="text" />
    </div>
  </div>
</template>

<script setup>
import { randomString } from '@/utils'

import HtmlableLightbox from '~/Core/components/Lightbox/HtmlableLightbox.vue'

defineProps({
  text: { required: true },
})

const textId = randomString()
</script>
