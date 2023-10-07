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
import { computed } from 'vue'
import cloneDeep from 'lodash/cloneDeep'

import { randomString } from '@/utils'

import Fields from '~/Core/fields/Fields'

import Form from './Form/Form'

class FieldsForm extends Form {
  /**
   * @param {Fields} fields
   * @param {Object} data
   * @param {Object} options
   * @param {String} resourceName
   * @param {null|String} formId
   */
  constructor(fields, data = {}, options = {}, formId = null) {
    super(data, options)

    // fields are passed as ref, but Vue JS make them reactive
    // but references to the original ref so both are reactive
    this.fields = fields
    this.formId = formId || randomString()
  }

  /**
   * Indicates whether the form is dirty
   */
  get isDirty() {
    return computed(() => this.fields instanceof Fields && this.fields.dirty())
  }

  /**
   * Reset the form data.
   */
  reset() {
    // Reset any additional fields
    super.reset()

    const keys = this.keys()

    this.fields.forEach(field => {
      field.handleChange(
        cloneDeep(this.originalData[keys[keys.indexOf(field.attribute)]])
      )
    })
  }

  /**
   * Hydrate the form data from the fields
   */
  hydrate() {
    this.fields.fill(this)

    return this
  }

  /**
   * The attributes that should be ignored as data
   */
  ignore() {
    let ignored = super.ignore()

    ignored.push('fields')
    ignored.push('formId')

    return ignored
  }
}

export default FieldsForm
