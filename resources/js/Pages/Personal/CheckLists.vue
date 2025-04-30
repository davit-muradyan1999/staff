<template>
  <Head :title="title" />

  <AddShifReason
    v-if="openShiftReasonPopup"
    :job="selectedJob"
    :jobs="selectedUpdatedJobs"
    :shiftReasons="shiftReasons"
    :type="selectedType"
    :all="selectedAll"
    :checkeds="checkAllCheckboxes"
    @close="setJobReasons"
  />

  <div class="row">
    <div class="d-flex justify-content-center">
      <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link" :class="{'active': activeTab == 'waiting'}" @click="activeTab = 'waiting'" id="pills-waiting-tab" data-bs-toggle="pill" data-bs-target="#pills-waiting" type="button" role="tab" aria-controls="pills-waiting" aria-selected="true">{{ $t('general.waiting') }} <span class="job-tab-count">{{ waitingJobsCount }}</span></button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" :class="{'active': activeTab == 'history'}" @click="activeTab = 'history'" id="pills-history-tab" data-bs-toggle="pill" data-bs-target="#pills-history" type="button" role="tab" aria-controls="pills-history" aria-selected="true">{{ $t('general.history') }} <span class="job-tab-count">{{ historyJobsCount }}</span></button>
        </li>
      </ul>
    </div>

    <div class="col-md-4 mb-3">
      <div class="input-group">
        <input v-model="search.text" class="form-control border-end-0 border search-moderator-input" type="text" id="example-search-input">
        <button class="btn btn-outline-secondary bg-white border-start-0 border-bottom-0 border ms-n5 search-moderator-button" type="button">
          <font-awesome-icon icon="magnifying-glass" />
        </button>
      </div>
    </div>
    <div class="col-md-4 mb-3">
      <Multiselect
        v-model="search.positions"
        valueProp="id"
        :options="positions"
        :searchable="true"
        mode="tags"
        label="name_p"
        track-by="name_p"
        :placeholder="`${$t('general.positions')}`"
      />
    </div>
    <div class="col-md-4 mb-3">
      <Multiselect
        v-model="search.companies"
        valueProp="ju_user_id"
        :options="companies"
        :searchable="true"
        mode="tags"
        label="ju_user_company_name"
        track-by="ju_user_company_name"
        :placeholder="`${$t('general.company')}`"
      />
    </div>

    <template v-if="activeTab == 'history'">
      <div class="col-md-4 mb-3">
        <Multiselect
          v-model="search.status"
          valueProp="status"
          :options="filterStatuses"
          :searchable="true"
          label="name"
          track-by="name"
          :placeholder="`${$t('general.status')}`"
          @change="changeStatus"
        />
      </div>

      <div class="col-md-4 mb-3">
        <Multiselect
          v-model="search.shiftReasons"
          valueProp="id"
          :options="filterShiftReasons"
          :searchable="true"
          mode="tags"
          label="name_s"
          track-by="name_s"
          :placeholder="`${$t('general.reasons')}`"
        />
      </div>

      <div class="col-md-4 mb-3">
        <div class="row">
          <div class="col-md-6 mb-3">
            <DatePicker v-model="search.startedAt" :model-config="pickerConfig" :masks="masks" mode="date" locale="ru">
              <template v-slot="{ inputValue, inputEvents }">
                <input
                  class="form-control check-list-dt"
                  :value="inputValue"
                  v-on="inputEvents"
                  :placeholder="`${$t('general.beginning')}`"
                />
              </template>
            </DatePicker>
            <div class="invalid-feedback" v-if="search.startedAt && search.finishedAt && search.startedAt > search.finishedAt" style="display: block;">
              {{ $t('messages.checkTheDatesAreCorrect') }}
            </div>
          </div>
          <div class="col-md-6">
            <DatePicker v-model="search.finishedAt" :model-config="pickerConfig" :masks="masks" mode="date" locale="ru">
              <template v-slot="{ inputValue, inputEvents }">
                <input
                  class="form-control check-list-dt"
                  :value="inputValue"
                  v-on="inputEvents"
                  :placeholder="`${$t('general.end')}`"
                />
              </template>
            </DatePicker>
          </div>
        </div>
      </div>
    </template>
  </div>

  <div class="mt-3">
    <div class="tab-content">
      <div class="tab-pane" :class="{'active': activeTab == 'waiting', 'show': activeTab == 'waiting'}" id="pills-waiting" role="tabpanel" aria-labelledby="pills-waiting-tab">
        <div class="card mb-4">
          <div class="card-body">
            <div class="row bg-white">
              <div class="col-md-4">
                {{ $t('general.totalSalary')}}
                <span class="h4 ms-1 text-success">{{ useFixedNumber(totalWaitingJobs) }}</span>
                <span class="ms-1 text-success">{{ $t('general.rub')}}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="table-responsive table-list">
          <div class="card mt-3" v-for="(job, key) in waitingJobLists" :key="key">
            <div class="card-body p-3 position-relative testimonial-group">
              <div class="row bg-white">
                <div class="col border-end">{{ moment(job?.jtg_finished_at).format("YYYY-MM-DD") }}</div>
                <div class="col border-end">
                  <a class="text-decoration-none" target="_blank" :href="route('company_profile', { id: job?.company_id })">
                    {{ job.j_user_company_name }}
                  </a>
                </div>
                <div class="col border-end">{{ job?.branche?.title_b || job?.establishment?.company?.company_name }}</div>
                <div class="col border-end">
                  <a class="text-decoration-none" target="_blank" :href="route('job_show_list', job.id)">
                    {{ job?.establishment?.name_e }} <span class="job-id">#{{ job.id }}</span>
                  </a>
                </div>
                <div class="col border-end">
                  {{ moment(job?.jtg_started_at).format("HH:mm") }} -
                  {{ moment(job?.jtg_finished_at).format("HH:mm") }}
                </div>
                <div class="col border-end">{{ useMinuteToHours(job?.total_minutes) }} ({{ $t('general.hours_2') }})</div>
                <div class="col"><span class="float-start">{{ useFixedNumber(job?.salary_waiting_total) }} {{ $t('general.rub') }}</span></div>

              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="tab-pane" :class="{'active': activeTab == 'history', 'show': activeTab == 'history'}" id="pills-history" role="tabpanel" aria-labelledby="pills-history-tab">
        <div class="card mb-4">
          <div class="card-body">
            <div class="row bg-white">
              <div class="col-md-4">
                {{ $t('general.totalSalary')}}
                <span class="h4 ms-1 text-success">{{ useFixedNumber(totalHistoryJobs) }}</span>
                <span class="ms-1 text-success">{{ $t('general.rub')}}</span>
              </div>
            </div>
          </div>
        </div>
        <div class="card mt-3" v-for="(job, key) in historyJobLists" :key="key">
          <div class="table-responsive table-list">
            <div class="card-body p-3 position-relative testimonial-group">
              <div class="row bg-white">
                <div class="col border-end">{{ moment(job?.jcl_started_at).format("YYYY-MM-DD") }}</div>
                <div class="col border-end">
                  <a class="text-decoration-none" target="_blank" :href="route('company_profile', { id: job?.company_id })">
                    {{ job.j_user_company_name }}
                  </a>
                </div>
                <div class="col border-end">{{ job?.branche?.title_b || job?.establishment?.company?.company_name }}</div>
                <div class="col border-end">
                  <a class="text-decoration-none" target="_blank" :href="route('job_show_list', job.id)">
                    {{ job?.establishment?.name_e }} <span class="job-id">#{{ job.id }}</span>
                  </a>
                </div>
                <div class="col border-end">
                  <span :class="job?.jtg_started_at != job?.jcl_started_at ? 'text-danger' : ''">{{ moment(job?.jcl_started_at).format("HH:mm") }}</span> -
                  <span :class="job?.jtg_finished_at != job?.jcl_finished_at ? 'text-danger' : ''">{{ moment(job?.jcl_finished_at).format("HH:mm") }}</span>
                </div>
                <div class="col border-end">{{ useMinuteToHours(job?.total_minutes) }} ({{ $t('general.hours_2') }})</div>
                <div class="col">
                  <span class="float-start" :class="{
                      'text-danger': (job?.jtg_finished_at != job?.jcl_finished_at) || job?.jcl_status != 'CONFIRMED',
                      'text-decoration-line-through': job?.jcl_status != 'CONFIRMED'
                    }">{{ useFixedNumber(job?.ebl_amount) }} {{ $t('general.rub') }}
                  </span>
                </div>
                <div class="col border-end">
                  <span :class="job?.jcl_status == 'CONFIRMED' ? 'text-success' : 'text-danger'">{{ job?.jcl_status == 'CONFIRMED' ? $t('general.confirmed') : $t('general.canceled') }}</span>
                </div>
                <div class="col">
                  <font-awesome-icon icon="circle-info" class="list-info" @click="editDayGraphic(job, 'history')" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</template>

