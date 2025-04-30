<template>
  <Teleport to="body">
    <div
      style="display: block"
      class="modal fade show"
      id="addModeratorModal"
      tabindex="-1"
      aria-labelledby="addModeratorModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <form @submit.prevent="submit">
            <div class="modal-header text-center">
              <h4 class="modal-title">{{ $t("general.graphic") }}</h4>
              <button
                type="button"
                class="btn-close d-inline-block"
                @click="closeModal"
              ></button>
            </div>
            <div class="modal-body">
              <div class="row g-3">
                <div class='demo-app-main p-0'>
                  <FullCalendar :options='calendarOptions'>
                    <template v-slot:eventContent='arg'>
                      <span class="event-show-title-calendar">{{ arg.event.title }}</span>
                    </template>
                  </FullCalendar>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade show" v-click-away="onClickAway"></div>
  </Teleport>
</template>

<script setup>
import { defineEmits } from 'vue'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import moment from 'moment'

const props = defineProps({
  job: Object,
});

const emit = defineEmits(['close'])

let initialGraphics = []

const calendarOptions =  {
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
  initialDate: props.job.graphics?.[0]?.started_at,
  editable: false,
  selectable: true,
  selectMirror: true,
  dayMaxEvents: true,
  weekends: true,
  eventDurationEditable: false,
}

if(props.job.graphics) {
  for(const graphic in props.job.graphics) {
    let startTime = moment(props.job.graphics[graphic].started_at).format("HH:mm")
    let endTime = moment(props.job.graphics[graphic].finished_at).format("HH:mm")
    let lunch = props.job.graphics[graphic].lunch

    initialGraphics.push({
      id: props.job.graphics[graphic].id,
      title: `${startTime}  - ${endTime} / Пер. ${lunch} мин.`,
      start: moment(props.job.graphics[graphic].started_at).format("YYYY-MM-DD"),
      startTimeValue: startTime,
      endTimeValue: endTime,
      color: +props.job.graphics[graphic].is_late_added ? '#c7b900' : ''
    })
  }
}

const onClickAway = (event) => {
  if(event.target.classList.contains('modal')) {
    emit("close")
  }
}

const closeModal = () => {
  emit("close")
}

</script>
