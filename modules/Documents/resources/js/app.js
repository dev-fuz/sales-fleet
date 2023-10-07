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

import DocumentsTab from './components/RecordTabDocument.vue'
import DocumentsTabPanel from './components/RecordTabDocumentPanel.vue'
import RecordTabTimelineDocument from './components/RecordTabTimelineDocument.vue'
import SettingsDocuments from './components/SettingsDocuments.vue'
import { useDocumentTypes } from './composables/useDocumentTypes'
import DocumentPublicView from './views/DocumentsPublicView.vue'
import routes from './routes'

const { setDocumentTypes } = useDocumentTypes()

if (window.Innoclapps) {
  Innoclapps.booting(function (Vue, router) {
    Vue.component('DocumentPublicView', DocumentPublicView)
    Vue.component('DocumentsTab', DocumentsTab)
    Vue.component('DocumentsTabPanel', DocumentsTabPanel)
    Vue.component('RecordTabTimelineDocument', RecordTabTimelineDocument)

    setDocumentTypes(Innoclapps.config('documents.types') || [])

    // Routes
    routes.forEach(route => router.addRoute(route))

    router.addRoute('settings', {
      path: 'documents',
      name: 'document-settings',
      component: SettingsDocuments,
      meta: {
        title: i18n.t('documents::document.documents'),
      },
    })
  })
}
