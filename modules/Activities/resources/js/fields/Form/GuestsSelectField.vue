<template>
  <FormFieldsGroup
    :field="field"
    :label="label"
    :form-id="formId"
    :field-id="fieldId"
  >
    <div class="block">
      <div class="inline-block">
        <GuestsSelect
          ref="guestsSelectRef"
          v-model="value"
          :guests="guests"
          :contacts="contacts"
        />
      </div>
    </div>
  </FormFieldsGroup>
</template>

<script setup>
import { computed, nextTick, onMounted, ref, toRef } from 'vue'
import isNil from 'lodash/isNil'

import { useField } from '~/Core/composables/useFormField'
import propsDefinition from '~/Core/fields/Form/props'
import FormFieldsGroup from '~/Core/fields/FormFieldsGroup.vue'

import GuestsSelect from '../../components/ActivityGuestsSelect.vue'

const props = defineProps(propsDefinition)

const guestsSelectRef = ref(null)
const value = ref('')
const guests = ref([])

const contacts = computed(() => props.field.contacts || [])

function handleChange(val) {
  value.value = val
  realInitialValue.value = val
  guests.value = val

  // Checking it the ref set selected guest is visible as when
  // the form is resetting the field via the handleChange method the
  // field may be destroyed already if within v-if statement and will not exists
  nextTick(
    () => guestsSelectRef.value && guestsSelectRef.value.setSelectedGuests()
  )
}

function setInitialValue() {
  guests.value = props.field.value || []
  value.value = !isNil(props.field.value)
    ? props.field.value
    : {
        contacts: [],
        users: [],
      }
}

const { field, label, fieldId, realInitialValue, initialize } = useField(
  value,
  toRef(props, 'field'),
  props.formId,
  toRef(props, 'isFloating'),
  {
    handleChange,
    setInitialValue,
  }
)

onMounted(initialize)
</script>
