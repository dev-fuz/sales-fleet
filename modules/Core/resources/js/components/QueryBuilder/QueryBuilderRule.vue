<template>
  <div
    class="relative justify-between last:!mb-0 odd:my-4 odd:border-t odd:border-neutral-200 odd:pt-4 dark:odd:border-neutral-600 sm:flex sm:items-center"
  >
    <div
      class="mb-1 flex w-full flex-col justify-start sm:mb-0 sm:mr-3 sm:w-auto sm:flex-row sm:items-center sm:space-x-3"
    >
      <div class="mb-1 flex items-center sm:mb-0">
        <div
          v-if="rule.helpText"
          v-i-tooltip.bottom.light="rule.helpText"
          class="-ml-1 mr-1"
        >
          <Icon
            icon="QuestionMarkCircle"
            class="h-4 w-4 text-neutral-500 hover:text-neutral-700 dark:text-white dark:hover:text-neutral-300"
          />
        </div>
        <label
          class="max-w-xs truncate whitespace-nowrap text-sm font-medium text-neutral-800 dark:text-neutral-200"
          v-text="rule.label"
        />
      </div>

      <ICustomSelect
        v-if="showOperands"
        v-model="selectFieldOperand"
        class="mb-1 w-52 sm:mb-0"
        size="sm"
        :disabled="readOnly"
        :clearable="false"
        :option-label-provider="operand => operand[operand.labelKey]"
        :options="rule.operands"
        @option:selected="handleOperandSelected"
      />

      <IFormSelect
        v-if="!rule.isStatic"
        v-show="!hasOnlyOneOperator"
        size="sm"
        :value="query.operator"
        :disabled="readOnly"
        class="w-auto bg-none"
        @input="
          $store.commit('filters/UPDATE_QUERY_OPERATOR', {
            query: query,
            value: $event,
          })
        "
      >
        <option
          v-for="operator in operators"
          :key="operator"
          :value="operator"
          :selected="operator == query.operator"
        >
          {{
            labels.operatorLabels[operator]
              ? labels.operatorLabels[operator]
              : operator
          }}
        </option>
      </IFormSelect>
    </div>

    <div v-show="!isNullable" class="grow">
      <component
        :is="rulesComponents[operand ? operand.rule.component : rule.component]"
        :query="query"
        :rule="operand ? operand.rule : rule"
        :index="index"
        :labels="labels"
        :operand="operand"
        :read-only="readOnly"
        :operator="query.operator"
        :is-nullable="isNullable"
        :is-between="isBetween"
      />
    </div>

    <IButtonIcon
      v-if="!readOnly"
      icon="X"
      class="absolute right-0 top-1 sm:relative sm:right-auto sm:top-auto sm:ml-3"
      icon-class="h-4 w-4 sm:h-5 sm:w-5"
      @click="requestRemove"
    />
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useStore } from 'vuex'
import cloneDeep from 'lodash/cloneDeep'
import find from 'lodash/find'

import CheckboxRule from './Rules/CheckboxRule.vue'
import DateRule from './Rules/DateRule.vue'
import MultiSelectRule from './Rules/MultiSelectRule.vue'
import NullableRule from './Rules/NullableRule.vue'
import NumberRule from './Rules/NumberRule.vue'
import NumericRule from './Rules/NumericRule.vue'
import RadioRule from './Rules/RadioRule.vue'
import SelectRule from './Rules/SelectRule.vue'
import StaticRule from './Rules/StaticRule.vue'
import TextRule from './Rules/TextRule.vue'
import { isBetweenOperator, isNullableOperator } from './utils'

const emit = defineEmits(['child-deletion-requested'])

const props = defineProps(['query', 'index', 'rule', 'labels', 'readOnly'])

defineOptions({
  name: 'Rule',
  inheritAttrs: false,
})

const rulesComponents = {
  'numeric-rule': NumericRule,
  'checkbox-rule': CheckboxRule,
  'date-rule': DateRule,
  'number-rule': NumberRule,
  'radio-rule': RadioRule,
  'select-rule': SelectRule,
  'multi-select-rule': MultiSelectRule,
  'text-rule': TextRule,
  'static-rule': StaticRule,
  'nullable-rule': NullableRule,
}

const store = useStore()

const selectFieldOperand = ref(null)

/**
 * Get the selected opereand
 */
const operand = computed(() =>
  find(props.rule.operands, ['value', props.query.operand])
)

/**
 * Inicates whether the rule as operand with rule
 */
const hasOperandWithRule = computed(() => operand.value && operand.value.rule)

/**
 * Get the rule operators
 */
const operators = computed(() => {
  if (!hasOperandWithRule.value) {
    return props.rule.operators
  }

  return operand.value.rule.operators
})

/**
 * Indicates whether the rules has only one operator
 */
const hasOnlyOneOperator = computed(() => operators.value.length === 1)

/**
 * Indicates whether the operands should be shown
 */
const showOperands = computed(() => {
  if (
    props.rule.isStatic ||
    (Object.hasOwn(props.rule, 'hideOperands') && props.rule.hideOperands)
  ) {
    return false
  }

  return props.rule.operands && props.rule.operands.length > 0
})

/**
 * Indicates whether the rule operator is between
 */
const isBetween = computed(() => isBetweenOperator(props.query.operator))

/**
 * Indicates whether the rule operator is nullable
 */
const isNullable = computed(() => isNullableOperator(props.query.operator))

/**
 * Handle the operand selected event
 */
function handleOperandSelected(operand) {
  store.commit('filters/UPDATE_QUERY_OPERAND', {
    query: props.query,
    value: operand[operand.valueKey],
  })

  // When operand is changed, set the first operator as active
  if (operand.rule.operators && operand.rule.operators.length > 0) {
    store.commit('filters/UPDATE_QUERY_OPERATOR', {
      query: props.query,
      value: operand.rule.operators[0],
    })
  }
}

/**
 * Request rule remove
 *
 * @return {Void}
 */
function requestRemove() {
  emit('child-deletion-requested', props.index)
}

onMounted(() => {
  selectFieldOperand.value = operand.value && cloneDeep(operand.value)
})
</script>
