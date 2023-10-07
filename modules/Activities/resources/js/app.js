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
import i18n from '~/Core/i18n'

import CreateActivityModal from './components/CreateActivityModal.vue'
import MyActivitiesCard from './components/MyActivitiesCard.vue'
import ActivitiesTab from './components/RecordTabActivity.vue'
import ActivitiesTabPanel from './components/RecordTabActivityPanel.vue'
import RecordTabTimelineActivity from './components/RecordTabTimelineActivity.vue'
import SettingsActivities from './components/SettingsActivities.vue'
import { useActivityTypes } from './composables/useActivityTypes'
import FormActivityDueDateField from './fields/Form/ActivityDueDateField.vue'
import FormActivityEndDateField from './fields/Form/ActivityEndDateField.vue'
import FormActivityTypeField from './fields/Form/ActivityTypeField.vue'
import FormGuestsSelectField from './fields/Form/GuestsSelectField.vue'
import IndexActivityDueDateField from './fields/Index/ActivityDueDateField.vue'
import IndexActivityEndDateField from './fields/Index/ActivityEndDateField.vue'
import IndexActivityTypeField from './fields/Index/ActivityTypeField.vue'
import routes from './routes'

const { setActivityTypes } = useActivityTypes()

if (window.Innoclapps) {
  Innoclapps.booting(function (Vue, router) {
    Vue.component('MyActivitiesCard', MyActivitiesCard)
    Vue.component('RecordTabTimelineActivity', RecordTabTimelineActivity)
    Vue.component('CreateActivityModal', CreateActivityModal)

    // Fields
    Vue.component('FormActivityDueDateField', FormActivityDueDateField)
    Vue.component('FormActivityEndDateField', FormActivityEndDateField)
    Vue.component('FormGuestsSelectField', FormGuestsSelectField)
    Vue.component('FormActivityTypeField', FormActivityTypeField)
    Vue.component('IndexActivityTypeField', IndexActivityTypeField)
    Vue.component('IndexActivityDueDateField', IndexActivityDueDateField)
    Vue.component('IndexActivityEndDateField', IndexActivityEndDateField)

    // Tabs
    Vue.component('ActivitiesTab', ActivitiesTab)
    Vue.component('ActivitiesTabPanel', ActivitiesTabPanel)

    setActivityTypes(Innoclapps.config('activities.types') || [])

    // Routes
    routes.forEach(route => router.addRoute(route))

    router.addRoute('settings', {
      path: 'activities',
      name: 'activity-settings',
      component: SettingsActivities,
      meta: {
        title: i18n.t('activities::activity.activities'),
      },
    })
  })
}
