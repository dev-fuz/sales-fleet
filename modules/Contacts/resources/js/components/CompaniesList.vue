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
        v-for="(company, index) in localCompanies"
        v-show="index <= limit - 1 || showAll"
        :key="company.id"
        class="group flex items-center space-x-3 py-4"
      >
        <div class="shrink-0">
          <CompanyImage />
        </div>

        <div class="min-w-0 flex-1 truncate">
          <a
            :href="company.path"
            class="text-sm font-medium text-neutral-800 hover:text-neutral-500 focus:outline-none dark:text-white dark:hover:text-neutral-300"
            @click.prevent="
              floatResource({
                resourceName: 'companies',
                resourceId: company.id,
                mode: floatMode,
              })
            "
            v-text="company.display_name"
          />
          <p>
            <a
              v-show="company.domain"
              :href="'http://' + company.domain"
              target="_blank"
              rel="noopener noreferrer"
              class="inline-flex items-center text-sm text-neutral-500 hover:text-neutral-400 focus:outline-none dark:text-neutral-400 dark:hover:text-neutral-500"
            >
              {{ company.domain }}
              <Icon icon="ExternalLink" class="ml-1 h-4 w-4" />
            </a>
          </p>
        </div>

        <div class="block shrink-0 md:hidden md:group-hover:block">
          <div class="flex items-center space-x-1">
            <IButton
              v-show="$gate.allows('view', company)"
              size="sm"
              variant="white"
              :to="company.path"
              :text="$t('core::app.view_record')"
            />
            <slot name="actions" :company="company"></slot>
          </div>
        </div>
      </li>
    </ul>
    <p
      v-show="!hasCompanies"
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

import CompanyImage from './CompanyImage.vue'

const props = defineProps({
  companies: { type: [Object, Array], required: true },
  limit: { type: Number, default: 3 },
  emptyText: String,
  title: String,
  floatMode: { type: String, default: 'detail' },
})

const { t } = useI18n()
const { floatResource } = useFloatingResourceModal()

const showAll = ref(false)

const localCompanies = computed(() =>
  orderBy(castArray(props.companies), company => new Date(company.created_at), [
    'asc',
  ])
)

const total = computed(() => localCompanies.value.length)

const hasCompanies = computed(() => total.value > 0)

const cardTitle = computed(
  () => props.title || t('contacts::company.companies')
)
</script>
