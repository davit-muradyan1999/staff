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
      <div class="modal-dialog modal-xl modal-xl-size">
        <div class="modal-content">
          <form @submit.prevent="submit">
            <div class="modal-header text-center">
              <h4 class="modal-title">{{ !data?.id ? $t("general.addJob") : $t("general.addJob") }}</h4>
              <button
                type="button"
                class="btn-close d-inline-block"
                @click="closeModal"
              ></button>
            </div>
            <div class="modal-body">
              <div
                v-if="errorMsg"
                class="alert alert-danger alert-dismissible ml-0 mr-0 mb-2"
                role="alert"
              >
                {{ errorMsg }}
              </div>


              <div class="mb-3 row">
                <label for="inputName" class="col-sm-2 col-form-label">{{ $t('general.establishment') }}</label>
                <div class="col-sm-10">
                  <Multiselect
                    v-model="form.establishment_id"
                    valueProp="id"
                    :options="establishments"
                    :searchable="true"
                    label="name_e"
                    track-by="name_e"
                    :class="{ 'is-invalid': v$.establishment_id.$errors.length }"
                  />
                  <div class="invalid-feedback">
                    {{ v$?.establishment_id?.$errors?.[0]?.$message || v$?.establishment_id?.$errors?.[0]?.$params?.message }}
                  </div>
                </div>
              </div>

              <div class="mb-3 row" v-if="branches.length > 0">
                <label for="inputName" class="col-sm-2 col-form-label">{{ $t('general.branche') }}</label>
                <div class="col-sm-10">
                  <Multiselect
                    v-model="form.branche_id"
                    valueProp="id"
                    :options="branches"
                    :searchable="true"
                    label="title_b"
                    track-by="title_b"
                    :class="{ 'is-invalid': v$.branche_id.$errors.length }"
                  />
                  <div class="invalid-feedback">
                    {{ v$?.branche_id?.$errors?.[0]?.$message || v$?.branche_id?.$errors?.[0]?.$params?.message }}
                  </div>
                </div>
              </div>

              <div class="mb-3 row">
                <label for="inputName" class="col-sm-2 col-form-label">{{ $t('general.currency') }}</label>
                <div class="col-sm-10">
                  <Multiselect
                    v-model="form.currency_id"
                    valueProp="id"
                    :options="currencies"
                    :searchable="true"
                    disabled
                    label="name"
                    track-by="name"
                    :class="{ 'is-invalid': v$.currency_id.$errors.length }"
                  />
                  <div class="invalid-feedback">
                    {{ v$?.currency_id?.$errors?.[0]?.$message || v$?.currency_id?.$errors?.[0]?.$params?.message }}
                  </div>
                </div>
              </div>

              <div class="mb-3 row">
                <label for="bonus" class="col-sm-2 col-form-label">{{ $t('general.bonus') }}</label>
                <div class="col-sm-10">
                  <input
                    type="text"
                    class="form-control"
                    id="bonus"
                    v-model="form.bonus"
                    :class="{ 'is-invalid': v$?.bonus?.$errors.length }"
                  >
                  <div class="invalid-feedback">
                    {{ v$?.bonus?.$errors?.[0]?.$message || v$?.bonus?.$errors?.[0]?.$params?.message }}
                  </div>
                </div>
              </div>

              <div class="mb-3 row">
                <label for="numberOfEmployeesNeeded" class="col-sm-2 col-form-label">{{ $t('general.numberOfEmployeesNeeded') }}</label>
                <div class="col-sm-10">
                  <input
                    type="text"
                    class="form-control"
                    id="numberOfEmployeesNeeded"
                    v-model="form.employee_qnt"
                    :class="{ 'is-invalid': v$?.employee_qnt?.$errors.length }"
                  >
                  <div class="invalid-feedback">
                    {{ v$?.employee_qnt?.$errors?.[0]?.$message || v$?.employee_qnt?.$errors?.[0]?.$params?.message }}
                  </div>
                </div>
              </div>

              <div class="mb-3 row">
                <label for="is_active" class="col-sm-2 col-form-label">{{ $t('general.active') }}</label>
                <div class="col-sm-10">
                  <input
                    type="checkbox"
                    id="is_active"
                    v-model="form.is_active"
                    :class="{ 'is-invalid': v$?.is_active?.$errors.length }"
                  >
                  <div class="invalid-feedback">
                    {{ v$?.is_active?.$errors?.[0]?.$message || v$?.is_active?.$errors?.[0]?.$params?.message }}
                  </div>
                </div>
              </div>

              <div class="row g-3">
                <div class='demo-app-main'>
                  <FullCalendar :options='calendarOptions' />
                </div>
              </div>
            </div>
            <div class="modal-footer text-center">
              <div class="col-md-12 text-center">
                <button type="submit" class="btn btn-primary me-3">{{ !data?.id ? $t('general.save') : $t('general.edit') }}</button>
                <button type="button" class="btn btn-secondary" @click="closeModal">{{ $t('general.revoke') }}</button>
              </div>
              <div class="col-md-12 text-center" v-if="data?.id">
                <a href="#" class="text" @click.prevent="deleteModerator"><i>{{ $t('general.deleteModerator') }}</i></a>
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
import axios from 'axios'
import { useForm } from '@inertiajs/vue3'
import useVuelidate from '@vuelidate/core'
import { Required, Numeric } from '../../../Utils/validationMessages'
import { ref, defineEmits, reactive } from 'vue'
import { notify } from '@kyvg/vue3-notification'
import { router } from '@inertiajs/vue3'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import { INITIAL_EVENTS, createEventId } from '@/Utils/fullCalendar'

