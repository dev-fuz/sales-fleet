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
import { computed, isRef, nextTick, ref, toValue, watch } from 'vue'
import { useRouter } from 'vue-router'
import castArray from 'lodash/castArray'
import difference from 'lodash/difference'
import pick from 'lodash/pick'

import { useResourceable } from './useResourceable'

function isPrimitiveArray(arr) {
  return Array.isArray(arr) && arr.every(item => typeof item !== 'object')
}

function syncObjects(oldObj, newObj) {
  let syncedObj = JSON.parse(JSON.stringify(oldObj)) // Deep clone

  for (let key in newObj) {
    if (Object.hasOwn(newObj, key)) {
      if (newObj[key] && Object.hasOwn(newObj[key], '_reset')) {
        // Handle reset
        syncedObj[key] = newObj[key]._reset
      } else if (Array.isArray(syncedObj[key])) {
        // Handle arrays in oldObj
        if (isPrimitiveArray(newObj[key])) {
          // If newObj's array is a primitive array, replace oldObj's array
          syncedObj[key] = newObj[key]
        } else {
          const itemsToSync = Array.isArray(newObj[key])
            ? newObj[key]
            : [newObj[key]] // Wrap in array if necessary
          syncedObj[key] = syncArrays(syncedObj[key], itemsToSync)
        }
      } else if (newObj[key] && newObj[key]._delete) {
        // Handle deletion
        delete syncedObj[key]
      } else if (
        typeof newObj[key] === 'object' &&
        newObj[key] !== null &&
        syncedObj[key]
      ) {
        // Handle nested objects
        syncedObj[key] = syncObjects(syncedObj[key], newObj[key])
      } else {
        // Handle primitive values and non-existing keys
        syncedObj[key] = newObj[key]
      }
    }
  }

  return syncedObj
}

function syncArrays(oldArray, newArray) {
  let syncedArray = [...oldArray] // Shallow clone
  let itemsToDelete = [] // Store IDs of items to be deleted

  newArray.forEach(newItem => {
    if (newItem && newItem._delete) {
      itemsToDelete.push(newItem.id)

      return // Skip the rest of the loop for this iteration
    }

    const existingItemIndex = oldArray.findIndex(
      oldItem => oldItem.id === newItem.id
    )

    if (existingItemIndex !== -1) {
      syncedArray[existingItemIndex] = syncObjects(
        oldArray[existingItemIndex],
        newItem
      )
    } else {
      syncedArray.push(newItem)
    }
  })

  // Perform deletions
  for (let id of itemsToDelete) {
    const indexToDelete = syncedArray.findIndex(item => item.id === id)

    if (indexToDelete !== -1) {
      syncedArray.splice(indexToDelete, 1)
    }
  }

  return syncedArray
}

export function useResource(resourceName, resourceId, config = {}) {
  const {
    resourceSingularName,
    associationsBeingSaved,
    resourceBeingRetrieved: resourceBeingFetched,
    retrieveResource,
    syncAssociations,
    detachAssociations,
    resourceBeingUpdated: updateRequestInProgress,
    updateResource: performUpdate,
  } = useResourceable(resourceName)

  const router = useRouter()

  let originalKeys = []
  let rawResourceId = null

  const resource = ref({})

  const resourceBeingUpdated = computed(
    () => updateRequestInProgress.value || associationsBeingSaved.value
  )

  const resourceReady = computed(() => Object.keys(resource.value).length > 0)

  if (isRef(resourceId)) {
    watch(
      resourceId,
      (newVal, oldVal) => {
        rawResourceId = toValue(newVal)

        if (toValue(oldVal) && toValue(newVal)) {
          resource.value = {}

          if (config.watchId !== false) {
            nextTick(fetchResource)
          }
        }
      },
      { immediate: true, flush: 'post' }
    )
  } else {
    rawResourceId = toValue(resourceId)
  }

  async function fetchResource() {
    let resourceObject = {}

    if (router[resourceSingularName.value]) {
      resourceObject = router[resourceSingularName.value]
      delete router[resourceSingularName.value]
    } else {
      resourceObject = await retrieveResource(rawResourceId)
    }

    resourceObject._sync_timestamp = resourceObject.updated_at.split('.')[0]

    synchronizeResource(resourceObject, true)

    return resource
  }

  async function updateResource(attributes) {
    const updatedResource = await performUpdate(attributes, rawResourceId)

    updatedResource._sync_timestamp = updatedResource.updated_at.split('.')[0]

    synchronizeResource(updatedResource, true)

    return resource
  }

  function syncOnlyOriginalKeys(resourceObject) {
    const resourceKeysNow = Object.keys(resource.value)

    if (resourceKeysNow.length) {
      let addedAfter = difference(resourceKeysNow, originalKeys)
      let doNotModify = pick(resource.value, addedAfter)

      for (const key in doNotModify) {
        resourceObject[key] = doNotModify[key]
      }

      return resourceObject
    } else {
      originalKeys = Object.keys(resourceObject)

      return resourceObject
    }
  }

  // not used yet
  async function syncResourceAssociations(attrs) {
    let updatedResource = await syncAssociations(rawResourceId, attrs)

    updatedResource._sync_timestamp = new Date()

    synchronizeResource(updatedResource, true)

    return resource
  }

  async function detachResourceAssociations(attrs) {
    const updatedResource = await detachAssociations(rawResourceId, attrs)

    updatedResource._sync_timestamp = new Date()

    synchronizeResource(updatedResource, true)

    return resource
  }

  function decrementResourceCount(key) {
    castArray(key).forEach(key => {
      if (Number(resource.value[key]) > 0) {
        resource.value[key] = resource.value[key] - 1
      }
    })
  }

  function incrementResourceCount(key) {
    castArray(key).forEach(key => {
      resource.value[key] = resource.value[key] + 1
    })
  }

  function synchronizeResource(newObj, isFreshObject = false) {
    if (Object.hasOwn(newObj, '_sync_timestamp')) {
      if (newObj._sync_timestamp instanceof Date) {
        newObj._sync_timestamp = newObj._sync_timestamp.toISOString()
      }

      if (newObj._sync_timestamp.indexOf('.') > -1) {
        newObj._sync_timestamp = newObj._sync_timestamp.split('.')[0]
      }
    }

    // If "isFreshObject" is set to true, we will assume that the resource is freshly retrieved from the database
    // In this case, will replace all old keys (except added after retrieval) as some array key items (like tags) may be deleted
    if (isFreshObject) {
      resource.value = syncOnlyOriginalKeys(newObj)
    } else {
      resource.value = syncObjects(toValue(resource), toValue(newObj))
    }
  }

  return {
    resource,
    resourceBeingFetched,
    resourceBeingUpdated,
    resourceReady,
    associationsBeingSaved,

    resourceSingularName,

    fetchResource,
    updateResource,
    performUpdate,

    synchronizeResource,
    incrementResourceCount,
    decrementResourceCount,

    syncResourceAssociations,
    detachResourceAssociations,
  }
}
