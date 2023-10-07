<template>
  <div>
    <IFormGroup
      v-if="withTimezoneField"
      :label="$t('core::app.timezone')"
      label-for="timezone"
    >
      <TimezoneInput
        :model-value="form.timezone"
        @update:model-value="$emit('update:timezone', $event)"
      />
      <IFormError v-text="form.getError('timezone')" />
    </IFormGroup>
    <IFormGroup
      v-if="withLocaleField"
      :label="$t('core::app.locale')"
      label-for="locale"
    >
      <ICustomSelect
        input-id="locale"
        :model-value="form.locale"
        :clearable="false"
        :options="locales"
        @update:model-value="$emit('update:locale', $event)"
      >
      </ICustomSelect>
      <IFormError v-text="form.getError('locale')" />
    </IFormGroup>
    <IFormGroup
      :label="$t('core::settings.date_format')"
      label-for="date_format"
    >
      <DateFormatInput
        :model-value="form.date_format"
        @update:model-value="$emit('update:dateFormat', $event)"
      />
      <IFormError v-text="form.getError('date_format')" />
    </IFormGroup>
    <IFormGroup
      :label="$t('core::settings.time_format')"
      label-for="time_format"
    >
      <TimeFormatInput
        :model-value="form.time_format"
        @update:model-value="$emit('update:timeFormat', $event)"
      />
      <IFormError v-text="form.getError('time_format')" />
    </IFormGroup>
    <IFormGroup
      :label="$t('core::settings.first_day_of_week')"
      label-for="first_day_of_week"
    >
      <!-- http://chartsbin.com/view/41671 -->
      <WeekDaySelectInput
        :model-value="form.first_day_of_week"
        :only="[1, 6, 0]"
        @update:model-value="$emit('update:firstDayOfWeek', $event)"
      />
      <IFormError v-text="form.getError('first_day_of_week')" />
    </IFormGroup>
  </div>
</template>

<script setup>
import { computed } from 'vue'

import DateFormatInput from '~/Core/components/DateFormatInput.vue'
import TimeFormatInput from '~/Core/components/TimeFormatInput.vue'
import TimezoneInput from '~/Core/components/TimezoneInput.vue'
import WeekDaySelectInput from '~/Core/components/WeekDaySelectInput.vue'
import { useApp } from '~/Core/composables/useApp'

defineEmits([
  'update:firstDayOfWeek',
  'update:timeFormat',
  'update:dateFormat',
  'update:locale',
  'update:timezone',
])

const props = defineProps({
  firstDayOfWeek: {},
  timeFormat: {},
  dateFormat: {},
  locale: {},
  timezone: {},
  form: { required: true, type: Object },
  exclude: { type: Array, default: () => [] },
})

const { locales } = useApp()

const withTimezoneField = computed(
  () => props.exclude.indexOf('timezone') === -1
)

const withLocaleField = computed(() => props.exclude.indexOf('locale') === -1)
</script>