<script setup>
import Layout from '@/Layouts/Personal/AppLayout.vue'
import { ref, watch } from 'vue'
import AddShifReason from '../../Pages/Company/Modals/AddShifReason.vue'
import { router } from '@inertiajs/vue3'
import moment from 'moment'
import { DatePicker } from 'v-calendar'
import { useMinuteToHours } from '@/Hooks/useMinuteToHours'
import { useFixedNumber } from '@/Hooks/useFixedNumber'

defineOptions({ layout: Layout })

const urlParams = new URLSearchParams(window.location.search)

const allCompaniesSelected = ref(true)
const activeTab = ref(urlParams.get('tab') || 'waiting')

let selectedEstablishments = []
let selectedPositions = []
let selectedCompanies = []
let selectedShiftReasons = []

if([...urlParams.getAll('establishments[]')].length > 0) {
  for(let id in [...urlParams.getAll('establishments[]')]) {
    selectedEstablishments.push([...urlParams.getAll('establishments[]')][id])
  }
}

if([...urlParams.getAll('positions[]')].length > 0) {
  for(let id in [...urlParams.getAll('positions[]')]) {
    selectedPositions.push([...urlParams.getAll('positions[]')][id])
  }
}

if([...urlParams.getAll('companies[]')].length > 0) {
  for(let id in [...urlParams.getAll('companies[]')]) {
    selectedCompanies.push(+[...urlParams.getAll('companies[]')][id])
  }
  allCompaniesSelected.value = false
}

