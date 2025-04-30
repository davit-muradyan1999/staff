<template>
  <Head :title="title" />
  <div class="d-flex justify-content-center list-tabs">
    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="bids-tab" data-bs-toggle="pill" data-bs-target="#bids" type="button" role="tab" aria-controls="bids" aria-selected="true">{{ $t("general.bids") }} <span class="job-tab-count">{{ bidsJobsCount }}</span></button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="current-tab" data-bs-toggle="pill" data-bs-target="#current" type="button" role="tab" aria-controls="current" aria-selected="false">{{ $t("general.currentPositions") }} <span class="job-tab-count">{{ currentJobsCount }}</span></button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="history-tab" data-bs-toggle="pill" data-bs-target="#history" type="button" role="tab" aria-controls="history" aria-selected="false">{{ $t("general.history") }} <span class="job-tab-count">{{ historyJobsCount }}</span></button>
      </li>
    </ul>
  </div>

  <div class="row mt-3">
    <div class="col-md-4 mb-3">
      <div class="input-group">
        <input v-model="search.text" class="form-control border-end-0 border search-moderator-input" type="text" id="example-search-input">
        <button class="btn btn-outline-secondary bg-white border-start-0 border-bottom-0 border ms-n5 search-moderator-button" type="button">
          <font-awesome-icon icon="magnifying-glass" />
        </button>
      </div>
    </div>

    <div class="col-md-4 mb-3" v-if="$page.props.auth?.user?.role == 'ADMIN' || $page.props.auth?.user?.role == 'ADMINISTRATOR'">
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

    <div class="col-md-4 mb-3" v-if="$page.props.auth?.user?.role == 'ADMIN' || $page.props.auth?.user?.role == 'ADMINISTRATOR'">
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

    <div class="col-md-4 mb-3" v-if="false">
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

    <div class="col-md-4 mb-2" v-if="$page.props.auth?.user?.role == 'ADMIN' || $page.props.auth?.user?.role == 'ADMINISTRATOR'">
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
  </div>

  <div class="tab-content">
    <div class="tab-pane fade show active" id="bids" role="tabpanel" aria-labelledby="bids-tab">
      <JobInfo v-for="job in bidsJobs" :key="job.id" :job="job" :searchURL="''" :editable="false" :showFavorite="false" />
    </div>

    <div class="tab-pane fade" id="current" role="tabpanel" aria-labelledby="current-tab">
      <JobInfo v-for="job in currentJobs" :key="job.id" :job="job" :searchURL="''" :editable="false" :showFavorite="false" />
    </div>
    <div class="tab-pane fade" id="history" role="tabpanel" aria-labelledby="history-tab">
      <JobInfo v-for="job in historyJobs" :key="job.id" :job="job" :searchURL="''" :editable="false" type="history" :showFavorite="false" />
    </div>
  </div>
</template>

<script setup>
import AdminLayout from '@/Layouts/Admin/AppLayout.vue'
import JobInfo from '@/Components/JobInfo.vue'
import { ref, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

defineOptions({ layout: AdminLayout })

const props = defineProps({
  title: String,
  bidsJobs: Object,
  currentJobs: Object,
  historyJobs: Object,
  bidsJobsCount: Number,
  currentJobsCount: Number,
  historyJobsCount: Number,
  companies: Object,
  branches: Object,
  employees: Object,
  establishments: Object,
})

const urlParams = new URLSearchParams(window.location.search)

let selectedEstablishments = []
let selectedPositions = []
let selectedCompanies = []
let selectedBranches = []
let selectedEmployees = []

if([...urlParams.getAll('branches[]')].length > 0) {
  for(let brancheID in [...urlParams.getAll('branches[]')]) {
    selectedBranches.push(+[...urlParams.getAll('branches[]')][brancheID])
  }
}

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
}

if([...urlParams.getAll('employees[]')].length > 0) {
  for(let id in [...urlParams.getAll('employees[]')]) {
    selectedEmployees.push(+[...urlParams.getAll('employees[]')][id])
  }
}

const activeTab = ref(urlParams.get('tab') || 'bids')

const search = ref({
  text: urlParams.get('text'),
  company_id: urlParams.get('company_id'),
  positions: selectedPositions,
  establishments: selectedEstablishments,
  companies: selectedCompanies,
  branches: selectedBranches,
  employees: selectedEmployees,
})

watch(search.value, (currentValue, oldValue) => {
  router.get(route('active_works'), {
    search: true,
    text: currentValue.text,
    tab: activeTab.value,
    company_id: currentValue.company_id,
    positions: currentValue.positions,
    establishments: currentValue.establishments,
    companies: currentValue.companies,
    branches: currentValue.branches,
    employees: currentValue.employees,
  }, {
    preserveState: true
  })
})

const changeCompany = (e) => {
  search.value.branches = []
}
</script>
