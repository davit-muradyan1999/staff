<template>
  <Head :title="title" />

  <SetStatus
    v-if="openSetStatusPopup"
    :job="selectedJob"
    :type="selectedType"
    @close="openSetStatusPopup = false"
  />

  <div class="row">
    <div class="d-flex justify-content-center">
      <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link" :class="{'active': activeTab == 'waiting'}" @click="activeTab = 'waiting'" id="pills-waiting-tab" data-bs-toggle="pill" data-bs-target="#pills-waiting" type="button" role="tab" aria-controls="pills-waiting" aria-selected="true">{{ $t('general.waiting') }} <span class="job-tab-count">{{ waitingJobsCount }}</span></button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" :class="{'active': activeTab == 'history'}" @click="activeTab = 'history'" id="pills-history-tab" data-bs-toggle="pill" data-bs-target="#pills-history" type="button" role="tab" aria-controls="pills-history" aria-selected="true">{{ $t('general.history') }} <span class="job-tab-count">{{ bonusHistoriesCount }}</span></button>
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
        v-if="false"
        v-model="search.positions"
        valueProp="id"
        :options="positions"
        :searchable="true"
        mode="tags"
        label="name_p"
        track-by="name_p"
        :placeholder="`${$t('general.positions')}`"
      />

      <Multiselect
        v-model="search.establishments"
        valueProp="id"
        :options="establishments"
        :searchable="true"
        mode="tags"
        label="name_e"
        track-by="name_e"
        :placeholder="`${$t('general.establishment')}`"
      />
    </div>
    <div class="col-md-4 mb-3">
      <Multiselect
        v-model="search.company_id"
        valueProp="id"
        :options="companies"
        :searchable="true"
        label="company_name"
        track-by="company_name"
        @change="changeCompany"
        :placeholder="`${$t('general.company')}`"
      />
    </div>

    <div class="col-md-4 mb-3" v-if="activeTab == 'history'">
      <Multiselect
        v-model="search.branches"
        valueProp="id"
        :options="branches"
        :searchable="true"
        mode="tags"
        label="title_b"
        track-by="title_b"
        :placeholder="`${$t('general.branches')}`"
      />
    </div>

    <div class="col-md-4 mb-3">
      <Multiselect
        v-model="search.status"
        valueProp="value"
        :options="bonusStatuses.statuses"
        :searchable="true"
        label="name"
        track-by="name"
        :placeholder="`${$t('general.status')}`"
      />
    </div>

    <div class="col-md-4 mb-3">
      <Multiselect
        v-model="search.employees"
        valueProp="id"
        :options="employees"
        :searchable="true"
        mode="tags"
        label="name"
        track-by="name"
        :placeholder="`${$t('general.employees')}`"
      />
    </div>
    <div class="col-md-4" v-if="activeTab == 'history'">
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
        <div class="col-md-6 mb-3">
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
  </div>

  <div class="mt-3">
    <div class="tab-content">
      <div class="tab-pane" :class="{'active': activeTab == 'waiting', 'show': activeTab == 'waiting'}" id="pills-waiting" role="tabpanel" aria-labelledby="pills-waiting-tab">
        <div class="table-responsive table-list">
          <div class="card mt-3" v-for="(job, key) in waitingLists.data" :key="key">
            <div class="card-body p-4 position-relative ">
              <div class="row bg-white">
                <div class="col border-end">
                  {{ moment(job?.jtg_finished_at).format("YYYY-MM-DD") }}
                </div>
                <div class="col border-end">
                  <a class="text-decoration-none" target="_blank" :href="route('profile', { id: job.jb_user_id })">
                    {{ job.user_name }}
                  </a>
                </div>
                <div class="col border-end">
                  {{ job?.branche?.title_b || job?.establishment?.company?.company_name }}
                </div>
                <div class="col border-end">
                  <a class="text-decoration-none" target="_blank" :href="route('admin.show_job_list', job.id)">
                    {{ job?.establishment?.name_e }} <span class="job-id">#{{ job.id }}</span>
                  </a>
                </div>
                <div class="col border-end">
                  {{ job?.bonus }} {{ $t('general.rub') }}
                </div>
                <div class="col" v-if="usePermissionA([])">
                  <template v-if="job?.bonus">
                    <template v-if="!job?.bh_status">
                      <button type="button" class="btn-sm btn-primary mb-2 border-0 w-100" @click="setStatus(job, 'CONFIRMED')">
                        {{ $t("general.confirm") }}
                      </button>
                      <button type="button" class="btn-sm btn-danger border-0 w-100" @click="setStatus(job, 'CANCELED')">
                        {{ $t("general.revoke") }}
                      </button>
                    </template>
                    <template v-else>
                      <span :class="job?.bh_status == 'CONFIRMED' ? 'text-success' : 'text-danger'">{{ job?.bh_status == 'CONFIRMED' ? $t('general.confirmed') : $t('general.canceled') }}</span>
                    </template>
                  </template>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="paginate-block-end mt-3">
          <Pagination :links="waitingLists?.links" />
        </div>
      </div>
      <div class="tab-pane" :class="{'active': activeTab == 'history', 'show': activeTab == 'history'}" id="pills-history" role="tabpanel" aria-labelledby="pills-history-tab">
        <div class="table-responsive table-list">
          <div class="card mt-3" v-for="(bonus, key) in bonusHistories.data" :key="key">
            <div class="card-body p-4 position-relative ">
              <div class="row bg-white">
                <div class="col border-end">
                  {{ moment(bonus?.created_at).format("YYYY-MM-DD") }}
                </div>
                <div class="col border-end">
                  <a class="text-decoration-none" target="_blank" :href="route('profile', { id: bonus?.employee?.id})">
                    {{ bonus?.employee?.name }}
                  </a>
                </div>
                <div class="col border-end">
                  {{ bonus?.job?.branche?.title_b }}
                </div>
                <div class="col border-end">
                  <a class="text-decoration-none" target="_blank" :href="route('admin.show_job_list', bonus.job_id)">
                    {{ bonus?.job?.establishment?.name_e }} <span class="job-id">#{{ bonus.job_id }}</span>
                  </a>
                </div>
                <div class="col border-end">
                  {{ bonus?.amount }} {{ $t('general.rub') }}
                </div>
                <div class="col">
                  <span :class="bonus?.status == 'CONFIRMED' ? 'text-success' : 'text-danger'">{{ bonus?.status == 'CONFIRMED' ? $t('general.confirmed') : $t('general.canceled') }}</span>
                  <span v-if="bonus?.reason" class="d-block fst-italic"><small>({{ bonus?.reason }})</small></span>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="paginate-block-end mt-3">
          <Pagination :links="bonusHistories?.links" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import Layout from '@/Layouts/Admin/AppLayout.vue'
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import moment from 'moment'
import SetStatus from './Modals/SetStatus.vue'
import { DatePicker } from 'v-calendar'
import { useBonusStatuses } from '@/Hooks/useBonusStatuses'
import { usePermissionA } from '@/Hooks/usePermissionA'

