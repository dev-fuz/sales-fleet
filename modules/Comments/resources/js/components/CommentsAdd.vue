<template>
  <a
    v-show="!commengIsBeingCreated"
    href="#"
    v-bind="$attrs"
    class="link inline-flex items-center text-sm"
    @click.prevent="commengIsBeingCreated = true"
  >
    <Icon icon="Plus" class="mr-1.5 h-4 w-4" />
    {{ $t('comments::comment.add') }}
  </a>
  <CommentsCreate
    v-if="commengIsBeingCreated"
    :commentable-type="commentableType"
    :commentable-id="commentableId"
    :via-resource="viaResource"
    :via-resource-id="viaResourceId"
    @created="handleCommentCreated"
    @cancelled="commengIsBeingCreated = false"
  />
</template>

<script setup>
import { onUnmounted } from 'vue'

import { useComments } from '../composables/useComments'

import CommentsCreate from './CommentsCreate.vue'

const emit = defineEmits(['created'])

const props = defineProps({
  commentableType: { required: true, type: String },
  commentableId: { required: true, type: Number },
  viaResource: String,
  viaResourceId: [String, Number],
})

defineOptions({
  inheritAttrs: false,
})

const { commengIsBeingCreated } = useComments(
  props.commentableId,
  props.commentableType
)

function handleCommentCreated(comment) {
  emit('created', comment)
}

onUnmounted(() => {
  commengIsBeingCreated.value = false
})
</script>
