<template>
  <FormFieldsGroup
    :field="field"
    :label="label"
    :field-id="fieldId"
    :form-id="formId"
    class="multiple"
  >
    <IFormGroup
      v-for="(phone, index) in value"
      v-show="!phone._delete"
      :key="index"
      class="rounded-md"
    >
      <div class="flex">
        <div class="relative flex grow items-stretch focus-within:z-10">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3">
            <Icon
              :icon="phone.type == 'mobile' ? 'DeviceMobile' : 'Phone'"
              class="h-5 w-5 text-neutral-500 dark:text-neutral-300"
            />
          </div>

          <IFormInput
            v-model="value[index].number"
            :rounded="false"
            class="rounded-l-md pl-10"
            :name="field.attribute + '.' + index + '.number'"
            @input="
              form.errors.clear(field.attribute + '.' + index + '.number'),
                searchDuplicateRecord(index, value[index].number)
            "
          />
        </div>

        <IDropdown adaptive-width>
          <template #toggle="{ toggle }">
            <IButton
              variant="white"
              class="relative -ml-px justify-between px-4 py-2 text-sm focus:z-10"
              :rounded="false"
              :size="false"
              @click="toggle"
            >
              <span class="max-w-[70px] truncate">
                {{
                  value[index].type
                    ? field.types[value[index].type]
                    : $t('contacts::fields.phone.types.type')
                }}
              </span>

              <Icon icon="ChevronDown" class="link h-4 w-4" />
            </IButton>
          </template>

          <IDropdownItem
            v-for="(typeLabel, id) in field.types"
            :key="id"
            :text="typeLabel"
            @click="value[index].type = id"
          />
        </IDropdown>

        <IButtonClose
          :rounded="false"
          variant="white"
          class="relative -ml-px rounded-r-md !px-2.5"
          @click="removePhone(index)"
        />
      </div>

      <IFormError
        v-text="form.getError(field.attribute + '.' + index + '.number')"
      />

      <IAlert
        v-if="duplicates[index]"
        dismissible
        class="mt-1"
        @dismissed="duplicates[index] = null"
      >
        <i18n-t
          scope="global"
          :keypath="field.checkDuplicatesWith.lang_keypath"
        >
          <template #display_name>
            <span class="font-medium">
              {{ duplicates[index].display_name }}
            </span>
          </template>
        </i18n-t>

        <div class="mt-4">
          <div class="-mx-2 -my-1.5 flex">
            <IButtonMinimal
              tag="a"
              :href="duplicates[index].path"
              target="_blank"
              rel="noopener noreferrer"
              variant="info"
              icon="ExternalLink"
              :text="$t('core::app.view_record')"
            />
          </div>
        </div>
      </IAlert>
    </IFormGroup>

    <div class="text-right">
      <a
        v-t="'contacts::fields.phone.add'"
        href="#"
        class="link mr-2 text-sm"
        @click.prevent="newPhone"
      />
    </div>
  </FormFieldsGroup>
</template>

<script setup>
import { computed, onMounted, ref, toRef, watch } from 'vue'
import cloneDeep from 'lodash/cloneDeep'
import debounce from 'lodash/debounce'
import isEqual from 'lodash/isEqual'
import omit from 'lodash/omit'
import reject from 'lodash/reject'

import { useForm } from '~/Core/composables/useFieldsForm'
import { useField } from '~/Core/composables/useFormField'
import propsDefinition from '~/Core/fields/Form/props'
import FormFieldsGroup from '~/Core/fields/FormFieldsGroup.vue'

const props = defineProps(propsDefinition)

const value = ref([])
const duplicates = ref({})
const form = useForm(props.formId)

const isDirty = computed(() => {
  if (!value.value) {
    return false
  }

  if (totalForDelete.value > 0) {
    return true
  }

  if (totalForInsert.value > 0) {
    return true
  }

  return !isEqual(value.value, realInitialValue.value)
})

/**
 * Get the predefined calling prefix
 */
const callingPrefix = computed(() => props.field.callingPrefix)

const totalForDelete = computed(
  () => value.value.filter(phone => phone._delete).length
)

const totalForInsert = computed(() => {
  return value.value.filter(
    phone =>
      !phone.id &&
      phone.number &&
      // Has only prefix value but the user did not added any number
      (!callingPrefix.value ||
        (callingPrefix.value && callingPrefix.value !== phone.number))
  ).length
})

const totalPhones = computed(() => value.value.length)

watch(totalPhones, newVal => {
  if (newVal === 0) {
    newPhone()
  }
})

function fill(form) {
  let phones = reject(cloneDeep(value.value), phone => phone._delete)

  if (callingPrefix.value) {
    // Remove phones with only prefix or deleted
    phones = reject(
      phones,
      phone =>
        !phone._delete &&
        callingPrefix.value &&
        phone.number.trim() === callingPrefix.value.trim()
    )
  }

  form.fill(
    props.field.attribute,
    phones
      .map(phone => omit(phone, 'id'))
      .filter(phone => Boolean(phone.number))
  )
}

function setInitialValue() {
  value.value = cloneDeep(props.field.value ? props.field.value : [])

  if (value.value.length === 0) {
    newPhone()

    realInitialValue.value = cloneDeep(value.value)
  }
}

function handleChange(val) {
  value.value = cloneDeep(val)

  if (totalPhones.value === 0) {
    newPhone()
  }

  realInitialValue.value = cloneDeep(value.value)
}

/**
 * Remove phone
 *
 * @param  {Number} index
 *
 * @return {Void}
 */
function removePhone(index) {
  duplicates.value[index] = null

  if (!value.value[index].id) {
    value.value.splice(index, 1)
  } else {
    value.value[index]._delete = true
  }

  form.errors.clear(props.field.attribute + '.' + index + '.number')

  if (
    totalPhones.value - totalForDelete.value === 0 ||
    (totalPhones.value - totalForDelete.value === 0 && totalForDelete.value > 0)
  ) {
    newPhone()
  }
}

/**
 * Add new phone
 *
 * @return {Void}
 */
function newPhone() {
  value.value.push({
    number: callingPrefix.value || '',
    type: props.field.type,
  })
}

/**
 * Search for duplicate record
 */
const searchDuplicateRecord = debounce((index, number) => {
  if (
    !checksForDuplicates.value ||
    props.resourceId ||
    !number ||
    (callingPrefix.value && callingPrefix.value === number)
  ) {
    duplicates.value[index] = null

    return
  }

  makeDuplicateCheckRequest(number).then(
    duplicate => (duplicates.value[index] = duplicate)
  )
}, 700)

const {
  field,
  label,
  fieldId,
  realInitialValue,
  initialize,
  checksForDuplicates,
  makeDuplicateCheckRequest,
} = useField(
  value,
  toRef(props, 'field'),
  props.formId,
  toRef(props, 'isFloating'),
  {
    isDirty,
    handleChange,
    setInitialValue,
    fill,
  }
)

onMounted(initialize)
</script>
