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
import { defineAsyncComponent } from 'vue'
import Notifications from 'notiwind'

import AuthLogin from '@/views/Auth/AuthLogin.vue'
import AuthPasswordEmail from '@/views/Auth/AuthPasswordEmail.vue'
import AuthPasswordReset from '@/views/Auth/AuthPasswordReset.vue'

import ActionDialog from '~/Core/components/Actions/ActionDialog.vue'
import ActionBulkEdit from '~/Core/components/Actions/ActionDialogBulkEdit.vue'
import Card from '~/Core/components/Cards/Card.vue'
import CardTable from '~/Core/components/Cards/CardTable.vue'
import CardTableAsync from '~/Core/components/Cards/CardTableAsync.vue'
import PresentationChart from '~/Core/components/Charts/PresentationChart.vue'
import ProgressionChart from '~/Core/components/Charts/ProgressionChart.vue'
import DatePicker from '~/Core/components/DatePicker/DatePicker.vue'
import DateRangePicker from '~/Core/components/DatePicker/DateRangePicker.vue'
import DropdownSelectInput from '~/Core/components/DropdownSelectInput.vue'
import Editor from '~/Core/components/Editor'
import ILayout from '~/Core/components/Layout.vue'
import NavbarSeparator from '~/Core/components/NavbarSeparator.vue'
import SearchInput from '~/Core/components/SearchInput.vue'
import TheFloatingResourceModal from '~/Core/components/TheFloatingResourceModal.vue'
import TheFloatNotifications from '~/Core/components/TheFloatNotifications.vue'
import TheNavbar from '~/Core/components/TheNavbar.vue'
import TheSidebar from '~/Core/components/TheSidebar.vue'
import IButtonPlugin from '~/Core/components/UI/Buttons'
import ICardPlugin from '~/Core/components/UI/Card'
import ICustomSelect from '~/Core/components/UI/CustomSelect/index'
import IDialogPlugin from '~/Core/components/UI/Dialog'
import IDropdownPlugin from '~/Core/components/UI/Dropdown'
import IFormPlugin from '~/Core/components/UI/Form'
import IActionMessage from '~/Core/components/UI/IActionMessage.vue'
import IAlert from '~/Core/components/UI/IAlert.vue'
import IAvatar from '~/Core/components/UI/IAvatar.vue'
import IBadge from '~/Core/components/UI/IBadge.vue'
import IColorSwatch from '~/Core/components/UI/IColorSwatch.vue'
import Icon from '~/Core/components/UI/Icon.vue'
import IEmptyState from '~/Core/components/UI/IEmptyState.vue'
import IIconPicker from '~/Core/components/UI/IIconPicker.vue'
import IOverlay from '~/Core/components/UI/IOverlay.vue'
import IPopover from '~/Core/components/UI/IPopover.vue'
import ISpinner from '~/Core/components/UI/ISpinner.vue'
import ITable from '~/Core/components/UI/ITable.vue'
import IStepCircle from '~/Core/components/UI/Steps/IStepCircle.vue'
import IStepsCircle from '~/Core/components/UI/Steps/IStepsCircle.vue'
import ITabsPlugin from '~/Core/components/UI/Tabs'
import ITooltipPlugin from '~/Core/components/UI/Tooltip'
import IVerticalNavigation from '~/Core/components/UI/VerticalNavigation/IVerticalNavigation.vue'
import IVerticalNavigationItem from '~/Core/components/UI/VerticalNavigation/IVerticalNavigationItem.vue'
import DetailFields from '~/Core/fields/DetailFields.vue'
import DetailFieldsItem from '~/Core/fields/DetailFieldsItem.vue'
import FieldInlineEdit from '~/Core/fields/FieldInlineEdit.vue'
import FieldsPlaceholder from '~/Core/fields/FieldsPlaceholder.vue'
import FormFields from '~/Core/fields/FormFields.vue'
import IndexFieldsItem from '~/Core/fields/IndexFieldsItem.vue'
import ActionPanel from '~/Core/views/ActionPanel.vue'

const TextCollapse = defineAsyncComponent(() =>
  import('~/Core/components/TextCollapse.vue')
)

export default function (app) {
  app.component('AuthLogin', AuthLogin)
  app.component('AuthPasswordEmail', AuthPasswordEmail)
  app.component('AuthPasswordReset', AuthPasswordReset)

  app
    .use(Notifications)
    .use(IButtonPlugin)
    .use(ICardPlugin)
    .use(IDropdownPlugin)
    .use(IFormPlugin)
    .use(IDialogPlugin, { globalEmitter: Innoclapps })
    .use(ITabsPlugin)
    .use(ITooltipPlugin)

  app
    .component('ILayout', ILayout)
    .component('IActionMessage', IActionMessage)
    .component('IAvatar', IAvatar)
    .component('ITable', ITable)
    .component('ICustomSelect', ICustomSelect)
    .component('IOverlay', IOverlay)
    .component('IPopover', IPopover)
    .component('IEmptyState', IEmptyState)
    .component('IIconPicker', IIconPicker)
    .component('ISpinner', ISpinner)
    .component('IStepsCircle', IStepsCircle)
    .component('IStepCircle', IStepCircle)
    .component('IColorSwatch', IColorSwatch)
    .component('IVerticalNavigation', IVerticalNavigation)
    .component('IVerticalNavigationItem', IVerticalNavigationItem)
    .component('IAlert', IAlert)
    .component('IBadge', IBadge)

  app.component('TheNavbar', TheNavbar)
  app.component('NavbarSeparator', NavbarSeparator)
  app.component('TheSidebar', TheSidebar)

  app.component('TheFloatNotifications', TheFloatNotifications)
  app.component('TheFloatingResourceModal', TheFloatingResourceModal)

  app.component('DatePicker', DatePicker)
  app.component('DateRangePicker', DateRangePicker)

  app.component('ActionPanel', ActionPanel)

  app.component('Icon', Icon)

  app.component('ActionDialog', ActionDialog)
  app.component('ActionBulkEdit', ActionBulkEdit)

  app.component('ProgressionChart', ProgressionChart)
  app.component('PresentationChart', PresentationChart)
  app.component('CardTable', CardTable)
  app.component('CardTableAsync', CardTableAsync)

  app.component('Card', Card)

  app.component('TextCollapse', TextCollapse)

  app.component('Editor', Editor)

  app.component('FormFields', FormFields)
  app.component('DetailFields', DetailFields)
  app.component('DetailFieldsItem', DetailFieldsItem)
  app.component('IndexFieldsItem', IndexFieldsItem)
  app.component('FieldsPlaceholder', FieldsPlaceholder)
  app.component('FieldInlineEdit', FieldInlineEdit)

  app.component('DropdownSelectInput', DropdownSelectInput)
  app.component('SearchInput', SearchInput)
}
