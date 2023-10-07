<template>
  <ILayout full :overlay="isLoading">
    <template #actions>
      <NavbarSeparator class="hidden lg:block" />

      <div class="inline-flex items-center">
        <IButtonGroup class="mr-5">
          <IButton
            v-i-tooltip.bottom="$t('core::app.list_view')"
            size="sm"
            class="relative focus:z-10"
            :to="{ name: 'activity-index' }"
            variant="white"
            icon="Bars3"
            icon-class="w-4 h-4 text-neutral-500 dark:text-neutral-400"
          />
          <IButton
            v-i-tooltip.bottom="$t('activities::calendar.calendar')"
            size="sm"
            class="relative bg-neutral-100 focus:z-10"
            :to="{ name: 'activity-calendar' }"
            variant="white"
            icon="Calendar"
            icon-class="w-4 h-4 text-neutral-700 dark:text-neutral-100"
          />
        </IButtonGroup>

        <IButton
          size="sm"
          icon="Plus"
          variant="primary"
          :text="$t('activities::activity.create')"
          @click="eventBeingCreated = true"
        />
      </div>
    </template>

    <div class="flex flex-wrap items-center px-3 py-4 sm:px-5">
      <div class="grow">
        <div class="flex w-full flex-wrap items-center">
          <div
            class="order-1 mb-2 flex w-full items-center sm:order-first sm:mb-0 sm:w-auto"
          >
            <a
              v-show="query.activity_type_id"
              v-t="'core::app.all'"
              href="#"
              class="link mr-3 border-r-2 border-neutral-200 pr-3 dark:border-neutral-600"
              @click.prevent="query.activity_type_id = null"
            />
            <IIconPicker
              v-model="query.activity_type_id"
              class="min-w-max"
              :icons="typesForIconPicker"
              value-field="id"
            />
          </div>
          <div
            class="mb-2 dark:border-neutral-600 sm:mb-0 sm:ml-3 sm:border-l sm:border-neutral-200 sm:pl-3"
          >
            <DropdownSelectInput
              v-if="$gate.userCan('view all activities')"
              v-model="user"
              :items="usersForDropdown"
              label-key="name"
              value-key="id"
              @change="query.user_id = $event ? $event.id : null"
            />
          </div>
        </div>
      </div>
      <div class="flex w-full justify-between sm:w-auto sm:justify-end">
        <IButtonGroup class="mr-3 inline">
          <IButton
            v-i-tooltip.left="
              $t('activities::calendar.fullcalendar.locale.buttonText.prev')
            "
            class="relative !rounded-l-md px-2 focus:z-10"
            :size="false"
            icon="ChevronLeft"
            icon-class="h-4 w-4"
            variant="white"
            @click="$refs.calendarRef.getApi().prev()"
          />
          <IButton
            class="relative focus:z-10"
            variant="white"
            size="sm"
            :text="
              $t('activities::calendar.fullcalendar.locale.buttonText.today')
            "
            @click="$refs.calendarRef.getApi().today()"
          />
          <IButton
            v-i-tooltip.right="
              $t('activities::calendar.fullcalendar.locale.buttonText.next')
            "
            class="relative !rounded-r-md px-2 focus:z-10"
            :size="false"
            icon-class="h-4 w-4"
            variant="white"
            icon="ChevronRight"
            @click="$refs.calendarRef.getApi().next()"
          />
        </IButtonGroup>
        <IDropdown :text="activeViewText" adaptive-width size="sm">
          <IDropdownItem
            v-show="activeView !== 'timeGridWeek'"
            :text="
              $t('activities::calendar.fullcalendar.locale.buttonText.week')
            "
            @click="changeView('timeGridWeek')"
          />
          <IDropdownItem
            v-show="activeView !== 'dayGridMonth'"
            :text="
              $t('activities::calendar.fullcalendar.locale.buttonText.month')
            "
            @click="changeView('dayGridMonth')"
          />
          <IDropdownItem
            v-show="activeView !== 'timeGridDay'"
            :text="
              $t('activities::calendar.fullcalendar.locale.buttonText.day')
            "
            @click="changeView('timeGridDay')"
          />
        </IDropdown>
      </div>
    </div>

    <div class="fc-wrapper">
      <FullCalendar
        v-if="calendarOptions.initialView"
        ref="calendarRef"
        class="h-screen"
        :options="calendarOptions"
      />

      <ActivitiesEdit
        v-if="activityId"
        :id="activityId"
        :resource-name="resourceName"
        :on-hidden="handleViewModalHidden"
        :on-action-executed="handleViewActionExecuted"
      />

      <CreateActivityModal
        :visible="eventBeingCreated"
        :due-date="createDueDate"
        :end-date="createEndDate"
        @created="onActivityCreatedEventHandler"
        @modal-hidden="handleActivityCreateModalHidden"
      />
    </div>
  </ILayout>
