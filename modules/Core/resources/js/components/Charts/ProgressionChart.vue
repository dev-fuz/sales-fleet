<template>
  <Card
    no-body
    class="h-56 md:h-52"
    :overflow-hidden="false"
    :card="card"
    :request-query-string="requestQueryString"
    @retrieved="result = $event.card.value"
  >
    <template v-for="(_, name) in $slots" #[name]="slotData">
      <slot :name="name" v-bind="slotData" />
    </template>
    <div class="relative" :class="variant">
      <BaseProgressionChart
        v-if="hasChartData"
        class="px-1"
        :chart-data="chartData"
        :amount-value="card.amount_value"
      />
      <p
        v-else
        v-t="'core::app.not_enough_data'"
        class="mt-12 text-center text-sm text-neutral-400 dark:text-neutral-300"
      />
    </div>
  </Card>
</template>

<script setup>
import { computed, shallowRef } from 'vue'

import { useChart } from '../../composables/useChart'

import BaseProgressionChart from './Base/ProgressionChart.vue'

const props = defineProps({
  card: { required: true, type: Object },
  requestQueryString: {
    type: Object,
    default() {
      return {}
    },
  },
})

const result = shallowRef(props.card.value)
const variant = computed(() => props.card.color || 'chart-primary')
const { chartData, hasData: hasChartData } = useChart(result)
</script>
