<template>
<Head :title="title" />

<AddCalendarDate
  v-if="openAddCalendarPopup"
  :data="modalData"
  :row="selectInfoData"
  :rememberData="rememberData"
  @close="setTime"
  @remember="rememberGraphic"
/>

<AddSpecialVacancy
  v-if="openSpecialVacancyPopup"
  :jobTypeId="form.job_type_id"
  :jobTypes="jobTypes"
  :branches="branches"
  @close="setSpecialVacancy"
/>

<div class="card mt-3 mb-4 card-table-list">
  <div class="card-header">
    {{ title }}
    <Link class="edit-action-link" :href="route('company_jobs') + searchURL">
      <button type="button" class="btn float-end btn-primary">
        <font-awesome-icon icon="arrow-left" />
      </button>
    </Link>
  </div>
  <div class="card-body card-table-list">
    <form @submit.prevent="submitForm" @keyup.13="submitForm">
      <template v-if="$page.props.auth?.user?.role == 'ADMIN' || $page.props.auth?.user?.role == 'ADMINISTRATOR'">
        <div class="mb-3 row">
          <label for="inputName" class="col-sm-2 col-form-label">{{ $t('general.company') }}</label>
          <div class="col-sm-10">
            <Multiselect
              v-model="form.company_id"
              valueProp="id"
              :options="companies"
              :searchable="true"
              label="company_name"
              track-by="company_name"
              :class="{ 'is-invalid': v$.company_id.$errors.length }"
              @change="changeCompany"
            />
            <div class="invalid-feedback">
              {{ v$?.company_id?.$errors?.[0]?.$message || v$?.company_id?.$errors?.[0]?.$params?.message }}
            </div>
          </div>
        </div>
      </template>

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
            @change="changeEstablishment"
            ref="establishmentSelect"
          />
          <div class="invalid-feedback">
            {{ v$?.establishment_id?.$errors?.[0]?.$message || v$?.establishment_id?.$errors?.[0]?.$params?.message }}
          </div>
        </div>
      </div>

      <template v-if="Object.keys(establishment).length">
        <div class="mb-3 row">
          <label for="inputName" class="col-sm-2 col-form-label">{{ $t('general.establishment') }} ({{ $t('general.title') }})</label>
          <div class="col-sm-10">
            <input
              type="text"
              class="form-control"
              id="bonus"
              :value="establishment.name_e"
              :disabled="true"
            >
          </div>
        </div>

        <div class="mb-3 row">
          <label for="inputName" class="col-sm-2 col-form-label">{{ $t('general.establishment') }} ({{ $t('general.responsibility') }})</label>
          <div class="col-sm-10">
            <input
              type="text"
              class="form-control"
              id="bonus"
              :value="establishment.obligation_e"
              :disabled="true"
            >
          </div>
        </div>

        <div class="mb-3 row">
          <label for="inputName" class="col-sm-2 col-form-label">{{ $t('general.establishment') }} ({{ $t('general.requirement') }})</label>
          <div class="col-sm-10">
            <input
              type="text"
              class="form-control"
              id="bonus"
              :value="establishment.requirement_e"
              :disabled="true"
            >
          </div>
        </div>

        <div class="mb-3 row align-items-center" v-show="establishment?.driver_license_lists.length > 0">
          <label class="col-sm-2 col-form-label">{{ $t('general.establishment') }} ({{ $t('general.driverLicense') }})</label>
          <div class="col-sm-10">
            <div class="form-check form-check-inline" v-for="license in establishment?.driver_license_lists" :key="license.id">
              <input
                class="form-check-input"
                type="checkbox"
                checked
                disabled
              >
              <label class="form-check-label">{{ license?.driver_license?.name_d }}</label>
            </div>
          </div>
        </div>

        <div class="mb-3 row align-items-center" v-show="establishment?.mean_of_transport_lists.length > 0">
          <label class="col-sm-2 col-form-label">{{ $t('general.establishment') }} ({{ $t('general.meanOfTransports') }})</label>
          <div class="col-sm-10">
            <div class="form-check form-check-inline" v-for="transport in establishment?.mean_of_transport_lists" :key="transport.id">
              <input
                class="form-check-input"
                type="checkbox"
                checked
                disabled
              >
              <label class="form-check-label">{{ transport?.mean_of_transport?.name_e }}</label>
            </div>
          </div>
        </div>

        <div class="mb-3 row align-items-center">
          <label for="absenceOfDisability" class="col-sm-2 col-form-label">{{ $t('general.establishment') }} ({{ $t('general.absenceOfDisability') }})</label>
          <div class="col-sm-10">
            <div class="form-check form-check-inline">
              <input
                class="form-check-input"
                type="checkbox"
                id="absenceOfDisability"
                :checked="establishment?.absence_disability ? true : false"
                disabled
              >
            </div>
          </div>
        </div>

        <div class="mb-3 row align-items-center">
          <label for="absenceOfDisability" class="col-sm-2 col-form-label">{{ $t('general.establishment') }} ({{ $t('general.gender') }})</label>
          <div class="col-sm-10">
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadio1" value="BOTH" :checked="establishment.gender == 'BOTH' ? true : false" disabled>
              <label class="form-check-label" for="inlineRadio1">{{ $t('general.both') }}</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadio2" value="MALE" :checked="establishment.gender == 'MALE' ? true : false" disabled>
              <label class="form-check-label" for="inlineRadio2">{{ $t('general.male') }}</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadio3" value="FEMALE" :checked="establishment.gender == 'FEMALE' ? true : false" disabled>
              <label class="form-check-label" for="inlineRadio3">{{ $t('general.female') }}</label>
            </div>
          </div>
        </div>

        <div class="mb-3 row">
          <label for="salary" class="col-sm-2 col-form-label">Позиция (Стоимость {{ $t('general.rubTime') }})</label>
          <div class="col-sm-10">
            <input
              type="text"
              class="form-control"
              id="salary"
              disabled
              :value="((+establishment?.salary || 0) + (+establishment?.tax || 0) + (+establishment?.commission || 0))"
            >
          </div>
        </div>

        <template v-if="false">
          <div class="mb-3 row">
            <label for="salary" class="col-sm-2 col-form-label">{{ $t('general.establishment') }} ({{ $t('general.salary') }})</label>
            <div class="col-sm-10">
              <input
                type="text"
                class="form-control"
                id="salary"
                disabled
                :value="establishment?.salary"
              >
            </div>
          </div>

          <div class="mb-3 row">
            <label for="tax" class="col-sm-2 col-form-label">{{ $t('general.establishment') }} ({{ $t('general.spending') }})</label>
            <div class="col-sm-10">
              <input
                type="text"
                class="form-control"
                id="tax"
                disabled
                :value="establishment?.tax"
              >
            </div>
          </div>

          <div class="mb-3 row">
            <label for="commission" class="col-sm-2 col-form-label">{{ $t('general.establishment') }} ({{ $t('general.income') }})</label>
            <div class="col-sm-10">
              <input
                type="text"
                class="form-control"
                id="commission"
                disabled
                :value="establishment?.commission"
              >
            </div>
          </div>
        </template>
      </template>

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

      <div class="mb-3 row align-items-center">
        <label class="col-sm-2 col-form-label">{{ $t('general.advantageEmployees') }}</label>
        <div class="col-sm-10">
          <Multiselect
            v-model="form.advantageEmployees"
            mode="tags"
            valueProp="id"
            :options="advantageEmployees"
            :searchable="true"
            label="name_a"
            track-by="name_a"
          >
          <template v-slot:tag="{ option, handleTagRemove, disabled }">
            <div
              class="multiselect-tag is-user"
              :class="{
                'is-disabled': disabled
              }"
            >
              <img style="margin-right: 7px;" width="18" height="18" class="mr-2" :src="`/storage/${option.icon}`">
              {{ option.name_a }}
              <span
                v-if="!disabled"
                class="multiselect-tag-remove"
                @mousedown.prevent="handleTagRemove(option, $event)"
              >
                <span class="multiselect-tag-remove-icon"></span>
              </span>
            </div>
          </template>
          </Multiselect>
        </div>
      </div>

      <div class="mb-3 row align-items-center">
        <label for="is_need_internship" class="col-sm-2 col-form-label">{{ $t('general.internshipRequired') }}</label>
        <div class="col-sm-10">
          <input
            type="checkbox"
            class="form-check-input"
            id="is_need_internship"
            v-model="form.is_need_internship"
            :true-value="1"
            :false-value="0"
            :class="{ 'is-invalid': v$?.is_need_internship?.$errors.length }"
          >
          <div class="invalid-feedback">
            {{ v$?.is_need_internship?.$errors?.[0]?.$message || v$?.is_need_internship?.$errors?.[0]?.$params?.message }}
          </div>
        </div>
      </div>

      <div class="mb-3 row align-items-center">
        <label for="is_paid_lunch" class="col-sm-2 col-form-label">{{ $t('general.breakTimeIsPaid') }}</label>
        <div class="col-sm-10">
          <input
            type="checkbox"
            class="form-check-input"
            id="is_paid_lunch"
            v-model="form.is_paid_lunch"
            :true-value="1"
            :false-value="0"
            :class="{ 'is-invalid': v$?.is_paid_lunch?.$errors.length }"
          >
          <div class="invalid-feedback">
            {{ v$?.is_paid_lunch?.$errors?.[0]?.$message || v$?.is_paid_lunch?.$errors?.[0]?.$params?.message }}
          </div>
        </div>
      </div>

      <div class="mb-3 row">
        <label class="col-sm-2 col-form-label">
          <button type="button" class="btn-sm btn-primary d-inline" @click="addSpecialVacancy">{{ $t('general.specialVacancies') }}</button>
        </label>
        <div class="col-sm-10">

        </div>
      </div>

      <div class="mb-3 row" v-if="form.job_type_id">
        <label for="inputName" class="col-sm-2 col-form-label">{{ $t('general.specialVacancies') }} ({{ $t('general.type') }})</label>
        <div class="col-sm-10">
          <Multiselect
            v-model="form.job_type_id"
            valueProp="id"
            :options="jobTypes"
            :searchable="true"
            label="name_t"
            track-by="name_t"
            :class="{ 'is-invalid': v$.job_type_id.$errors.length }"
            disabled
          />
          <div class="invalid-feedback">
            {{ v$?.job_type_id?.$errors?.[0]?.$message || v$?.job_type_id?.$errors?.[0]?.$params?.message }}
          </div>
        </div>
      </div>

      <div class="mb-3 row" v-if="selectedBrancheOptions?.length > 0">
        <label for="inputName" class="col-sm-2 col-form-label">{{ $t('general.branche') }} ({{ $t('general.special') }})</label>
        <div class="col-sm-10">
          <Multiselect
            v-model="form.specialBranches"
            valueProp="id"
            :options="selectedBrancheOptions"
            :searchable="true"
            label="title_b"
            track-by="title_b"
            mode="tags"
            @deselect="unSelectBranche"
            @clear="clearBranche"
          />
          <!-- <div class="invalid-feedback">
            {{ v$?.establishment_id?.$errors?.[0]?.$message || v$?.establishment_id?.$errors?.[0]?.$params?.message }}
          </div> -->
        </div>
      </div>

      <div class="mb-3 row" v-if="selectedEmployeeOptions?.length > 0">
        <label for="inputName" class="col-sm-2 col-form-label">{{ $t('general.collaborator') }} ({{ $t('general.special') }})</label>
        <div class="col-sm-10">
          <Multiselect
            v-model="form.specialEmployees"
            valueProp="id"
            :options="selectedEmployeeOptions"
            :searchable="true"
            label="name"
            track-by="name"
            mode="tags"
            @deselect="unSelectEmployee"
            @clear="clearEmployees"
          />
          <!-- <div class="invalid-feedback">
            {{ v$?.establishment_id?.$errors?.[0]?.$message || v$?.establishment_id?.$errors?.[0]?.$params?.message }}
          </div> -->

          <div class="invalid-feedback d-block" v-if="form?.job_type_id == 5 && +form.employee_qnt > +form.specialEmployees?.length">
            Необходимое количество сотрудников больше, чем поле «Сотрудники»
          </div>
        </div>
      </div>

      <div class="row g-3">
        <div class='demo-app-main'>
          <FullCalendar :options='calendarOptions'>
            <template v-slot:eventContent='arg'>
              <span class="event-title-calendar">{{ arg.event.title }}</span>
              <div @click.stop="removeTime(arg)" class="removeTimeCalendar">
                <font-awesome-icon icon="trash" />
              </div>
            </template>
          </FullCalendar>
          <div class="invalid-feedback" v-if="v$?.graphics?.$errors?.[0]?.$message || v$?.graphics?.$errors?.[0]?.$params?.message" style="display: block;">
            {{ v$?.graphics?.$errors?.[0]?.$message || v$?.graphics?.$errors?.[0]?.$params?.message }}
          </div>
        </div>
      </div>

      <div class="mb-3 row align-items-center">
        <label for="is_active" class="col-sm-1 col-form-label">{{ $t('general.active') }}</label>
        <div class="col-sm-11">
          <input
            type="checkbox"
            class="form-check-input"
            id="is_active"
            v-model="form.is_active"
            :true-value="1"
            :false-value="0"
            :class="{ 'is-invalid': v$?.is_active?.$errors.length }"
          >
          <div class="invalid-feedback">
            {{ v$?.is_active?.$errors?.[0]?.$message || v$?.is_active?.$errors?.[0]?.$params?.message }}
          </div>
        </div>
      </div>

      <div class="col-md-12">
        <button type="submit" class="btn btn-primary me-3" @click="submit">{{ !data?.id ? $t('general.save') : $t('general.edit') }}</button>
        <button type="button" class="btn btn-danger" v-if="data?.id" @click="deleteData(data.id)">{{ $t('general.delete') }}</button>
      </div>
    </form>
  </div>