defineOptions({ layout: Layout })

const urlParams = new URLSearchParams(window.location.search)

const bonusStatuses = useBonusStatuses()

let selectedEstablishments = []
let selectedPositions = []
let selectedBranches = []
let selectedShiftReasons = []
let selectedEmployees = []

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

if([...urlParams.getAll('branches[]')].length > 0) {
  for(let brancheID in [...urlParams.getAll('branches[]')]) {
    selectedBranches.push(+[...urlParams.getAll('branches[]')][brancheID])
  }
}

if([...urlParams.getAll('shiftReasons[]')].length > 0) {
  for(let reason in [...urlParams.getAll('shiftReasons[]')]) {
    selectedShiftReasons.push(+[...urlParams.getAll('shiftReasons[]')][reason])
  }
}

if([...urlParams.getAll('employees[]')].length > 0) {
  for(let id in [...urlParams.getAll('employees[]')]) {
    selectedEmployees.push(+[...urlParams.getAll('employees[]')][id])
  }
}

const openSetStatusPopup = ref(false)
const selectedJob = ref({})
const selectedType = ref('')

const setStatus = (job, type) => {
  openSetStatusPopup.value = true
  selectedJob.value = job
  selectedType.value = type
}

const props = defineProps({
  title: String,
  positions: Object,
  companies: Object,
  branches: Object,
  permissions: Object,
  shiftReasons: Object,
  waitingJobs: Object,
  establishments: Object,
  employees: Object,
  waitingJobsCount: Number,
  filterShiftReasons: Object,
  startedAt: String,
  finishedAt: String,
  bonusHistories: Object,
  bonusHistoriesCount: Number
});

const search = ref({
  text: urlParams.get('text'),
  status: urlParams.get('status'),
  positions: selectedPositions,
  establishments: selectedEstablishments,
  company_id: urlParams.get('company_id'),
  branches: selectedBranches,
  shiftReasons: selectedShiftReasons,
  employees: selectedEmployees,
  startedAt: urlParams.get('startedAt') || props?.startedAt,
  finishedAt: urlParams.get('finishedAt') || props?.finishedAt,
});

const waitingLists = ref(props.waitingJobs)
const activeTab = ref(urlParams.get('tab') || 'waiting')

const changeCompany = (e) => {
  search.value.branches = []
}

watch(search.value, (currentValue, oldValue) => {
  router.get(route('admin.check_list_bonuses'), {
    search: true,
    text: currentValue.text,
    status: currentValue.status,
    positions: currentValue.positions,
    establishments: currentValue.establishments,
    company_id: currentValue.company_id,
    branches: currentValue.branches,
    shiftReasons: currentValue.shiftReasons,
    employees: currentValue.employees,
    startedAt: currentValue.startedAt,
    finishedAt: currentValue.finishedAt,
  }, {
    preserveState: true
  })
});

watch(() => props?.waitingJobs, (first, second) => {
  waitingLists.value = first
});

const pickerConfig = {
  type: 'string',
  mask: 'YYYY-MM-DD',
};

const masks = {
  input: 'YYYY-MM-DD',
};

</script>