</template>

<script setup>
import { computed, onMounted, ref, shallowReactive, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'
import momentTimezonePlugin from '@fullcalendar/moment-timezone'
import timeGridPlugin from '@fullcalendar/timegrid'
import FullCalendar from '@fullcalendar/vue3'
import { useStorage } from '@vueuse/core'

import { getLocale } from '@/utils'

import { useApp } from '~/Core/composables/useApp'
import { usePrivateChannel } from '~/Core/composables/useBroadcast'
import { useDates } from '~/Core/composables/useDates'
import { useLoader } from '~/Core/composables/useLoader'
import { useResourceable } from '~/Core/composables/useResourceable'

import { useActivityTypes } from '../composables/useActivityTypes'

import ActivitiesEdit from './ActivitiesEdit.vue'

const currentLocale = getLocale()
const resourceName = Innoclapps.resourceName('activities')

const { t } = useI18n()
const { setLoading, isLoading } = useLoader()

const calendarDefaultView = useStorage('default-calendar-view', 'timeGridWeek')
const { currentUser, users } = useApp()
const { dateToAppTimezone, currentTimeFormat } = useDates()
const { typesForIconPicker } = useActivityTypes()
const { updateResource } = useResourceable(resourceName)

const calendarRef = ref(null)

const createDueDate = ref(null)
const createEndDate = ref(null)
const activityId = ref(null)
const activeView = ref(null)
const user = currentUser.value
const eventBeingCreated = ref(false)

const query = ref({
  activity_type_id: null,
  user_id: currentUser.value.id,
})

const calendarOptions = shallowReactive({
  plugins: [
    dayGridPlugin,
    timeGridPlugin,
    interactionPlugin,
    momentTimezonePlugin,
  ],
  locale: currentLocale.toLowerCase().replace('_', '-'),
  locales: [
    {
      code: currentLocale.toLowerCase().replace('_', '-'),
      ...lang[currentLocale].activities.calendar.fullcalendar.locale,
    },
  ],
  headerToolbar: {
    left: false,
    center: 'title',
    end: false,
  },
  dayMaxEventRows: true, // for all non-TimeGrid views
  views: {
    day: {
      dayMaxEventRows: false,
    },
  },
  eventDisplay: 'block',
  initialView: null,
  firstDay: currentUser.value.first_day_of_week,
  timeZone: currentUser.value.timezone,
  lazyFetching: false,
  editable: true,
  droppable: true,

  scrollTime: '00:00:00', // not scroll to current time e.q. on day view

  // Remove the top left all day text as it's not suitable
  allDayContent: arg => {
    arg.text = ''

    return arg
  },

  moreLinkClick: arg => {
    calendarRef.value.getApi().gotoDate(arg.date)

    calendarRef.value.getApi().changeView('dayGridDay')
  },

  viewDidMount: arg => {
    activeView.value = arg.view.type

    // We don't remember the dayGridDay as
    // this is the more link redirect view
    if (arg.view.type !== 'dayGridDay') {
      rememberDefaultView(arg.view.type)
    }
  },

  eventContent: createEventTitleDomNodes,

  eventClick: info => {
    activityId.value = parseInt(info.event.id)
  },

  dateClick: data => {
    createDueDate.value = {
      date: data.allDay
        ? data.dateStr
        : moment.utc(data.dateStr).format('YYYY-MM-DD'),
      time: data.allDay ? null : moment.utc(data.dateStr).format('HH:mm'),
    }

    // On end date, we will format with the user timezone as the end date
    // has not time when on dateClick click and for this reason, we must get the actual date
    // to be displayed in the create modal e.q. if user click on day view 19th April 12 AM
    // the dueDate will be shown properly but not the end date as if we format the end date
    // with UTC will 18th April e.q. 18th April 22:00 (UTC)
    createEndDate.value = {
      date: data.allDay
        ? data.dateStr
        : moment(data.dateStr).format('YYYY-MM-DD'),
      time: null,
    }

    eventBeingCreated.value = true
  },

  eventResize: resizeInfo => {
    let payload = {}

    if (resizeInfo.event.allDay) {
      payload = {
        due_date: resizeInfo.event.startStr,
        end_date: endDateForStorage(resizeInfo.event.endStr),
      }
    } else {
      payload = {
        due_date: dateToAppTimezone(resizeInfo.event.startStr, 'YYYY-MM-DD'),
        due_time: dateToAppTimezone(resizeInfo.event.startStr, 'HH:mm'),
        end_date: dateToAppTimezone(resizeInfo.event.endStr, 'YYYY-MM-DD'),
        end_time: dateToAppTimezone(resizeInfo.event.endStr, 'HH:mm'),
      }
    }

    updateResource(payload, resizeInfo.event.id)
  },

  eventDrop: dropInfo => {
    const payload = {}
    const event = calendarRef.value.getApi().getEventById(dropInfo.event.id)

    if (dropInfo.event.allDay) {
      payload.due_date = dropInfo.event.startStr

      payload.due_time = null
      payload.end_time = null

      // When dropping event from time column to all day e.q. on week view
      // there is no end date as it's the same day, for this reason, we need to update the
      // end date to be the same like the start date for the update request payload
      if (!dropInfo.event.end) {
        payload.end_date = payload.due_date
      } else {
        // Multi days event, we will remove the one day to store
        // the end date properly in database as here for the calendar they are endDate + 1 day so they are
        // displayed properly see prepareEventsForCalendar method
        payload.end_date = endDateForStorage(dropInfo.event.endStr)
      }

      event.setExtendedProp('isAllDay', true)
      event.setEnd(endDateForCalendar(payload.end_date))
    } else {
      payload.due_date = dateToAppTimezone(
        dropInfo.event.startStr,
        'YYYY-MM-DD'
      )
      payload.due_time = dateToAppTimezone(dropInfo.event.startStr, 'HH:mm')

      // When dropping all day event to non all day e.q. on week view from top to the timeline
      // we need to update the end date as well
      if (dropInfo.oldEvent.allDay && !dropInfo.event.allDay) {
        let endDateStr = moment(dropInfo.event.startStr)
          .add(1, 'hours')
          .format('YYYY-MM-DD HH:mm:ss')
        payload.end_date = dateToAppTimezone(endDateStr, 'YYYY-MM-DD')
        payload.end_time = dateToAppTimezone(endDateStr, 'HH:mm')
        event.setEnd(endDateStr)
        event.setExtendedProp('hasEndTime', true)
      } else {
        // We will check if the actual endStr is set, if not will use the due dates as due time
        // because this may happen when the activity due and end
        // date are the same, in this case, fullcalendar does not provide the endStr
        payload.end_date = dropInfo.event.endStr
          ? dateToAppTimezone(dropInfo.event.endStr, 'YYYY-MM-DD')
          : payload.due_date

        // Time can be modified on week and day view, on month view we will
        // only modify the time on actual activities with time
        if (
          activeView.value !== 'dayGridMonth' ||
          dropInfo.event.extendedProps.hasEndTime
        ) {
          payload.end_time = dropInfo.event.endStr
            ? dateToAppTimezone(dropInfo.event.endStr, 'HH:mm')
            : payload.due_time
          event.setExtendedProp('hasEndTime', true)
        }
      }

      event.setExtendedProp('isAllDay', false)
    }

    updateResource(payload, dropInfo.event.id)
  },

  loading: setLoading,

  events: (info, successCallback, failureCallback) => {
    Innoclapps.request()
      .get('/calendar', {
        params: {
          resourceName,
          ...query.value,
          start_date: dateToAppTimezone(info.start.toUTCString()),
          end_date: dateToAppTimezone(info.end.toUTCString()),
        },
      })
      .then(({ data }) => successCallback(prepareEventsForCalendar(data)))
      .catch(error => {
        console.error(error)
        failureCallback('Error while retrieving events', error)
      })
  },
})

const usersForDropdown = computed(() => [
  ...users.value,
  {
    id: false,
    name: t('core::app.all'),
    class: 'border-t border-neutral-200 dark:border-neutral-700',
  },
])

const activeViewText = computed(() => {
  switch (activeView.value) {
    case 'timeGridWeek':
      return t('activities::calendar.fullcalendar.locale.buttonText.week')
    case 'dayGridMonth':
      return t('activities::calendar.fullcalendar.locale.buttonText.month')
    case 'dayGridDay':
    case 'timeGridDay':
      return t('activities::calendar.fullcalendar.locale.buttonText.day')
  }

  return ''
})

function onActivityCreatedEventHandler() {
  refreshEvents()
  eventBeingCreated.value = false
}

function changeView(viewName) {
  calendarRef.value.getApi().changeView(viewName)
  activeView.value = viewName
  rememberDefaultView(viewName)
}

/**
 * Create end date for the calendar
 *
 * @see  prepareEventsForCalendar
 *
 * @param  {Mixed} date
 * @param  {String} format
 *
 * @return {String}
 */
function endDateForCalendar(date, format = 'YYYY-MM-DD') {
  return moment(date).add('1', 'days').format(format)
}

/**
 * Create end date for storage
 *
 * @see  prepareEventsForCalendar
 *
 * @param  {Mixed} date
 * @param  {String} format
 *
 * @return {String}
 */
function endDateForStorage(date, format = 'YYYY-MM-DD') {
  return moment(date).subtract('1', 'days').format(format)
}

/**
 * @see https://fullcalendar.io/docs/event-render-hooks
 */
function createEventTitleDomNodes(arg) {
  let event = document.createElement('span')

  if (arg.event.allDay) {
    event.innerHTML = arg.event.title
  } else {
    let momentInstanceStartTime = moment(arg.event.startStr)

    let startTime = momentInstanceStartTime.format(
      moment().PHPconvertFormat(currentTimeFormat.value)
    )
    let momentInstanceEndTime

    if (arg.isMirror && arg.isDragging && arg.event.extendedProps.isAllDay) {
      // Dropping from all day to non-all day
      // In this case, there is no end date, we will automatically add 1 hour to the start date
      momentInstanceEndTime = moment(arg.event.startStr).add(1, 'hours')
    } else if (
      ((arg.isMirror && arg.isResizing) ||
        (arg.isMirror && arg.isDragging) ||
        (arg.event.endStr && arg.event.extendedProps.hasEndTime === true)) &&
      // This may happen when the activity due and end
      // date are the same, in this case, fullcalendar does not provide the endStr
      // attribute and the time will be shown only from the startStr
      arg.event.endStr != arg.event.startStr
    ) {
      momentInstanceEndTime = moment(arg.event.endStr)
    }

    if (momentInstanceEndTime) {
      let endTime = momentInstanceEndTime.format(
        moment().PHPconvertFormat(currentTimeFormat.value)
      )

      if (momentInstanceEndTime.date() != momentInstanceStartTime.date()) {
        startTime +=
          ' - ' + endTime + ' ' + momentInstanceEndTime.format('MMM D')
      } else {
        startTime += ' - ' + endTime
      }
    }

    event.innerHTML = startTime + ' ' + arg.event.title
  }

  return {
    domNodes: [event],
  }
}

function prepareEventsForCalendar(events) {
  return events.map(event => {
    // @see https://stackoverflow.com/questions/30323397/fullcalendar-event-shows-wrong-end-date-by-one-day
    // @see https://fullcalendar.io/docs/event-parsing
    // e.q. event with start 2021-04-01 and end date 2021-04-03 in the calendar is displayed
    // from 2021-04-01 to 2021-04-02, in this case on fetch, we will add 1 days so they are
    // displayed properly and on update, we will remove 1 day so they are saved properly
    event.extendedProps.isAllDay = event.allDay

    if (event.allDay) {
      event.end = endDateForCalendar(event.end)
    } else if (!/\d{4}-\d{2}-\d{2}\T?\d{2}:\d{2}:\d{2}$/.test(event.end)) {
      // no end time, is not in y-m-dTh:i:s format
      // to prevent clogging the calendar with events showing
      // over the week/day view, we will just add the start hour:minute
      // as end hour:minute + 30 minutes to be shown in one simple box
      // this can usually happen when to due and the end date are the same and there is no end time
      event.end = moment(event.end)
      const momentStart = moment(event.start)
      event.end
        .hour(momentStart.hour())
        .minute(momentStart.minute())
        .second(0)
        .add(30, 'minutes')
      event.end = event.end.format('YYYY-MM-DD\THH:mm:ss')
      event.extendedProps.hasEndTime = false
    } else {
      event.extendedProps.hasEndTime = true
    }

    // We need to set endEditable on events displayed on the month view as for some reason
    // when the calendar option {editable: true} is set the month view events are not resizable
    // note this is only applicable for all day events as non-all days events cannot be dragged
    // on month view (fullcalendar limitation)
    if (activeView.value === 'dayGridMonth') {
      event.endEditable = true
    }

    if (event.isReadOnly) {
      event.editable = false
    }

    return event
  })
}

function handleViewModalHidden() {
  activityId.value = null
  refreshEvents()
}

function handleActivityCreateModalHidden() {
  eventBeingCreated.value = false
  createDueDate.value = null
  createEndDate.value = null
}

/**
 * Handle action executed function callback
 *
 * Because Activity/Edit.vue redirects to the index view of the resource
 * after an action is executed, we are providing custom calendar callback when action is executed
 */
function handleViewActionExecuted(action) {
  if (action.destroyable) {
    Innoclapps.modal().hide('editActivityModal')
  }
}

function refreshEvents() {
  calendarRef.value.getApi().refetchEvents()
}

function rememberDefaultView(viewName) {
  calendarDefaultView.value = viewName
}

function setCalendarAsDefaultView() {
  calendarOptions.initialView = calendarDefaultView.value
}

watch(query, refreshEvents, { deep: true })

usePrivateChannel(
  `App.Models.User.${currentUser.value.id}`,
  '.Modules\\Activities\\Events\\CalendarSyncFinished',
  refreshEvents
)

onMounted(() => {
  setCalendarAsDefaultView()
})
</script>

<style>
.fc-theme-standard .fc-scrollgrid {
  border-left: 0 !important;
  border-right: 0 !important;
}
</style>