</div>
</template>

<script setup>

import { usePage } from '@inertiajs/vue3';

import AdminLayout from '@/Layouts/Admin/AppLayout.vue'
import AddCalendarDate from '../../Modals/AddCalendarDate.vue'
import AddSpecialVacancy from '../../Modals/AddSpecialVacancy.vue'
import axios from 'axios'
import { useForm } from '@inertiajs/vue3'
import useVuelidate from '@vuelidate/core'
import { Required, Numeric, AboveZero } from '../../../../Utils/validationMessages'
import { ref, defineEmits, reactive, watch } from 'vue'
import { notify } from '@kyvg/vue3-notification'
import { router } from '@inertiajs/vue3'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import { INITIAL_EVENTS, createEventId } from '@/Utils/fullCalendar'
import { getEstablishmentById } from '@/Services/Misc.js'
import { getBranchesByCompanyId, getEstablishmentsByCompanyId, getAdvantageEmployeesByCompanyId } from '@/Services/Misc.js'
import moment from 'moment'

const props = defineProps({
  title: String,
  data: Object,
  establishments: Object,
  currencies: Object,
  branches: Object,
  jobTypes: Object,
  jobSpecialBrancheIDS: Array,
  jobSpecialEmployeeIDS: Array,
  selectedBrancheOptions: Object,
  selectedEmployeeOptions: Object,
  companies: Object,
  advantageEmployees: Object,
  advantageEmployeeIDS: Array
});

