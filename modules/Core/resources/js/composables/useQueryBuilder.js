/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.3.1
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2023 KONKORD DIGITAL
 */
import { computed, unref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useStore } from 'vuex'

export function useQueryBuilder(identifier, view) {
  const store = useStore()

  const viewName = unref(view || identifier)

  /**
   * Get the currently rules in the builder
   */
  const queryBuilderRules = computed({
    set(newValue) {
      store.commit('filters/SET_BUILDER_RULES', {
        identifier: unref(identifier),
        view: viewName,
        rules: newValue,
      })
    },
    get() {
      return (
        store.getters['filters/getBuilderRules'](unref(identifier), viewName) ||
        {}
      )
    },
  })

  /**
   * Indicates wheter there are rules in the builder
   */
  const hasBuilderRules = computed(
    () =>
      queryBuilderRules.value.children &&
      queryBuilderRules.value.children.length > 0
  )

  /**
   * Indicates whether the rules in the builder are valid
   */
  const rulesAreValid = computed({
    set(newValue) {
      store.commit('filters/SET_RULES_ARE_VALID', {
        identifier: unref(identifier),
        view: viewName,
        value: newValue,
      })
    },
    get() {
      return store.getters['filters/rulesAreValid'](unref(identifier), viewName)
    },
  })

  /**
   * Indicates whether there are rules applied in the query builder
   */
  const hasRulesApplied = computed({
    set(newValue) {
      store.commit('filters/SET_HAS_RULES_APPLIED', {
        identifier: unref(identifier),
        view: viewName,
        value: newValue,
      })
    },
    get() {
      return store.getters['filters/hasRulesApplied'](
        unref(identifier),
        viewName
      )
    },
  })

  /**
   * Indicates whether the filters rules are visible
   */
  const rulesAreVisible = computed({
    set(newValue) {
      store.commit('filters/SET_RULES_VISIBLE', {
        identifier: unref(identifier),
        view: viewName,
        visible: newValue,
      })
    },
    get() {
      return store.getters['filters/rulesAreVisible'](
        unref(identifier),
        viewName
      )
    },
  })

  /**
   * Toggle the query builder visibility
   */
  function toggleFiltersRules() {
    rulesAreVisible.value = !rulesAreVisible.value
  }

  /**
   * Find rule from the query builder from the given rule attribute ID
   */
  function findRule(ruleId) {
    return store.getters['filters/findRuleInQueryBuilder'](
      unref(identifier),
      viewName,
      ruleId
    )
  }

  /**
   * Reset the query builder rules
   */
  function resetQueryBuilderRules() {
    store.commit('filters/RESET_BUILDER_RULES', {
      identifier: unref(identifier),
      view: viewName,
    })
  }

  return {
    queryBuilderRules,
    rulesAreVisible,
    hasBuilderRules,
    hasRulesApplied,
    rulesAreValid,
    toggleFiltersRules,
    findRule,
    resetQueryBuilderRules,
  }
}

export function useQueryBuilderLabels() {
  const { t } = useI18n()

  const labels = {
    operatorLabels: {
      is: t('core::filters.operators.is'),
      was: t('core::filters.operators.was'),
      equal: t('core::filters.operators.equal'),
      not_equal: t('core::filters.operators.not_equal'),
      in: t('core::filters.operators.in'),
      not_in: t('core::filters.operators.not_in'),
      less: t('core::filters.operators.less'),
      less_or_equal: t('core::filters.operators.less_or_equal'),
      greater: t('core::filters.operators.greater'),
      greater_or_equal: t('core::filters.operators.greater_or_equal'),
      between: t('core::filters.operators.between'),
      not_between: t('core::filters.operators.not_between'),
      begins_with: t('core::filters.operators.begins_with'),
      not_begins_with: t('core::filters.operators.not_begins_with'),
      contains: t('core::filters.operators.contains'),
      not_contains: t('core::filters.operators.not_contains'),
      ends_with: t('core::filters.operators.ends_with'),
      not_ends_with: t('core::filters.operators.not_ends_with'),
      is_empty: t('core::filters.operators.is_empty'),
      is_not_empty: t('core::filters.operators.is_not_empty'),
      is_null: t('core::filters.operators.is_null'),
      is_not_null: t('core::filters.operators.is_not_null'),
    },
    matchType: t('core::filters.match_type'),
    matchTypeAll: t('core::filters.match_type_all'),
    matchTypeAny: t('core::filters.match_type_any'),
    addRule: t('core::filters.add_condition'),
    removeRule: '<span aria-hidden="true">&times;</span>',
    addGroup: t('core::filters.add_group'),
    removeGroup: '<span aria-hidden="true">&times;</span>',
  }

  return { labels }
}
