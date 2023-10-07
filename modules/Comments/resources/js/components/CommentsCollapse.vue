<template>
  <div>
    <p
      v-show="hasComments"
      class="inline-flex items-center text-sm font-medium text-neutral-800 dark:text-white"
      v-bind="$attrs"
    >
      <span class="mr-3 h-5 w-5">
        <Icon
          v-show="!requestInProgress"
          icon="ChatAlt"
          class="h-5 w-5 text-current"
        />
        <ISpinner v-if="requestInProgress" class="mt-px h-4 w-4 text-current" />
      </span>

      <a
        href="#"
        class="inline-flex items-center focus:outline-none"
        @click="toggleCommentsVisibility"
      >
        <span
          v-t="{
            path: 'comments::comment.total',
            args: { total: countComputed },
          }"
        />
        <Icon
          :icon="commentsAreVisible ? 'ChevronDown' : 'ChevronRight'"
          class="ml-3 h-4 w-4"
        />
      </a>
    </p>

    <div
      v-show="commentsAreVisible && commentsAreLoaded"
      :class="['mt-3', listWrapperClass]"
    >
      <CommentsList
        v-if="commentsAreLoaded"
        :comments="comments"
        :commentable-type="commentableType"
        :commentable-id="commentableId"
        :via-resource="viaResource"
        :via-resource-id="viaResourceId"
        :auto-focus-if-required="true"
        @updated="handleCommentUpdatedEvent"
        @deleted="handleCommentDeletedEvent"
      />
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, watch } from 'vue'
import { whenever } from '@vueuse/core'

import { useComments } from '../composables//useComments'

import CommentsList from './CommentsList.vue'

const emit = defineEmits([
  'update:count',
  'update:comments',
  'deleted',
  'updated',
])

const props = defineProps({
  count: Number,
  listWrapperClass: [Array, Object, String],
  commentableType: { required: true, type: String },
  commentableId: { required: true, type: Number },
  comments: { required: true, type: Array },
  viaResource: String,
  viaResourceId: [String, Number], // must be provided when "viaResource" is provided
})

defineOptions({
  inheritAttrs: false,
})

const {
  requestInProgress,
  commentsAreVisible,
  commentsAreLoaded,
  toggleCommentsVisibility,
  getAllComments,
} = useComments(props.commentableId, props.commentableType)

const countComputed = computed(() => {
  if (!commentsAreLoaded.value) {
    return props.count || 0
  }

  return props.comments.length
})

const hasComments = computed(() => countComputed.value > 0)

/**
 * Emit the update count event when new comments are added/deleted
 * For in case any parent component interested to update it's data
 */
watch(countComputed, newVal => {
  emit('update:count', newVal)
})

whenever(commentsAreVisible, () => {
  // When the comments visibility is toggled on after a comment is added
  // We don't need to make a request to load them all as we already know
  // that there were zero comments and new one was created
  if (
    props.count === 0 &&
    props.comments.length === 1 &&
    props.comments[0].was_recently_created === true
  ) {
    commentsAreLoaded.value = true

    return
  }

  if (!commentsAreLoaded.value) {
    // TODO, fires twice because of timeline and direct embed
    loadComments()
  }
})

async function loadComments() {
  let comments = await getAllComments(
    props.viaResource,
    props.viaResource ? props.viaResourceId : null
  )

  commentsAreLoaded.value = true

  emit('update:comments', comments)
}

async function handleCommentDeletedEvent(id) {
  await nextTick()

  if (!hasComments.value) {
    commentsAreVisible.value = false
  }

  emit('deleted', id)
}

function handleCommentUpdatedEvent(comment) {
  emit('updated', comment)
}

onBeforeUnmount(() => {
  commentsAreVisible.value = false
  commentsAreLoaded.value = false
})
</script>