const branches = ref(props?.branches)
const establishments = ref(props?.establishments)
const advantageEmployees = ref(props?.advantageEmployees)

const openAddCalendarPopup = ref(false)
const modalData = ref({})
const selectInfoData = ref({})
const establishment = ref({})
const searchURL = ref(window.location.search)
const showSpecialWorkerBlock = ref(false)
const openSpecialVacancyPopup = ref(false)
const selectedBrancheOptions = ref(props?.selectedBrancheOptions)
const selectedEmployeeOptions = ref(props?.selectedEmployeeOptions)

const rememberData = ref([])

const form = useForm({
  id: props?.data?.id,
  job_type_id: props?.data?.job_type_id,
  company_id: props?.data?.company_id,
  establishment_id: props?.data?.establishment_id,
  branche_id: props?.data?.branche_id,
  currency_id: 1,
  bonus: props?.data?.bonus,
  employee_qnt: props?.data?.employee_qnt,
  is_need_internship: props?.data?.is_need_internship || 0,
  is_paid_lunch: props?.data?.is_paid_lunch || 0,
  is_active: props?.data?.is_active || 0,
  graphics: props?.data?.graphics || [],
  specialEmployees: props?.jobSpecialEmployeeIDS,
  specialBranches: props?.jobSpecialBrancheIDS,
  advantageEmployees: props?.advantageEmployeeIDS
});

