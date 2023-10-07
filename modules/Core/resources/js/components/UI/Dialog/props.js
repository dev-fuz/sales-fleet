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
import { randomString } from '@/utils'

export default {
  form: { type: Boolean },
  overlay: { type: Boolean, default: true },
  visible: Boolean, // v-model
  title: String,
  subTitle: String,
  busy: Boolean,
  size: {
    type: String,
    default: 'md',
    validator(value) {
      return ['sm', 'md', 'lg', 'xl', 'xxl'].includes(value)
    },
  },
  id: { type: String, default: randomString() },

  okTitle: { type: String, default: 'Ok' },
  okDisabled: Boolean,
  okLoading: Boolean,
  okVariant: { type: String, default: 'primary' },
  okSize: String,

  cancelTitle: { type: String, default: 'Cancel' },
  cancelVariant: { type: String, default: 'white' },
  cancelDisabled: Boolean,
  cancelSize: String,

  hideFooter: Boolean,
  hideHeader: Boolean,
  hideHeaderClose: Boolean,
  initialFocus: { type: Object, default: null },
  staticBackdrop: Boolean, // prevent dialog close on esc and backdrop click
}
