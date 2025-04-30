<template>
  <Head :title="title" />

  <div class="row g-3">
    <div class='demo-app-main'>
      <FullCalendar :options='calendarOptions'>
        <template v-slot:eventContent='arg'>
          <span class="event-title-calendar cursor-pointer">{{ arg.event.title }}</span>
          <span @click.stop="removeTime(arg)" class="removeTimeCalendar">
            <font-awesome-icon icon="circle-check" :class="arg.event?.extendedProps?.type == 'waiting' ? 'text-danger' : (arg.event?.extendedProps?.type == 'paid' ? 'text-success' : 'text-warning')" />
          </span>
        </template>
      </FullCalendar>
    </div>
  </div>
</template>

<script setup>
import Layout from '@/Layouts/Personal/AppLayout.vue'
import { ref, reactive, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import moment from 'moment'

defineOptions({ layout: Layout })

const props = defineProps({
  title: String,
  waitingJobs: Object,
  historyJobs: Object,
})

const waitingJobs = ref(props.waitingJobs)
const historyJobs = ref(props.historyJobs)

let initialGraphics = []

const calendarOptions =  reactive({
  plugins: [
    dayGridPlugin,
    timeGridPlugin,
    interactionPlugin // needed for dateClick
  ],
  headerToolbar: {
    left: 'prev,next',
    center: 'title',
    right: ''
  },
  locale: 'ru',
  height: window.screen.height - 300,
  initialView: 'dayGridMonth',
  initialEvents: initialGraphics, // alternatively, use the `events` setting to fetch from a feed
  // initialDate: form.graphics?.[0]?.started_at,
  editable: false,
  selectable: true,
  selectMirror: true,
  dayMaxEvents: true,
  weekends: true,
  eventDurationEditable: false,
  select: handleDateSelect,
  eventClick: handleEventClick,
  eventsSet: handleEvents,
  eventDrop: dragEvent,
  datesSet: handleMonthChange,
  /* you can update a remote database when these fire:
  eventAdd:
  eventChange:
  eventRemove:
  */
})

if(waitingJobs.value.length > 0) {
  setSchedule(waitingJobs.value, 'waiting')
}

if(historyJobs.value.length > 0) {
  setSchedule(historyJobs.value, 'history')
}

function setSchedule(data, type) {
  for(const i in data) {
    let startTime = ''
    let endTime = ''

    if(type == 'waiting') {
      startTime = moment(data[i].jtg_started_at).format("HH:mm")
      endTime = moment(data[i].jtg_finished_at).format("HH:mm")
    }

    if(type == 'history') {
      startTime = moment(data[i].jcl_started_at).format("HH:mm")
      endTime = moment(data[i].jcl_finished_at).format("HH:mm")
    }

    initialGraphics.push({
      id: Math.floor(Math.random() * 1000000),
      jobId: data[i].id,
      title: `${data[i]?.j_user_company_name} / ${data[i]?.establishment?.name_e} #${data[i].id} / ${startTime}  - ${endTime}`,
      start: moment(data[i].jtg_started_at).format("YYYY-MM-DD"),
      startTimeValue: startTime,
      endTimeValue: endTime,
      type: +data[i]?.ebl_is_paid ? 'paid' : type,
      color: 'rgb(0 5 209)'
    })

    calendarOptions.events = initialGraphics
  }
}

function handleDateSelect(selectInfo) {

}

function handleEventClick(clickInfo) {
  if(clickInfo?.event?._def?.extendedProps?.jobId)
    window.open(`/jobs/show_list/${clickInfo?.event?._def?.extendedProps?.jobId}`, '_blank').focus();
}

function handleEvents(events) {

}

function dragEvent(event) {

}

async function handleMonthChange(payload) {
  const startedAt = moment(payload.start).add(1, 'months').startOf('month').format('YYYY-MM-DD');
  const finishedAt = moment(payload.start).add(1, 'months').endOf('month').format('YYYY-MM-DD');

  initialGraphics = []

  router.get(route('schedule'), {
    search: true,
    startedAt: startedAt,
    finishedAt: finishedAt,
  }, {
    preserveState: true
  })
}

watch(() => props?.waitingJobs, (first, second) => {
  waitingJobs.value = first
  setSchedule(first, 'waiting')
});

watch(() => props?.historyJobs, (first, second) => {
  historyJobs.value = first
  setSchedule(first, 'history')
});

</script>