const rules = {
  job_type_id: {  },
  company_id: {  },
  establishment_id: { Required },
  branche_id: { Required },
  currency_id: { Required },
  bonus: { Numeric },
  employee_qnt: { Required, Numeric, AboveZero },
  is_active: {  },
  is_need_internship: {  },
  is_paid_lunch: {  },
  graphics: { Required },
};

let initialGraphics = []

if(props?.data?.job_type_id) {
  showSpecialWorkerBlock.value = true
}

if(form.graphics) {
  for(const graphic in form.graphics) {
    let startTime = moment(form.graphics[graphic].started_at).format("HH:mm")
    let endTime = moment(form.graphics[graphic].finished_at).format("HH:mm")
    let lunch = form.graphics[graphic].lunch

    initialGraphics.push({
      id: form.graphics[graphic].id,
      title: `${startTime}  - ${endTime} / Пер. ${lunch} мин.`,
      start: moment(form.graphics[graphic].started_at).format("YYYY-MM-DD"),
      startTimeValue: startTime,
      endTimeValue: endTime,
      color: +form.graphics[graphic].is_late_added ? '#c7b900' : ''
    })
  }
}

const currentEvents = ref({})

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
  initialView: 'dayGridMonth',
  height: window.screen.height - 300,
  initialEvents: initialGraphics, // alternatively, use the `events` setting to fetch from a feed
  initialDate: form.graphics?.[0]?.started_at,
  editable: true,
  selectable: true,
  selectMirror: true,
  dayMaxEvents: true,
  weekends: true,
  eventDurationEditable: false,
  select: handleDateSelect,
  selectLongPressDelay: 100,

  eventClick: handleEventClick,
  eventsSet: handleEvents,

  eventDrop: dragEvent,
  // eventLongPressDelay: dragEvent,

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

  if(form?.job_type_id == 5 && +form.employee_qnt > +form.specialEmployees?.length) {
    return false
  }

  axios.post(routeUrl, form).then((response) => {
    notify({
      title: response.data.message,
      type: 'success'
    })
    router.get(route('company_jobs'))
  }).catch(error => {

    notify({
      title: error.response.data.message,
      type: 'error'
    })


    errorMsg.value = error.response.data.message
  });
}

