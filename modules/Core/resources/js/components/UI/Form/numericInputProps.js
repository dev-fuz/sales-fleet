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
export default {
  /**
   * Currency symbol.
   */
  currency: { type: String, default: '' },

  /**
   * Maximum value allowed.
   */
  max: { type: Number, default: Number.MAX_SAFE_INTEGER || 9007199254740991 },

  /**
   * Minimum value allowed.
   */
  min: { type: Number, default: Number.MIN_SAFE_INTEGER || -9007199254740991 },

  /**
   * Enable/Disable minus value.
   */
  minus: Boolean,

  /**
   * Input placeholder.
   */
  placeholder: { type: String, default: '' },

  /**
   * Value when the input is empty
   */
  emptyValue: { type: [Number, String], default: '' },

  /**
   * Number of decimals.
   * Decimals symbol are the opposite of separator symbol.
   */
  precision: {
    type: Number,
    default: () => Number(Innoclapps.config('currency.precision')),
  },

  /**
   * Thousand separator type.
   * Separator props accept either . or , (default).
   */
  separator: { type: String, default: ',' },

  /**
   * Forced thousand separator.
   * Accepts any string.
   */
  thousandSeparator: {
    default: () => Innoclapps.config('currency.thousands_separator'),
    type: String,
  },

  /**
   * Forced decimal separator.
   * Accepts any string.
   */
  decimalSeparator: {
    default: () => Innoclapps.config('currency.decimal_mark'),
    type: String,
  },
  /**
   * The output type used for v-model.
   * It can either be String or Number (default).
   */
  outputType: { type: String, default: 'Number' },

  /**
   * v-model value.
   */
  modelValue: { type: [Number, String], default: '', required: true },

  disabled: Boolean,

  /**
   * Position of currency symbol
   * Symbol position props accept either 'suffix' or 'prefix' (default).
   */
  currencySymbolPosition: { type: String, default: 'prefix' },
}
