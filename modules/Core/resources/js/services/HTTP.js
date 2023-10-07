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
import axios from 'axios'

const instance = axios.create({
  transformRequest: [
    (data, headers) => {
      // axios v1.1.5 no longer set the Content-Type header to "multipart/form-data" automatically if not set to null
      // As a temporary solution (if fixed from axios), we will manually check if the data has files and update
      // the Content-Type header to null, so in the default axios transformer set the header properly.
      if (data instanceof FormData && formDataContainsFiles(data)) {
        headers['Content-Type'] = null
      }

      return data
    },
    ...axios.defaults.transformRequest,
  ],
})

instance.defaults.withCredentials = true
instance.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
instance.defaults.headers.common['Content-Type'] = 'application/json'

instance.interceptors.request.use(config => {
  // https://stackoverflow.com/questions/49435859/broadcast-to-others-and-dont-broadcast-to-current-user-are-not-working
  if (window.Echo) {
    let socketId = window.Echo.socketId()

    if (socketId) {
      config.headers['X-Socket-ID'] = window.Echo.socketId()
    }
  }

  return config
})

instance.interceptors.response.use(
  response => {
    return response
  },
  error => {
    if (axios.isCancel(error)) {
      return error
    }

    error.isValidationError = () => false

    const status = error.response.status

    if (status === 404) {
      // 404 not found
      Innoclapps.$emit('error-404')
    } else if (status === 403) {
      // Forbidden
      Innoclapps.$emit('error-403', error)
    } else if (status === 401) {
      // Session timeout / Logged out
      window.location.href = Innoclapps.config('url') + '/login'
    } else if (status === 409) {
      // Conflicts
      Innoclapps.$emit('conflict', error.response.data.message)
    } else if (status === 419) {
      // Handle expired CSRF token
      Innoclapps.$emit('token-expired', error)
    } else if (status === 422) {
      error.isValidationError = () => true
      // Emit form validation errors event
      Innoclapps.$emit('form-validation-errors', error.response.data.errors)
    } else if (status === 429) {
      // Handle throttle errors
      Innoclapps.$emit('too-many-requests', error)
    } else if (status === 503) {
      Innoclapps.$emit('maintenance-mode', error.response.data.message)
    } else if (status >= 500) {
      // 500 errors
      Innoclapps.$emit('error', error.response.data.message)
    }

    // Do something with response error
    return Promise.reject(error)
  }
)

function formDataContainsFiles(formData) {
  function containsFilesRecursive(value) {
    if (value instanceof File) {
      return true // Found a file
    } else if (value instanceof Blob) {
      return false // Found a non-file blob (e.g., a non-file input)
    } else if (Array.isArray(value)) {
      for (const item of value) {
        if (containsFilesRecursive(item)) {
          return true // Found a file within an array
        }
      }
    } else if (typeof value === 'object' && value !== null) {
      for (const subValue of Object.values(value)) {
        if (containsFilesRecursive(subValue)) {
          return true // Found a file within an object
        }
      }
    }

    return false // No files found
  }

  // eslint-disable-next-line no-unused-vars
  for (const [key, value] of formData.entries()) {
    if (containsFilesRecursive(value)) {
      return true // Found a file within the FormData
    }
  }

  return false // No files found
}

export default instance
export const CancelToken = axios.CancelToken