const addSpecialVacancy = (e) => {
  openSpecialVacancyPopup.value = true
}

const clearBranche = (e) => {
  form.job_type_id = ''
  form.specialBranches = []
  selectedBrancheOptions.value = []
}

const clearEmployees = (e) => {
  form.job_type_id = ''
  form.specialEmployees = []
  selectedEmployeeOptions.value = []
}

const unSelectBranche = (e) => {
  selectedBrancheOptions.value = selectedBrancheOptions.value.filter(function(value, index, arr){
    return value.id != e;
  });

  if(!form.specialBranches.length) {
    form.job_type_id = ''
    form.specialBranches = []
    selectedBrancheOptions.value = []
  }
}

const unSelectEmployee = (e) => {
  selectedEmployeeOptions.value = selectedEmployeeOptions.value.filter(function(value, index, arr){
    return value.id != e;
  });

  if(!form.specialBranches.length) {
    form.job_type_id = ''
    form.specialEmployees = []
    selectedEmployeeOptions.value = []
  }
}

const setSpecialVacancy = (e) => {
  openSpecialVacancyPopup.value = false
  if(e) {
    form.job_type_id = e?.job_type_id

    if(e?.job_type_id == 1 || e?.job_type_id == 2) {
      form.specialBranches = []
      form.specialEmployees = []
    } else if(e?.job_type_id == 3 || e?.job_type_id == 4 || e?.job_type_id == 5) {

      if(e?.job_type_id == 3 || e?.job_type_id == 4) {
        form.specialEmployees = []
        selectedEmployeeOptions.value = []

        if(e?.specialBranches.length > 0) {
          for(let i in e?.specialBranches) {
            if(!form.specialBranches.includes(e?.specialBranches[i])) {
              form.specialBranches.push(e?.specialBranches[i])
            }
          }
        }

        if(e?.selectedBrancheOptions.length > 0) {
          for(let i in e?.selectedBrancheOptions) {
            if(!selectedBrancheOptions.value.includes(e?.selectedBrancheOptions[i].id)) {
              selectedBrancheOptions.value.push(e?.selectedBrancheOptions[i])
            }
          }
        }

      } else if(e?.job_type_id == 5) {
        form.specialBranches = []
        selectedBrancheOptions.value = []

        if(e?.specialEmployees.length > 0) {
          for(let i in e?.specialEmployees) {
            if(!form.specialEmployees.includes(e?.specialEmployees[i])) {
              form.specialEmployees.push(e?.specialEmployees[i])
            }
          }
        }

        if(e?.selectedEmployeeOptions.length > 0) {
          for(let i in e?.selectedEmployeeOptions) {
            if(!selectedEmployeeOptions.value.includes(e?.selectedEmployeeOptions[i].id)) {
              selectedEmployeeOptions.value.push(e?.selectedEmployeeOptions[i])
            }
          }
        }
      }
    } else {
      form.specialBranches = []
      form.specialEmployees = []
      selectedEmployeeOptions.value = []
      selectedBrancheOptions.value = []
    }
  }
}

const rememberGraphic = (e) => {
  if(e.type == 'add')
    rememberData.value[e.field] = e.value
  else
    delete rememberData.value[e.field]
}

