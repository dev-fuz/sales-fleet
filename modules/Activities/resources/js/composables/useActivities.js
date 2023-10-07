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
import { useI18n } from 'vue-i18n'

import { useApp } from '~/Core/composables/useApp'
import { useDates } from '~/Core/composables/useDates'
import { useResourceable } from '~/Core/composables/useResourceable'

import { useActivityTypes } from './useActivityTypes'

export function useActivities() {
  const { t } = useI18n()
  const { findTypeByFlag } = useActivityTypes()
  const { currentUser } = useApp()
  const { userTimezone } = useDates()

  async function createFollowUpActivity(
    date,
    viaResource,
    viaResourceId,
    relatedToDisplayName,
    attributes = {}
  ) {
    const utcMomentDate = moment
      .tz(date, userTimezone.value)
      .hour(Innoclapps.config('activities.defaults.hour'))
      .minute(Innoclapps.config('activities.defaults.minutes'))
      .clone()
      .utc()

    const { createResource } = useResourceable(
      Innoclapps.resourceName('activities')
    )

    let activity = await createResource(
      Object.assign(
        {
          title: t('activities::activity.follow_up_with_title', {
            with: relatedToDisplayName,
          }),
          activity_type_id: findTypeByFlag('task').id,
          due_date: utcMomentDate.format('YYYY-MM-DD'),
          due_time: utcMomentDate.format('HH:mm:ss'),
          end_date: utcMomentDate.format('YYYY-MM-DD'),
          reminder_minutes_before: Innoclapps.config(
            'defaults.reminder_minutes'
          ),
          user_id: currentUser.value.id,
          [viaResource]: [viaResourceId],
        },
        attributes
      )
    )

    return activity
  }

  return {
    createFollowUpActivity,
  }
}
