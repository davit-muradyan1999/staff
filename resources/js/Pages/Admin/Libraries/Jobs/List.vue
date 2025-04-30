<template>
  <Head :title="title" />
  <AddJob
    v-if="openAddJobPopup"
    :data="selectedJob"
    :establishments="establishments"
    :currencies="currencies"
    :branches="branches"
    @close="openAddJobPopup = false"
  />
  <div class="mt-3 text-end">
    <Link class="edit-action-link d-inline float-end" :href="route('company_jobs_add') + searchURL">
      <button class="btn btn-success ps-3 pe-3 pt-2 pb-2 me-3" type="button">
        {{ $t('general.addJob') }}
      </button>
    </Link>
  </div>
  <div class="d-flex justify-content-center">
    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link" :class="{'active': activeTab == 'unaccepted'}" @click="activeTab = 'unaccepted'" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-home" type="button" role="tab" aria-controls="pills-home" aria-selected="true">{{ $t('general.unaccepted') }} <span class="job-tab-count">{{ unacceptedJobsCount }}</span></button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" :class="{'active': activeTab == 'active'}" @click="activeTab = 'active'" id="pills-active-tab" data-bs-toggle="pill" data-bs-target="#pills-active" type="button" role="tab" aria-controls="pills-active" aria-selected="true">Вакансии <span class="job-tab-count">{{ activeJobsCount }}</span></button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" :class="{'active': activeTab == 'inActions'}" @click="activeTab = 'inActions'" id="pills-current-tab" data-bs-toggle="pill" data-bs-target="#pills-current" type="button" role="tab" aria-controls="pills-current" aria-selected="true">{{ $t('general.inActions') }} <span class="job-tab-count">{{ inActionJobsCount }}</span></button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" :class="{'active': activeTab == 'passive'}"  @click="activeTab = 'passive'" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-profile" type="button" role="tab" aria-controls="pills-profile" aria-selected="false">Пассивные <span class="job-tab-count">{{ passiveJobsCount }}</span></button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" :class="{'active': activeTab == 'history'}"  @click="activeTab = 'history'" id="pills-history-tab" data-bs-toggle="pill" data-bs-target="#pills-history" type="button" role="tab" aria-controls="pills-history" aria-selected="false">История <span class="job-tab-count">{{ historyJobsCount }}</span></button>
      </li>
    </ul>
  </div>

  <div class="mt-3">
    <div class="col-md-3 mb-2">
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
  </div>

  <div class="mt-3" v-if="false">
    <button class="btn btn-primary ps-3 pe-3 pt-2 pb-2 me-3 mb-3 no-border-outline all-braches-button" :class="{'active': allBranchesSelected}" type="button" @click="setAllBranches">
      {{ $t('general.allBranches') }}
    </button>
    <div class="btn-group-toggle d-inline-block me-2" data-toggle="buttons" v-for="branche in branches" :key="branche.id">
      <input
        type="checkbox"
        v-model="search.branches"
        :true-value="[]"
        :value="branche.id"
        class="btn-check no-border-outline"
        :id="`btn-check-${branche.id}`"
        autocomplete="off"
      >
      <label class="btn btn-primary ps-3 pe-3 pt-2 pb-2 me-3 mb-3 no-border-outline" :class="{'choosed-filter-button': search.branches.includes(branche.id)}" :for="`btn-check-${branche.id}`">
        {{ branche.title_b }}
      </label>
    </div>
  </div>

  <div class="tab-content">
    <div class="tab-pane" :class="{'active': activeTab == 'unaccepted', 'show': activeTab == 'unaccepted'}" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
      <JobInfo v-for="job in unacceptedJobs" :key="job.id" :job="job" :searchURL="searchURL" :editable="true" :editLink="true" />
    </div>
    <div class="tab-pane" :class="{'active': activeTab == 'active', 'show': activeTab == 'active'}" id="pills-active" role="tabpanel" aria-labelledby="pills-active-tab">
      <JobInfo v-for="job in activeJobs" :key="job.id" :job="job" :searchURL="searchURL" :editable="true" :editLink="false" />
    </div>
    <div class="tab-pane" :class="{'active': activeTab == 'inActions', 'show': activeTab == 'inActions'}" id="pills-current" role="tabpanel" aria-labelledby="pills-current-tab">
      <JobInfo v-for="job in inActionJobs" :key="job.id" :job="job" :searchURL="searchURL" :editable="true" :editLink="false" />
    </div>
    <div class="tab-pane" :class="{'active': activeTab == 'passive', 'show': activeTab == 'passive'}" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">
      <JobInfo v-for="job in passiveJobs" :key="job.id" :job="job" :searchURL="searchURL" :editable="true" :editLink="true" />
    </div>
    <div class="tab-pane" :class="{'active': activeTab == 'history', 'show': activeTab == 'history'}" id="pills-history" role="tabpanel" aria-labelledby="pills-history-tab">
      <JobInfo v-for="job in historyJobs" :key="job.id" :job="job" :searchURL="searchURL" :editable="true" :editLink="false" />
    </div>
  </div>
</template>

<script setup>
import Layout from '@/Layouts/Admin/AppLayout.vue'
import AddJob from '@/Pages/Company/Modals/AddJob.vue'
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import JobInfo from '@/Components/JobInfo.vue'

defineOptions({ layout: Layout })

const urlParams = new URLSearchParams(window.location.search)

const props = defineProps({
  title: String,
  branches: Object,
  establishments: Object,
  currencies: Object,

  unacceptedJobs: Object,
  activeJobs: Object,
  inActionJobs: Object,
  passiveJobs: Object,
  historyJobs: Object,

  unacceptedJobsCount: Number,
  activeJobsCount: Number,
  inActionJobsCount: Number,
  passiveJobsCount: Number,
  historyJobsCount: Number,
})

const allBranchesSelected = ref(true)
const searchURL = ref(window.location.search)
const activeTab = ref(urlParams.get('tab') || 'active')
let selectedBranches = []

if([...urlParams.getAll('branches[]')].length > 0) {
  for(let brancheID in [...urlParams.getAll('branches[]')]) {
    selectedBranches.push(+[...urlParams.getAll('branches[]')][brancheID])
  }
  allBranchesSelected.value = false
}

const search = ref({
  activeTab: activeTab,
  branches: selectedBranches,
})

const openAddJobPopup = ref(false)
const selectedJob = ref({})

const editJob = (data) => {
  openAddJobPopup.value = true
  selectedJob.value = data
}

const openBlockIds = ref([])
const openBlock = (id) => {
  if(openBlockIds.value.includes(id)){
    openBlockIds.value.splice(openBlockIds.value.indexOf(id), 1);
    return;
  }
  openBlockIds.value.push(id);
}

const setAllBranches = () => {
  search.value.branches = []
}

watch(search.value, (currentValue, oldValue) => {
  router.get(route('company_jobs'), {
    search: true,
    branches: currentValue.branches,
    tab: currentValue.activeTab,
  })

  allBranchesSelected.value = currentValue.branches.length > 0 ? false : true
});
</script>
