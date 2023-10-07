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
import { toRef, watch } from 'vue'

const title = toRef(document?.title ?? null)

watch(title, newTitle => {
  if (newTitle) {
    document.title = newTitle
  }
})

export function usePageTitle(newTitle = null) {
  const t = toRef(newTitle)

  watch(
    t,
    v => {
      if (v) {
        title.value = v
      }
    },
    { immediate: true }
  )

  return title
}
