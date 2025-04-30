<template>
  <Head :title="title" />
  <div class="d-flex justify-content-center">
    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
      <li class="nav-item" role="presentation">
        <button :class="{'active': activeTab == 'active'}" @click="activeTab = 'active'" class="nav-link" id="active-tab" data-bs-toggle="pill" data-bs-target="#active" type="button" role="tab" aria-controls="active" aria-selected="true">{{ $t("general.waitingEmployees") }} {{ activeJobsCount }}</button>
      </li>
      <li class="nav-item" role="presentation">
        <button :class="{'active': activeTab == 'current'}" @click="activeTab = 'current'" class="nav-link" id="current-tab" data-bs-toggle="pill" data-bs-target="#current" type="button" role="tab" aria-controls="current" aria-selected="true">{{ $t("general.inActionEmployees") }} {{ inActionJobsCount }}</button>
      </li>
      <li class="nav-item" role="presentation">
        <button :class="{'active': activeTab == 'black-list'}" @click="activeTab = 'black-list'" class="nav-link" id="black-list-tab" data-bs-toggle="pill" data-bs-target="#black-list" type="button" role="tab" aria-controls="black-list" aria-selected="true">{{ $t("general.blackList") }} {{ blackListsCount }}</button>
      </li>
      <li class="nav-item" role="presentation">
        <button :class="{'active': activeTab == 'history'}" @click="activeTab = 'history'" class="nav-link" id="history-tab" data-bs-toggle="pill" data-bs-target="#history" type="button" role="tab" aria-controls="history" aria-selected="false">История {{ historyJobsCount }}</button>
      </li>
    </ul>
  </div>

  <div class="row mt-3">
    <div class="col-md-3 mb-3">
      <div class="input-group">
        <input v-model="search.text" class="form-control border-end-0 border search-moderator-input" type="text" id="example-search-input">
        <button class="btn btn-outline-secondary bg-white border-start-0 border-bottom-0 border ms-n5 search-moderator-button" type="button">
          <font-awesome-icon icon="magnifying-glass" />
        </button>
      </div>
    </div>

    <div class="col-md-3 mb-2" v-if="activeTab != 'black-list' && usePermissionM([1,3,4])">
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

  <div class="tab-content">
    <div :class="{'active': activeTab == 'active', 'show': activeTab == 'active'}" class="tab-pane fade show" id="active" role="tabpanel" aria-labelledby="active-tab">
      <EmployeeList v-for="job in activeJobs" :key="job.id" :job="job" type="active" />
    </div>
    <div :class="{'active': activeTab == 'current', 'show': activeTab == 'current'}" class="tab-pane fade" id="current" role="tabpanel" aria-labelledby="current-tab">
      <EmployeeList v-for="job in inActionJobs" :key="job.id" :job="job" type="current" />
    </div>
    <div :class="{'active': activeTab == 'black-list', 'show': activeTab == 'black-list'}" class="tab-pane fade" id="black-list" role="tabpanel" aria-labelledby="black-list-tab">
      <BlackList v-for="employee in blackLists" :key="employee.id" :employee="employee" />
    </div>
    <div :class="{'active': activeTab == 'history', 'show': activeTab == 'history'}" class="tab-pane fade" id="history" role="tabpanel" aria-labelledby="history-tab">
      <EmployeeList v-for="job in historyJobs" :key="job.id" :job="job" type="history" />
    </div>
  </div>
</template>

<script setup>
import Layout from '@/Layouts/Personal/AppLayout.vue'
import { ref, watch } from 'vue'
import EmployeeList from '@/Components/EmployeeList.vue'
import BlackList from '@/Components/BlackList.vue'
import { router } from '@inertiajs/vue3'
import { usePermissionM } from '@/Hooks/usePermissionM'

defineOptions({ layout: Layout })

const urlParams = new URLSearchParams(window.location.search)

const props = defineProps({
  title: String,
  branches: Object,
  activeJobs: Object,
  inActionJobs: Object,
  historyJobs: Object,
  blackLists: Object,
  activeJobsCount: Number,
  inActionJobsCount: Number,
  historyJobsCount: Number,
  blackListsCount: Number,
})

let selectedBranches = []

if([...urlParams.getAll('branches[]')].length > 0) {
  for(let brancheID in [...urlParams.getAll('branches[]')]) {
    selectedBranches.push(+[...urlParams.getAll('branches[]')][brancheID])
  }
}

const activeTab = ref(urlParams.get('tab') || 'active')

const search = ref({
  text: urlParams.get('text'),
  branches: selectedBranches,
  activeTab: activeTab,
})

watch(search.value, (currentValue, oldValue) => {
  router.get(route('employees'), {
    search: true,
    text: currentValue.text,
    branches: currentValue.branches,
    tab: currentValue.activeTab,
  }, {
    preserveState: true
  })
});
</script>
