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
import find from 'lodash/find'
import map from 'lodash/map'

export function useCards() {
  function applyUserConfig(cards, dashboard) {
    return map(cards, (card, index) => {
      let config = find(dashboard.cards, ['key', card.uriKey])

      card.order = config
        ? Object.hasOwn(config, 'order')
          ? config.order
          : index + 1
        : index + 1

      card.enabled =
        !config || config.enabled || typeof config.enabled == 'undefined'
          ? true
          : false

      return card
    })
  }

  return { applyUserConfig }
}