if([...urlParams.getAll('shiftReasons[]')].length > 0) {
  for(let reason in [...urlParams.getAll('shiftReasons[]')]) {
    selectedShiftReasons.push(+[...urlParams.getAll('shiftReasons[]')][reason])
  }
}

const props = defineProps({
  title: String,
  positions: Object,
  permissions: Object,
  shiftReasons: Object,
  waitingJobs: Object,
  establishments: Object,
  historyJobs: Object,
  waitingJobsCount: Number,
  historyJobsCount: Number,
  totalWaitingJobs: Number,
  totalHistoryJobs: Number,
  filterShiftReasons: Object,
  companies: Object,
  startedAt: String,
  finishedAt: String,
});

const search = ref({
  text: urlParams.get('text'),
  status: urlParams.get('status'),
  positions: selectedPositions,
  establishments: selectedEstablishments,
  companies: selectedCompanies,
  shiftReasons: selectedShiftReasons,
  startedAt: urlParams.get('startedAt') || props?.startedAt,
  finishedAt: urlParams.get('finishedAt') || props?.finishedAt,
});

const checkAllCheckboxes = ref([])
const checkedAll = ref(false)

const waitingJobLists = ref(props.waitingJobs)
const historyJobLists = ref(props.historyJobs)

const openShiftReasonPopup = ref(false)
const selectedType = ref('')
const selectedJob = ref({})

