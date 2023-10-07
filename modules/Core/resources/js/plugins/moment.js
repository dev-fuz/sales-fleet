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
import moment from 'moment-timezone'

import { getLocale } from '@/utils'

// import other locales as they are added
import 'moment/dist/locale/pt-br'
import 'moment/dist/locale/es'
import 'moment/dist/locale/ru'
import 'moment/dist/locale/it'

import momentPhp from './momentPhp'

const getMomentLocale = () => getLocale().replace('_', '-').toLowerCase()

// If the locale is not imported, will fallback to en
moment.locale(
  moment.locales().indexOf(getMomentLocale()) === -1 ? 'en' : getMomentLocale()
)

momentPhp(moment)

window.moment = moment
