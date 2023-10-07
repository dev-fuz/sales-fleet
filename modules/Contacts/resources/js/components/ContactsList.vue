<template>
  <div>
    <h2
      class="flex items-center justify-between font-medium text-neutral-800 dark:text-white"
    >
      <span>
        {{ cardTitle }}
        <span
          v-show="total > 0"
          class="text-sm font-normal text-neutral-400"
          v-text="'(' + total + ')'"
        />
      </span>

      <span>
        <a
          v-if="total > limit && !showAll"
          v-t="'core::app.show_all'"
          href="#"
          class="link shrink-0 text-sm font-normal"
          @click.prevent="showAll = true"
        />
        <a
          v-if="total > limit && showAll"
          v-t="'core::app.show_less'"
          href="#"
          class="link shrink-0 text-sm font-normal"
          @click.prevent="showAll = false"
        />
        <span
          v-if="total > limit"
          v-show="!showAll"
          v-t="{ path: 'core::app.has_more', args: { count: total - limit } }"
          class="ml-1 text-xs font-normal text-neutral-400"
        />
      </span>
    </h2>
    <ul
      class="divide-y divide-neutral-200 dark:divide-neutral-700"
      :class="{ '-mb-4': total > 0 }"
    >
      <li
        v-for="(contact, index) in localContacts"
        v-show="index <= limit - 1 || showAll"
        :key="contact.id"
        class="group flex items-center space-x-3 py-4"
      >
        <div class="shrink-0">
          <IAvatar :src="contact.avatar_url"></IAvatar>
        </div>

        <div class="min-w-0 flex-1 truncate">
          <a
            :href="contact.path"
            class="text-sm font-medium text-neutral-800 hover:text-neutral-500 focus:outline-none dark:text-white dark:hover:text-neutral-300"
            @click.prevent="
              floatResource({
                resourceName: 'contacts',
                resourceId: contact.id,
                mode: floatMode,
              })
            "
            v-text="contact.display_name"
          />
          <p
            class="text-sm text-neutral-500 dark:text-neutral-300"
            v-text="contact.job_title"
          />
        </div>

        <div class="block shrink-0 md:hidden md:group-hover:block">
          <div class="flex items-center space-x-1">
            <IButton
              v-show="$gate.allows('view', contact)"
              size="sm"
              variant="white"
              :to="contact.path"
              :text="$t('core::app.view_record')"
            />
            <slot name="actions" :contact="contact"></slot>
          </div>
        </div>
      </li>
    </ul>
    <p
      v-show="!hasContacts"
      class="text-sm text-neutral-500 dark:text-neutral-300"
      v-text="emptyText"
    />
    <slot name="tail"></slot>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import castArray from 'lodash/castArray'
import orderBy from 'lodash/orderBy'

import { useFloatingResourceModal } from '~/Core/composables/useFloatingResourceModal'

const props = defineProps({
  contacts: { type: [Object, Array], required: true },
  limit: { type: Number, default: 3 },
  emptyText: String,
  title: String,
  floatMode: { type: String, default: 'detail' },
})

const { t } = useI18n()
const { floatResource } = useFloatingResourceModal()

const showAll = ref(false)

const localContacts = computed(() =>
  orderBy(castArray(props.contacts), contact => new Date(contact.created_at), [
    'asc',
  ])
)

const total = computed(() => localContacts.value.length)

const hasContacts = computed(() => total.value > 0)

const cardTitle = computed(() => props.title || t('contacts::contact.contacts'))
</script>