const filterStatuses = ref([
  {
    'status': 'CONFIRMED',
    'name': 'Подтверждено',
  },
  {
    'status': 'CANCELED',
    'name': 'Отменено',
  }
])

const selectedAll = ref('')
const selectedUpdatedJob = ref({})
const selectedUpdatedJobs = ref({})

const editDayGraphic = (job, type, all = '') => {
  openShiftReasonPopup.value = true
  selectedJob.value = job
  selectedUpdatedJobs.value = waitingJobLists.value
  selectedType.value = type
  selectedAll.value = all
}

const changeStatus = (e) => {
  search.value.shiftReasons = []
}

const setJobReasons = (e) => {
  if(e?.form?.id) {
    waitingJobLists.value.filter(function (obj) {
      if(obj?.jtg_id == e?.form?.id) {
        let startHour = ''
        let finishHour = ''

        if(e?.form.started_at && e?.form.finished_at) {
          let hourS = '';
          let hourE = '';

          let startMinutes = '';
          let endMinutes = '';

          if(typeof e?.form.started_at.hours == 'number' && e?.form.started_at.hours < 10) {
            hourS = '0'
          }

          if(typeof e?.form.finished_at.hours == 'number' && e?.form.finished_at.hours < 10) {
            hourE = '0'
          }

          if(typeof e?.form.started_at.minutes == 'number' && e?.form.started_at.minutes < 10) {
            startMinutes = '0'
          }

          if(typeof e?.form.finished_at.minutes == 'number' && e?.form.finished_at.minutes < 10) {
            endMinutes = '0'
          }

          startHour = hourS + e?.form.started_at.hours + ':' + startMinutes + e?.form.started_at.minutes
          finishHour = hourE + e?.form.finished_at.hours + ':' + endMinutes + e?.form.finished_at.minutes
        }


        const newStartDT = moment(obj?.jtg_started_at).format("YYYY-MM-DD") + ' ' + startHour
        const newEndDT = moment(obj?.jtg_finished_at).format("YYYY-MM-DD") + ' ' + finishHour

        obj.reasons = e?.form?.reasons
        obj.description = e?.form?.description
        obj.jtg_started_at = newStartDT
        obj.jtg_finished_at = newEndDT
      }
    })
  }

  openShiftReasonPopup.value = false
}

watch(search.value, (currentValue, oldValue) => {
  router.get(route('my_check_lists'), {
    search: true,
    text: currentValue.text,
    status: currentValue.status,
    tab: activeTab.value,
    positions: currentValue.positions,
    establishments: currentValue.establishments,
    companies: currentValue.companies,
    shiftReasons: currentValue.shiftReasons,
    startedAt: currentValue.startedAt,
    finishedAt: currentValue.finishedAt,
  }, {
    preserveState: true
  })
});

watch(() => props?.waitingJobs, (first, second) => {
  waitingJobLists.value = first
});

watch(() => props?.historyJobs, (first, second) => {
  historyJobLists.value = first
});

const setTotalPerEmployee = (job, type = null) => {
  const salary = job?.establishment?.salary ? job?.establishment?.salary : 0
  let totalHours = 0
  // const bonus = job?.bonus ? job?.bonus : 0
  const bonus = 0

  if(job?.list_total_minutes) {
    totalHours =  job?.list_total_minutes ? useFixedNumber(job?.list_total_minutes / 60, 4) : 0
  } else {
    totalHours =  job?.total_minutes ? useFixedNumber(job?.total_minutes / 60, 4) : 0
  }

  return useFixedNumber((+salary * +totalHours) + +bonus)
}

const pickerConfig = {
  type: 'string',
  mask: 'YYYY-MM-DD',
}

const masks = {
  input: 'YYYY-MM-DD',
}

</script>
