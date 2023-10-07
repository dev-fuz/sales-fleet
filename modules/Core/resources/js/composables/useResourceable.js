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
import { computed, isRef, ref, shallowRef, toValue, watch } from 'vue'

import FieldsForm from '../services/FieldsForm'
import Form from '../services/Form/Form'

import { useForm } from './useForm'

export function useResourceable(resourceName) {
  let rawResourceName = null,
    _resource = shallowRef({})

  if (isRef(resourceName)) {
    watch(
      resourceName,
      newVal => {
        rawResourceName = newVal

        if (newVal) {
          _resource.value = Innoclapps.resource(newVal)
        }
      },
      { immediate: true, flush: 'post' }
    )
  } else {
    rawResourceName = toValue(resourceName)
    _resource.value = Innoclapps.resource(rawResourceName) || {}
  }

  const resourceBeingCreated = ref(false)
  const resourceBeingDeleted = ref(false)
  const resourceBeingUpdated = ref(false)
  const resourceBeingRetrieved = ref(false)
  const resourceBeingCloned = ref(false)
  const associationsBeingSaved = ref(false)
  const resourceSingularName = computed(() => _resource.value.singularName)

  function getRawResourceName() {
    return rawResourceName
  }

  async function retrieveResource(resourceId) {
    resourceBeingRetrieved.value = true

    try {
      let { data } = await Innoclapps.request().get(
        `/${getRawResourceName()}/${resourceId}`
      )

      return data
    } finally {
      resourceBeingRetrieved.value = false
    }
  }

  async function updateResource(attributes, id, config = {}) {
    let form

    if (!(attributes instanceof Form)) {
      form = useForm(attributes).form
    } else {
      form = attributes
    }

    if (attributes instanceof FieldsForm) {
      attributes.hydrate()
    }

    resourceBeingUpdated.value = true

    try {
      const updatedResource = await form.put(
        `/${rawResourceName}/${id}`,
        config
      )

      Innoclapps.$emit('resource-updated', {
        resourceName: rawResourceName,
        resourceId: id,
        resource: updatedResource,
      })

      Innoclapps.$emit(`${rawResourceName}-updated`, updatedResource)

      return updatedResource
    } finally {
      resourceBeingUpdated.value = false
    }
  }

  async function createResource(attributes) {
    let form

    if (!(attributes instanceof Form)) {
      form = useForm(attributes).form
    } else {
      form = attributes
    }

    if (attributes instanceof FieldsForm) {
      attributes.hydrate()
    }

    resourceBeingCreated.value = true

    try {
      const data = await form.post(`/${rawResourceName}`)

      Innoclapps.$emit('resource-updated', {
        resourceName: rawResourceName,
        resourceId: data.id,
        resource: data,
      })

      Innoclapps.$emit(`${rawResourceName}-updated`, data)

      return data
    } finally {
      resourceBeingCreated.value = false
    }
  }

  async function deleteResource(id) {
    resourceBeingDeleted.value = true

    try {
      const data = await Innoclapps.request().delete(
        `/${rawResourceName}/${id}`
      )

      Innoclapps.$emit('resource-deleted', {
        resourceName: rawResourceName,
        resourceId: id,
      })

      Innoclapps.$emit(`${rawResourceName}-deleted`, data)

      return data
    } finally {
      resourceBeingDeleted.value = false
    }
  }

  async function cloneResource(id) {
    resourceBeingCloned.value = true

    try {
      const { data } = await Innoclapps.request().post(
        `/${rawResourceName}/${id}/clone`
      )

      Innoclapps.$emit('resource-cloned', {
        resourceName: rawResourceName,
        resourceId: id,
        clonedResource: data,
      })

      Innoclapps.$emit(`${rawResourceName}-cloned`, data)

      return data
    } finally {
      resourceBeingCloned.value = false
    }
  }

  async function syncAssociations(resourceId, attrs) {
    associationsBeingSaved.value = true

    try {
      let { data } = await Innoclapps.request().post(
        `associations/${rawResourceName}/${resourceId}`,
        attrs
      )

      return data
    } finally {
      associationsBeingSaved.value = false
    }
  }

  async function detachAssociations(resourceId, attrs) {
    associationsBeingSaved.value = true

    try {
      const { data } = await Innoclapps.request().delete(
        `associations/${rawResourceName}/${resourceId}`,
        {
          data: attrs,
        }
      )

      return data
    } finally {
      associationsBeingSaved.value = false
    }
  }

  return {
    retrieveResource,
    resourceBeingRetrieved,

    createResource,
    resourceBeingCreated,

    cloneResource,
    resourceBeingCloned,

    updateResource,
    resourceBeingUpdated,

    deleteResource,
    resourceBeingDeleted,

    detachAssociations,
    syncAssociations,
    associationsBeingSaved,

    resourceName,
    rawResourceName,

    getRawResourceName,
    resourceSingularName,
  }
}
