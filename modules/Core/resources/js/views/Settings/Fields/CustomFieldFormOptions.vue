<template>
  <IFormGroup>
    <div class="flex">
      <IFormLabel
        class="grow text-neutral-900 dark:text-neutral-100"
        :label="$t('core::fields.options')"
        required
      />
      <IButtonIcon
        v-show="!addingOptionsViaText"
        v-i-tooltip="$t('core::app.add_another')"
        icon="Plus"
        @click="newOptionAndFocus"
      />
    </div>
    <div v-if="form.options.length === 0 && !addingOptionsViaText" class="mt-2">
      <IAlert variant="info">
        <i18n-t
          scope="global"
          :keypath="'core::fields.custom.create_option_icon'"
          tag="div"
          class="flex"
        >
          <template #icon>
            <Icon
              icon="Plus"
              class="mx-1 h-5 w-5 cursor-pointer"
              @click="newOptionAndFocus"
            />
          </template>
        </i18n-t>

        <a
          href="#"
          class="mt-2 inline-flex items-center text-sm text-info-900 hover:text-info-700"
          @click.prevent="addingOptionsViaText = true"
        >
          {{ $t('core::fields.custom.or_add_options_via_text') }}
          <Icon icon="ArrowLeft" class="ml-1 mt-0.5 h-3 w-3" />
        </a>
      </IAlert>
    </div>
    <div v-if="addingOptionsViaText">
      <IFormTextarea
        v-model="textOptions"
        :placeholder="$t('core::fields.custom.text_options_each_on_new_line')"
      />

      <div
        class="mt-1 flex items-center justify-end divide-x divide-neutral-200 text-right text-sm"
      >
        <a
          v-t="'core::fields.custom.convert_text_to_options'"
          href="#"
          class="link"
          @click.prevent="convertTextToOptions"
        />

        <a
          v-t="'core::app.cancel'"
          href="#"
          class="link ml-2 pl-2"
          @click.prevent="cancelAddOptionsViaText"
        />
      </div>
    </div>
    <draggable
      v-bind="draggableOptions"
      :list="form.options"
      :item-key="(item, index) => index"
      handle=".option-draggable-handle"
      @sort="setDisplayOrder"
    >
      <template #item="{ element, index }">
        <div class="relative mt-3">
          <div class="group -mx-6 px-6">
            <div
              class="option-draggable-handle absolute -left-5 top-3 z-20 hidden focus-within:block group-hover:block hover:block"
            >
              <IButtonIcon
                icon="Selector"
                class="cursor-move text-neutral-400"
                icon-class="w-4 h-4"
              />
            </div>
            <div class="relative">
              <div
                v-if="element.id"
                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-neutral-500 dark:text-neutral-200"
                v-text="$t('core::app.id') + ': ' + element.id"
              />
              <IFormInput
                ref="optionNameRef"
                v-model="form.options[index].name"
                :class="['pr-20', { 'pl-14': element.id }]"
                @keydown.enter.prevent.stop="newOptionAndFocus"
                @keydown="form.errors.clear('options')"
              />
              <IPopover auto-placement class="w-72">
                <IButtonIcon
                  v-i-tooltip="$t('core::app.colors.color')"
                  icon="ColorSwatch"
                  :style="{ color: element.swatch_color }"
                  class="absolute right-11 top-2.5 -mt-px"
                />

                <template #popper>
                  <div class="p-4">
                    <IColorSwatch
                      v-model="form.options[index].swatch_color"
                      :swatches="swatches"
                    />
                  </div>
                </template>
              </IPopover>
              <IButtonIcon
                icon="X"
                class="absolute right-3 top-2.5"
                @click="removeOption(index)"
              />
            </div>
          </div>
        </div>
      </template>
    </draggable>
    <IFormError v-text="form.getError('options')" />
  </IFormGroup>
</template>

<script setup>
import { nextTick, ref } from 'vue'
import draggable from 'vuedraggable'

import { useDraggable } from '~/Core/composables/useDraggable'

const props = defineProps({
  form: { required: true, type: Object },
})

const { draggableOptions } = useDraggable()
const optionNameRef = ref(null)
const addingOptionsViaText = ref(false)
const textOptions = ref('')
const swatches = Innoclapps.config('favourite_colors')

/** Set the display order of the options based at the current sorting */
function setDisplayOrder() {
  props.form.options.forEach((option, index) => {
    option.display_order = index + 1
  })
}

function newOptionAndFocus() {
  newOption()
  // Focus the last option
  nextTick(() => {
    optionNameRef.value.focus()
  })
}

function newOption(name = null) {
  props.form.options.push({
    name: name,
    display_order: props.form.options.length + 1,
    swatch_color: null,
  })
}

function cancelAddOptionsViaText() {
  addingOptionsViaText.value = false
}

function convertTextToOptions() {
  if (!textOptions.value) {
    return
  }

  textOptions.value.split('\n').forEach(option => option && newOption(option))

  cancelAddOptionsViaText()
}

async function removeOption(index) {
  let option = props.form.options[index]

  if (option.id) {
    await Innoclapps.dialog().confirm()
  }

  props.form.options.splice(index, 1)
}
</script>
