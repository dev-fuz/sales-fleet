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
import { formatMoney, formatNumber, toFixed } from 'accounting-js'

export function useAccounting() {
  return {
    toFixed,
    formatNumber: function (value, options = {}) {
      return formatNumber(
        value,
        Object.assign(
          {
            precision: Innoclapps.config('currency.precision'),
            thousand: Innoclapps.config('currency.thousands_separator'),
            decimal: Innoclapps.config('currency.decimal_mark'),
          },
          options
        )
      )
    },
    formatMoney: function (value, currency = null) {
      currency = currency || Innoclapps.config('currency')

      return formatMoney(value, {
        symbol: currency.symbol,
        precision: currency.precision,
        thousand: currency.thousands_separator,
        decimal: currency.decimal_mark,
        format: currency.symbol_first == true ? '%s%v' : '%v%s',
      })
    },
  }
}
