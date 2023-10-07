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
import findIndex from 'lodash/findIndex'

export default {
  /**
   * Set the resource records
   *
   * @param {Object} state
   * @param {Array} collection
   */
  SET(state, collection) {
    state.collection = collection

    state.dataFetched = true
  },

  /**
   * Reset the resource records
   *
   * @param {Object} state
   */
  RESET(state) {
    state.collection = []

    state.dataFetched = false
  },

  /**
   * Add record to resource collection
   *
   * @param {Object} state
   * @param {Object} item
   */
  ADD(state, item) {
    state.collection.push(item)
  },

  /**
   * Update resource record
   *
   * @param {Object} state
   * @param {Object} data
   */
  UPDATE(state, data) {
    const index = findIndex(state.collection, ['id', Number(data.id)])

    if (index !== -1) {
      state.collection[index] = data.item
    }
  },

  /**
   * Remove resource record
   *
   * @param {Object} state
   * @param {Number} id
   */
  REMOVE(state, id) {
    const index = findIndex(state.collection, ['id', Number(id)])

    if (index != -1) {
      state.collection.splice(index, 1)
    }
  },
}
