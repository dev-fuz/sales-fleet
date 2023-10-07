<template>
  <ILayout>
    <div class="mx-auto max-w-5xl">
      <ICard
        :header="$t('core::oauth.connected_accounts')"
        :overlay="isLoading"
      >
        <div v-if="hasAccounts" class="space-y-3">
          <OAuthAccount
            v-for="account in accounts"
            :key="account.id"
            :account="account"
          >
            <IButton
              size="sm"
              :text="$t('core::oauth.re_authenticate')"
              @click="reAuthenticate(account)"
            />
            <IButton
              v-if="account.authorizations.delete"
              size="sm"
              class="ml-1"
              variant="white"
              icon="Trash"
              @click="destroy(account.id)"
            />
          </OAuthAccount>
        </div>
        <div v-else v-show="!isLoading" class="text-center">
          <Icon icon="EmojiSad" class="mx-auto h-12 w-12 text-neutral-400" />
          <h3
            v-t="'core::oauth.no_accounts'"
            class="mt-2 text-sm font-medium text-neutral-800 dark:text-white"
          />
        </div>
      </ICard>
    </div>
    <router-view></router-view>
  </ILayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute } from 'vue-router'
import { useStore } from 'vuex'
import findIndex from 'lodash/findIndex'

import { useLoader } from '~/Core/composables/useLoader'

import OAuthAccount from './OAuthAccount.vue'

const { t } = useI18n()
const store = useStore()
const route = useRoute()

const { setLoading, isLoading } = useLoader()

const accounts = ref([])
const hasAccounts = computed(() => accounts.value.length > 0)

function reAuthenticate(account) {
  window.location.href = `${Innoclapps.config('url')}/${account.type}/connect`
}

async function destroy(id) {
  await Innoclapps.dialog().confirm(t('core::oauth.delete_warning'), {
    okText: t('core::app.confirm'),
  })
  await Innoclapps.request().delete(`oauth/accounts/${id}`)

  accounts.value.splice(findIndex(accounts.value, ['id', Number(id)]), 1)
  store.commit('emailAccounts/RESET')
  Innoclapps.success(t('core::oauth.deleted'))
}

function fetch() {
  setLoading(true)
  Innoclapps.request()
    .get('oauth/accounts')
    .then(({ data }) => {
      accounts.value = data

      if (route.query.reconnect) {
        reAuthenticate(
          data.find(account => account.id == route.query.reconnect)
        )
      }
    })
    .finally(() => setLoading(false))
}

fetch()
</script>
