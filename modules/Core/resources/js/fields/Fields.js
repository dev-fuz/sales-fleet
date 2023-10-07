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
import castArray from 'lodash/castArray'
import cloneDeep from 'lodash/cloneDeep'
import each from 'lodash/each'
import findIndex from 'lodash/findIndex'

import { isValueEmpty } from '@/utils'

class Fields {
  /**
   * Initialize new Fields instance.
   *
   * @param  {Array|Object} fields
   *
   * @return {Void}
   */
  constructor(fields = []) {
    this.items = cloneDeep(castArray(fields))
  }

  /**
   * Set the fields to the collection.
   *
   * @param {Array|Object} fields
   */
  set(fields) {
    this.items = cloneDeep(castArray(fields))

    return this
  }

  /**
   * Merge the given field attribute value with it's current form value.
   *
   * Only use if the field value is array.
   *
   * @param {String} attribute
   * @param {Mixed} value
   */
  mergeValue(attribute, value) {
    this.updateValue(attribute, [
      ...(this.find(attribute).currentValue || []),
      ...castArray(value),
    ])

    return this
  }

  /**
   * Update the field actual initial value.
   *
   * @param {String} attribute
   * @param {Mixed} value
   */
  updateValue(attribute, value) {
    this.update(attribute, {
      value,
    })
  }

  /**
   * Sync the fields value with the given values
   *
   * E.q. used when the record is changed to update the fields that are already mounted
   * with the new values, applicable for form and detail.
   *
   * @param  {Object} values
   */
  sync(values) {
    this.forEach(field => {
      let value = this.extractValueFromData(field, values)

      if (value !== undefined) {
        field.handleChange && field.handleChange(value)
        field.value = value
      }
    })

    return this
  }

  /**
   * Populate all the fields initial values from the given data.
   *
   * @param  {Object} data
   */
  populate(data) {
    this.forEach(
      field => (field.value = this.extractValueFromData(field, data))
    )

    return this
  }

  /**
   * Check whether there are dirty fields in the collection.
   */
  dirty() {
    let isDirty = false

    this.items.forEach(field => {
      if (field.isDirty && field.isDirty()) {
        isDirty = true
      }
    })

    return isDirty
  }

  /**
   * Fill field values for the given form.
   *
   * @param  {Form} form
   */
  fill(form) {
    this.forEach(field => !field.hidden && field.fill && field.fill(form))

    return form
  }

  /**
   * Check whether any required fields are missing values.
   */
  get requiredFieldsMissingValues() {
    let result = false

    this.forEach(field => {
      if (field.isRequired && isValueEmpty(field.value)) {
        result = true
      }
    })

    return result
  }

  /**
   * @private
   *
   * Get the field value from the given object.
   *
   * @param  {Object} field
   * @param  {Object} data
   */
  extractValueFromData(field, data) {
    if (field.belongsToRelation) {
      return Object.hasOwn(data, field.belongsToRelation)
        ? data[field.belongsToRelation]
        : undefined
    } else if (field.hasManyRelationship) {
      return Object.hasOwn(data, field.hasManyRelationship)
        ? data[field.hasManyRelationship]
        : undefined
    } else if (field.morphManyRelationship) {
      return Object.hasOwn(data, field.morphManyRelationship)
        ? data[field.morphManyRelationship]
        : undefined
    } else if (field.morphToManyRelationship) {
      return Object.hasOwn(data, field.morphToManyRelationship)
        ? data[field.morphToManyRelationship]
        : undefined
    } else {
      // Perhaps heading field, it has no attribute.
      if (field.attribute && Object.hasOwn(data, field.attribute)) {
        return data[field.attribute]
      } else {
        return undefined
      }
    }
  }

  /**
   * Find single field by given attribute.
   *
   * @param  {String} attribute
   */
  find(attribute) {
    let result

    this.every(field => {
      if (field.attribute == attribute) {
        result = field

        return false
      }

      return true
    })

    return result
  }

  /**
   * Determine if an field exists in the collection by attribute.
   *
   * @param  {String} attribute
   */
  has(attribute) {
    return Boolean(this.find(attribute))
  }

  /**
   * Update single field by attribute.
   *
   * @param  {String} attribute
   * @param  {Object} data
   */
  update(attribute, data) {
    let field = this.find(attribute)

    if (!field) {
      console.trace(
        'Cannot update field in collection as the field is not found. - ' +
          attribute
      )

      return this
    }

    each(data, (val, key) => (field[key] = val))

    return this
  }

  /**
   * Special every loop to loop through fields only.
   *
   * @param  {Function} callback
   */
  every(callback) {
    this.items.every(field => {
      return callback(field)
    })

    return this
  }

  /**
   * Special foreach loop to loop through fields only.
   *
   * @param  {Function} callback
   */
  forEach(callback) {
    this.items.forEach(field => callback(field))

    return this
  }

  /**
   * Push new field to collection.
   *
   * @param  {Object} field
   */
  push(field) {
    this.items.push(field)

    return this
  }

  /**
   * Get fields keys/attributes.
   */
  keys() {
    let result = []

    this.forEach(field => result.push(field.attribute))

    return result
  }

  /**
   * Remove field from the collection.
   *
   * @param  {String} attribute
   */
  remove(attribute) {
    const index = findIndex(this.items, ['attribute', attribute])

    if (index != -1) {
      this.items.splice(index, 1)

      return true
    }

    return false
  }

  /**
   * Get the count of total collapsable fields.
   */
  get totalCollapsable() {
    return this.all().filter(field => field.collapsed).length
  }

  /**
   * Check whether the collection has collapsable fields.
   */
  get hasCollapsable() {
    return this.totalCollapsable > 0
  }

  /**
   * Check whether the fields collection is not empty.
   */
  isNotEmpty() {
    return this.all().length > 0
  }

  /**
   * Check whether the fields collection is empty.
   */
  isEmpty() {
    return !this.isNotEmpty()
  }

  /**
   * Get all fields from the collection.
   *
   * @return {Array}
   */
  all() {
    return this.items || []
  }
}

export default Fields
