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
  // eslint-disable-next-line no-unused-vars
  beforeMount: function (el, binding, vnode) {
    el._showModal = () => {
      Innoclapps.$emit('modal-show', binding.value)
    }
    el.addEventListener('click', el._showModal)
  },
  // eslint-disable-next-line no-unused-vars
  unmounted: function (el, binding, vnode) {
    el.removeEventListener('click', el._showModal)
  },
}
