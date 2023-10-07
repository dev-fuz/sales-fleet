<template>
  <form @submit.prevent="submit" @keydown="form.onKeydown($event)">
    <ICard
      :header="$t('core::mail_template.mail_templates')"
      :overlay="isLoading"
    >
      <template #actions>
        <div class="flex flex-col items-center sm:flex-row">
          <div class="flex items-center sm:mr-3">
            <span
              v-t="'core::app.locale'"
              class="mr-2 text-sm font-medium text-neutral-700 dark:text-neutral-300"
            />
            <DropdownSelectInput
              v-model="locale"
              :items="locales"
              placement="bottom-end"
              @change="fetch"
            >
              <template #default="{ toggle }">
                <button
                  type="button"
                  class="inline-flex w-full justify-center rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-700 shadow-sm hover:bg-neutral-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 focus:ring-offset-neutral-100"
                  @click="toggle"
                >
                  {{ locale }}
                </button>
              </template>
            </DropdownSelectInput>
          </div>
          <div class="flex items-center">
            <span
              v-t="'core::mail_template.template'"
              class="mr-2 text-sm font-medium text-neutral-700 dark:text-neutral-300"
            />
            <DropdownSelectInput
              v-model="template"
              :items="templates"
              placement="bottom-end"
              value-key="id"
              label-key="name"
              @change="setActive"
            >
              <template #header>
                <div
                  class="border-b border-neutral-200 dark:border-neutral-700"
                >
                  <div class="px-4 py-3">
                    <p
                      v-t="'core::mail_template.choose_to_edit'"
                      class="text-sm font-medium text-neutral-900 dark:text-white"
                    />
                  </div>
                </div>
              </template>
              <template #default="{ toggle }">
                <button
                  type="button"
                  class="inline-flex w-full justify-center rounded-md border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-700 shadow-sm hover:bg-neutral-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 focus:ring-offset-neutral-100"
                  @click="toggle"
                >
                  <Icon icon="Mail" class="mr-2 h-5 w-5" />
                  {{ template.name }}
                </button>
              </template>
            </DropdownSelectInput>
          </div>
        </div>
      </template>

      <IFormGroup
        :label="$t('core::mail_template.subject')"
        label-for="subject"
        required
      >
        <IFormInput id="subject" v-model="form.subject" name="subject" />
        <IFormError v-text="form.getError('subject')" />
      </IFormGroup>

      <IFormGroup>
        <div class="mb-2 flex items-center">
          <!--
              <DropdownSelectInput :items="['HTML', 'Text']"
              v-model="templateType" />
            -->
          <IFormLabel :label="$t('core::mail_template.message')" required />
        </div>
        <div v-show="isHtmlTemplateType">
          <Editor
            v-if="componentReady"
            v-model="form.html_template"
            :config="{ urlconverter_callback: placeholderURLConverter }"
            :auto-completer="editorAutoCompleter"
          />
        </div>
        <div v-show="!isHtmlTemplateType">
          <IFormTextarea
            v-model="form.text_template"
            :rows="8"
            name="text_template"
          />
        </div>
        <IFormError v-text="form.getError('html_template')" />
        <IFormError v-text="form.getError('text_template')" />
      </IFormGroup>

      <MailPlaceholders :placeholders="template.placeholders" />

      <template #footer>
        <div class="text-right">
          <IButton
            type="submit"
            :disabled="form.busy"
            :text="$t('core::app.save')"
          />
        </div>
      </template>
    </ICard>
  </form>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import find from 'lodash/find'
import findIndex from 'lodash/findIndex'

import MailPlaceholders from '~/Core/components/MailPlaceholders.vue'
import { useApp } from '~/Core/composables/useApp'
import { useForm } from '~/Core/composables/useForm'
import { useLoader } from '~/Core/composables/useLoader'

const { t } = useI18n()
const { setLoading, isLoading } = useLoader()
const { locales } = useApp()

const componentReady = ref(false)
const { form } = useForm()
const templateType = ref('HTML') // or text
const templates = ref([]) // in locale templates
const template = ref({}) // active template
const locale = ref('en') // default selected locale

const isHtmlTemplateType = computed(() => templateType.value === 'HTML')

const editorAutoCompleter = computed(() => ({
  id: 'placeholders',
  trigger: '{',
  list: template.value.placeholders
    .filter(p => p.tag !== 'action_button')
    .map(p => ({
      value: `${p.interpolation_start} ${p.tag} ${p.interpolation_end}`,
      text: `${p.interpolation_start} ${p.tag} ${p.interpolation_end} - ${p.description}`,
    })),
}))

function submit() {
  form.put(`/mailables/${template.value.id}`).then(data => {
    let index = findIndex(templates.value, ['id', Number(data.id)])
    templates.value[index] = data

    Innoclapps.success(t('core::mail_template.updated'))
  })
}

function fetch() {
  setLoading(true)

  Innoclapps.request()
    .get(`/mailables/${locale.value}/locale`)
    .then(({ data }) => {
      templates.value = data
      // If previous template selected, keep it selected
      // Otherwise find the template with the same name
      // We find by name because the template may have different id
      setActive(
        Object.keys(template.value).length === 0
          ? data[0]
          : find(templates.value, ['name', template.value.name])
      )

      componentReady.value = true
    })
    .finally(() => setLoading(false))
}

function setActive(mailableTemplate) {
  template.value = mailableTemplate

  form.set({
    subject: mailableTemplate.subject,
    html_template: mailableTemplate.html_template,
    text_template: mailableTemplate.text_template,
  })
}

/**
 * Merge field url converter callback
 *
 * @param  {String} url
 * @param  {Node} node
 * @param  {Boolean} on_save
 * @param  {String} name
 *
 * @return {String}
 */
// eslint-disable-next-line no-unused-vars
function placeholderURLConverter(url, node, on_save, name) {
  if (url.indexOf('%7B%7B%20') > -1 && url.indexOf('%20%7D%7D') > -1) {
    url = url.replace('%7B%7B%20', '{').replace('%20%7D%7D', '}')
  }

  return url
}

function prepareComponent() {
  fetch()
}

// Mail templates component must make the request each time is created
// this helps to seed any missing templates in database
prepareComponent()
</script>
