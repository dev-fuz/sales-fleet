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
  resourceName: String,
  resourceId: [String, Number],
  field: { type: Object, required: true },
  formId: { type: String, required: true },
  isFloating: Boolean,
}
