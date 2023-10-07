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

import CreateDealModal from './components/CreateDealModal.vue'
import DealFloatingModal from './components/DealFloatingModal.vue'
import DealPresentationCard from './components/DealPresentationCard.vue'
import SettingsDeals from './components/SettingsDeals.vue'
import { useLostReasons } from './composables/useLostReasons'
import { usePipelines } from './composables/usePipelines'
import FormLostReasonField from './fields/Form/LostReasonField.vue'
import FormPipelineStageField from './fields/Form/PipelineStageField.vue'
import IndexLostReasonField from './fields/Index/LostReasonField.vue'
import IndexPipelineStageField from './fields/Index/PipelineStageField.vue'
import DealsPipelinesCreate from './views/DealsPipelinesCreate.vue'
import DealsPipelinesEdit from './views/DealsPipelinesEdit.vue'
import routes from './routes'

const { setPipelines } = usePipelines()
const { setLostReasons } = useLostReasons()

if (window.Innoclapps) {
  Innoclapps.booting((Vue, router) => {
    Vue.component('DealPresentationCard', DealPresentationCard)
    Vue.component('DealFloatingModal', DealFloatingModal)
    Vue.component('CreateDealModal', CreateDealModal)

    // Fields
    Vue.component('FormLostReasonField', FormLostReasonField)
    Vue.component('FormPipelineStageField', FormPipelineStageField)
    Vue.component('IndexPipelineStageField', IndexPipelineStageField)
    Vue.component('IndexLostReasonField', IndexLostReasonField)

    setPipelines(Innoclapps.config('deals.pipelines') || [])
    setLostReasons(Innoclapps.config('deals.lost_reasons') || [])

    // Routes
    routes.forEach(route => router.addRoute(route))

    router.addRoute('settings', {
      path: 'deals',
      name: 'deals-settings-index',
      component: SettingsDeals,
      meta: {
        title: i18n.t('deals::deal.deals'),
      },
      children: [
        {
          path: 'pipelines/create',
          name: 'create-pipeline',
          component: DealsPipelinesCreate,
          meta: { title: i18n.t('deals::deal.pipeline.create') },
        },
      ],
    })
    router.addRoute('settings', {
      path: 'deals/pipelines/:id/edit',
      name: 'edit-pipeline',
      component: DealsPipelinesEdit,
      meta: { title: i18n.t('deals::deal.pipeline.edit') },
    })
  })
}
