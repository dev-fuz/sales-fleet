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

import LoggedCallsCard from './components/LoggedCallsCard.vue'
import CallsTab from './components/RecordTabCall.vue'
import CallsTabPanel from './components/RecordTabCallPanel.vue'
import RecordTabTimelineCall from './components/RecordTabTimelineCall.vue'
import SettingsCalls from './components/SettingsCalls.vue'
import SettingsTwilio from './components/SettingsTwilio.vue'
import { useCallOutcomes } from './composables/useCallOutcomes'
import DetailPhoneCallableField from './fields/Detail/PhoneCallableField.vue'
import IndexPhoneCallableField from './fields/Index/PhoneCallableField.vue'
import VoIP from './VoIP'

const { setCallOutcomes } = useCallOutcomes()

if (window.Innoclapps) {
  Innoclapps.booting((Vue, router) => {
    Vue.component('CallsTab', CallsTab)
    Vue.component('CallsTabPanel', CallsTabPanel)
    Vue.component('RecordTabTimelineCall', RecordTabTimelineCall)
    Vue.component('LoggedCallsCard', LoggedCallsCard)

    // Fields
    Vue.component('DetailPhoneCallableField', DetailPhoneCallableField)
    Vue.component('IndexPhoneCallableField', IndexPhoneCallableField)

    // Voip
    if (
      Object.hasOwn(Innoclapps.appConfig, 'voip') &&
      Innoclapps.appConfig.voip.client &&
      Vue.config.globalProperties.$gate.userCan('use voip')
    ) {
      const VoIPInstance = new VoIP(Innoclapps.appConfig.voip.client)
      Vue.config.globalProperties.$voip = VoIPInstance
      Vue.component('CallComponent', VoIPInstance.callComponent)
    }

    setCallOutcomes(Innoclapps.config('calls.outcomes') || [])

    router.addRoute('settings', {
      path: 'integrations/twilio',
      component: SettingsTwilio,
      name: 'settings-integrations-twilio',
      meta: {
        title: 'Twilio',
      },
    })

    router.addRoute('settings', {
      path: 'calls',
      name: 'calls-settings',
      component: SettingsCalls,
      meta: {
        title: i18n.t('calls::call.calls'),
      },
    })
  })
}