const setTime = (e) => {
  openAddCalendarPopup.value = false

  if(!e?.started_at || !e?.finished_at) return false

  const startedAt = setTimeFormat(e.started_at)
  const finishedAt = setTimeFormat(e.finished_at)
  const lunch = e?.lunch

  let calendarApi = selectInfoData.value.view.calendar
  calendarApi.unselect()


  if(form.graphics.some(element => element.id == e?.event?.event?._def?.publicId)) {
    selectInfoData.value.event.setProp('title', `${startedAt} - ${finishedAt} / Пер. ${lunch} мин.`);
    selectInfoData.value.event.setProp('start', selectInfoData.value.event.startStr);
    selectInfoData.value.event.setProp('end', selectInfoData.value.event.startStr);
    selectInfoData.value.event.setExtendedProp('startTimeValue', startedAt);
    selectInfoData.value.event.setExtendedProp('endTimeValue', finishedAt);
    selectInfoData.value.event.setExtendedProp('lunch', lunch);

    form.graphics.map(function(value, index) {
      if(value.id == e?.event?.event?._def?.publicId) {
        value.started_at = `${selectInfoData.value.event.startStr} ${startedAt}`,
        value.finished_at = `${selectInfoData.value.event.startStr} ${finishedAt}`
        value.lunch = lunch
      }
      return value
    })

  } else {
    const uniqueId = new Date().valueOf()

    form.graphics.push({
      'id': uniqueId,
      'started_at': `${selectInfoData.value.startStr} ${startedAt}`,
      'finished_at': `${selectInfoData.value.startStr} ${finishedAt}`,
      'lunch': lunch
    })

    calendarApi.addEvent({
      id: uniqueId,
      title: `${startedAt} - ${finishedAt} / Пер. ${lunch} мин.`,
      start: selectInfoData.value.startStr,
      end: selectInfoData.value.startStr,
      startTimeValue: startedAt,
      endTimeValue: finishedAt,
      lunch: lunch,
    })
  }

}

const setTimeFormat = (time) => {
  let hours = +time.hours < 10 ?  `0${+time.hours}` : time.hours;
  let minutes = +time.minutes < 10 ?  `0${+time.minutes}` : time.minutes;

  return `${hours}:${minutes}`
}

function handleDateSelect(selectInfo) {
  console.log(11)
  selectInfoData.value = selectInfo
  modalData.value = props.data
  openAddCalendarPopup.value = true
}

function handleEventClick(clickInfo) {
  console.log(22)
  selectInfoData.value = clickInfo
  modalData.value = props.data
  openAddCalendarPopup.value = true
}

function handleEvents(events) {

}

function dragEvent(event) {
  form.graphics = form.graphics.filter(function(value, index, arr) {
    if(value.id == event?.event?._def?.publicId) {

      const startDt = moment(event?.event?.start).format("YYYY-MM-DD")
      const startedAt = moment(value.started_at).format("HH:mm")
      const finishedAt = moment(value.finished_at).format("HH:mm")

      value.started_at = startDt + ' ' + startedAt
      value.finished_at = startDt + ' ' + finishedAt
    }

    return value
  });
}

const removeTime =  (clickInfo) => {
  if (confirm(`Вы уверены, что хотите удалить '${clickInfo.event.title}'`)) {
    clickInfo.event.remove()

    form.graphics = form.graphics.filter(function(value, index, arr){
      return value.id != clickInfo.event._def.publicId;
    });
  }

  return false;
}

const changeEstablishment = async (value) => {
  if(!value) {
    establishment.value = {}
    return true
  }

  const data = await getEstablishmentById(value)
  establishment.value = data
}

const establishmentSelect = ref(null)
const changeCompany = async (value) => {
  form.establishment_id = null
  form.branche_id = null
  form.advantageEmployees = []
  establishments.value = []
  branches.value = []
  establishment.value = {}
  establishmentSelect.value.clear()


  const branchesData = await getBranchesByCompanyId(value)
  branches.value = branchesData

  const establishmentsData = await getEstablishmentsByCompanyId(value)
  establishments.value = establishmentsData

  const advantageEmployeesData = await getAdvantageEmployeesByCompanyId(value)
  advantageEmployees.value = advantageEmployeesData
}

changeEstablishment(props?.data?.establishment_id)

const deleteData = (id) => {
  if (confirm('Подтвердить удаление?') == true) {
    form.post(route('delete_company_job', id), {
      onFinish: () => {

      },
    })
  }
}

</script>
