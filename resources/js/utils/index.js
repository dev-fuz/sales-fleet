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
import formatBytes from './formatBytes'
import getContrast from './getContrast'
import getLocale from './getLocale'
import isDarkMode from './isDarkMode'
import isPurchaseKey from './isPurchaseKey'
import isValueEmpty from './isValueEmpty'
import isVisible from './isVisible'
import lightenDarkenColor from './lightenDarkenColor'
import passiveEventArg from './passiveEventArg'
import randomString from './randomString'
import {
  determineReminderTypeBasedOnMinutes,
  determineReminderValueBasedOnMinutes,
} from './reminders'
import shadeColor from './shadeColor'
import strTitle from './strTitle'
import strTruncate from './strTruncate'
import throwConfetti from './throwConfetti'
import timelineLabels from './timelineLabels'

export {
  randomString,
  isValueEmpty,
  passiveEventArg,
  isVisible,
  getLocale,
  strTitle,
  strTruncate,
  getContrast,
  lightenDarkenColor,
  timelineLabels,
  formatBytes,
  determineReminderTypeBasedOnMinutes,
  determineReminderValueBasedOnMinutes,
  shadeColor,
  throwConfetti,
  isPurchaseKey,
  isDarkMode,
}