const props = defineProps({
  data: Object,
  establishments: Object,
  currencies: Object,
  branches: Object
});

const emit = defineEmits(['close'])

const errorMsg = ref('')

const form = useForm({
  id: props?.data?.id,
  establishment_id: props?.data?.establishment_id,
  branche_id: props?.data?.branche_id,
  currency_id: 1,
  bonus: props?.data?.bonus,
  employee_qnt: props?.data?.employee_qnt,
  is_active: props?.data?.is_active,
});

const rules = {
  establishment_id: { Required },
  branche_id: { Required },
  currency_id: { Required },
  bonus: { Numeric },
  employee_qnt: { Numeric },
  is_active: {  },
};

const currentEvents = ref([])

const calendarOptions =  {
  plugins: [
    dayGridPlugin,
    timeGridPlugin,
    interactionPlugin // needed for dateClick
  ],
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,timeGridWeek,timeGridDay'
  },
  locale: 'ru',
  height: window.screen.height - 300,
  initialView: 'dayGridMonth',
  initialEvents: INITIAL_EVENTS, // alternatively, use the `events` setting to fetch from a feed
  editable: true,
  selectable: true,
  selectMirror: true,
  dayMaxEvents: true,
  weekends: true,
  select: handleDateSelect,
  eventClick: handleEventClick,
  eventsSet: handleEvents
  /* you can update a remote database when these fire:
  eventAdd:
  eventChange:
  eventRemove:
  */
}

const v$ = useVuelidate(rules, form);

const submit = () => {
  v$.value.$touch();
  if (v$._value.$invalid) return false;

  const routeUrl = props?.data?.id ? route('update_company_job') : route('add_company_job')

  axios.post(routeUrl, form).then((response) => {
      notify({
        title: response.data.message,
        type: 'success'
      })
      closeModal()
      router.get(route('company_jobs'))
    }).catch(error => {
      errorMsg.value = error.response.data.message
    });
}

const onClickAway = (event) => {
  if(event.target.classList.contains('modal')) {
    emit("close")
  }
}

const deleteModerator = () => {

}

const closeModal = () => {
  emit("close")
}

function handleDateSelect(selectInfo) {
  let title = prompt('Please enter a new title for your event')
  let calendarApi = selectInfo.view.calendar

  calendarApi.unselect() // clear date selection

  if (title) {
    calendarApi.addEvent({
      id: createEventId(),
      title,
      start: selectInfo.startStr,
      end: selectInfo.endStr,
      allDay: selectInfo.allDay
    })
  }
}

function handleEventClick(clickInfo) {
  if (confirm(`Are you sure you want to delete the event '${clickInfo.event.title}'`)) {
    clickInfo.event.remove()
  }
}

function handleEvents(events) {
  currentEvents.value = events
}

</script>

<style lang='css'>
.demo-app {
  display: flex;
  min-height: 100%;
  font-family: Arial, Helvetica Neue, Helvetica, sans-serif;
  font-size: 14px;
}

.demo-app-sidebar {
  line-height: 1.5;
  background: #eaf9ff;
  border-right: 1px solid #d3e2e8;
}

.demo-app-sidebar-section {
  padding: 2em;
}

.demo-app-main {
  flex-grow: 1;
  padding: 3em;
}

.fc td {
  height: 20px;
}

</style>
