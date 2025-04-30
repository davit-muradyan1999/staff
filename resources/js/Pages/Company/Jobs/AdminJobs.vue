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
  <MarkerJob
    v-if="openMarkerPopup"
    :job="selectedMarkerJob"
    :editable="true"
    @close="openMarkerPopup = false"
  />
  <div class="mt-3 text-end" v-if="usePermissionA([8])">
    <Link class="edit-action-link d-inline float-end add-job" :href="route('company_jobs_add') + searchURL">
      <button class="btn btn-success ps-2 pe-2 pt-2 pb-2" type="button">
        {{ $t('general.addJob') }}
      </button>
    </Link>
  </div>
  <div class="d-flex justify-content-center list-tabs">
    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link" :class="{'active': activeTab == 'inActions'}" @click="activeTab = 'inActions'" id="pills-current-tab" data-bs-toggle="pill" data-bs-target="#pills-current" type="button" role="tab" aria-controls="pills-current" aria-selected="true">{{ $t('general.inActions') }} <span class="job-tab-count">{{ inActionJobsCount }}</span></button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" :class="{'active': activeTab == 'active'}" @click="activeTab = 'active'" id="pills-active-tab" data-bs-toggle="pill" data-bs-target="#pills-active" type="button" role="tab" aria-controls="pills-active" aria-selected="true">{{ $t('general.adopted') }} <span class="job-tab-count">{{ activeJobsCount }}</span></button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" :class="{'active': activeTab == 'unaccepted'}" @click="activeTab = 'unaccepted'" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-home" type="button" role="tab" aria-controls="pills-home" aria-selected="true">{{ $t('general.unaccepted') }} <span class="job-tab-count">{{ unacceptedJobsCount }}</span></button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" :class="{'active': activeTab == 'passive'}"  @click="activeTab = 'passive'" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-profile" type="button" role="tab" aria-controls="pills-profile" aria-selected="false">Пассивные <span class="job-tab-count">{{ passiveJobsCount }}</span></button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" :class="{'active': activeTab == 'history'}"  @click="activeTab = 'history'" id="pills-history-tab" data-bs-toggle="pill" data-bs-target="#pills-history" type="button" role="tab" aria-controls="pills-history" aria-selected="false">История <span class="job-tab-count">{{ historyJobsCount }}</span></button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" :class="{'active': activeTab == 'map'}"  @click="activeTab = 'map'" id="pills-map-tab" data-bs-toggle="pill" data-bs-target="#pills-map" type="button" role="tab" aria-controls="pills-map" aria-selected="false">Карта <span class="job-tab-count"></span></button>
      </li>
    </ul>
  </div>

  <div class="row mt-3" style="z-index: 1020 !important;  position: relative;">
    <div class="col-md-4 mb-3">
      <div class="input-group">
        <input v-model="search.text" class="form-control border-end-0 border search-moderator-input" type="text" id="example-search-input">
        <button class="btn btn-outline-secondary bg-white border-start-0 border-bottom-0 border ms-n5 search-moderator-button" type="button">
          <font-awesome-icon icon="magnifying-glass" />
        </button>
      </div>
    </div>

    <div class="col-md-4 mb-3" v-if="$page.props.auth?.user?.role == 'ADMIN' || usePermissionA([8])">
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

    <div class="col-md-4 mb-2">
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

    <div class="col-md-4 mb-2">
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
    <div class="tab-pane" :class="{'active': activeTab == 'inActions', 'show': activeTab == 'inActions'}" id="pills-current" role="tabpanel" aria-labelledby="pills-current-tab">
      <JobInfo v-for="job in inActionJobs" :key="job.id" :job="job" :searchURL="searchURL" :editable="true" :editLink="true" />
    </div>
    <div class="tab-pane" :class="{'active': activeTab == 'active', 'show': activeTab == 'active'}" id="pills-active" role="tabpanel" aria-labelledby="pills-active-tab">
      <JobInfo v-for="job in activeJobs" :key="job.id" :job="job" :searchURL="searchURL" :editable="true" :editLink="true" />
    </div>
    <div class="tab-pane" :class="{'active': activeTab == 'unaccepted', 'show': activeTab == 'unaccepted'}" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
      <JobInfo v-for="job in unacceptedJobs" :key="job.id" :job="job" :searchURL="searchURL" :editable="true" :editLink="true" />
    </div>
    <div class="tab-pane" :class="{'active': activeTab == 'passive', 'show': activeTab == 'passive'}" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">
      <JobInfo v-for="job in passiveJobs" :key="job.id" :job="job" :searchURL="searchURL" :editable="true" :editLink="true" />
    </div>
    <div class="tab-pane" :class="{'active': activeTab == 'history', 'show': activeTab == 'history'}" id="pills-history" role="tabpanel" aria-labelledby="pills-history-tab">
      <JobInfo v-for="job in historyJobs" :key="job.id" :job="job" :searchURL="searchURL" :editable="true" :editLink="true" />
    </div>
    <div class="tab-pane" :class="{'active': activeTab == 'map', 'show': activeTab == 'map'}" id="pills-map" role="tabpanel" aria-labelledby="pills-map-tab">
      <div style="height: 700px; width: 100%;" class="job-map" v-if="activeTab == 'map'">
        <l-map ref="map" @ready="onLeafletReady" :zoom="mapConfig.zoom" :center="mapCenter" :minZoom="1" :maxZoom="18" :zoomAnimation="true" no-blocking-animations>
          <l-tile-layer
            v-for="layer in mapConfig.layers"
            :key="layer.name"
            :url="layer.url"
            :name="layer.name"
            :visible="layer.visible"
            :attribution="layer.attribution"
            layer-type="base"
          ></l-tile-layer>
          <l-control-layers />
          <template v-if="true">
            <marker-cluster :options="{ showCoverageOnHover: false, chunkedLoading: true }">
              <template v-for="marker in markers" :key="marker.id">
                <l-marker :lat-lng="[marker?.lat, marker?.lng]" @click="openMarkerJobPopup(marker)">
                  <l-icon
                    :icon-url="marker?.type == 'inAction' ? '/images/marker-icon-green.png' : '/images/marker-icon-blue.png'"
                  />
                </l-marker>
              </template>
            </marker-cluster>
          </template>
        </l-map>
      </div>
    </div>
  </div>
</template>

<script setup>
import AdminLayout from '@/Layouts/Admin/AppLayout.vue'
import AddJob from '../Modals/AddJob.vue'
import { ref, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import JobInfo from '@/Components/JobInfo.vue'
import { useLeafletConfig } from '@/Hooks/useLeafletConfig'
import MarkerJob from '@/Pages/Personal/Modals/MarkerJob.vue'
import MarkerCluster from "@/Components/MarkerCluster.vue"
import { usePermissionA } from '@/Hooks/usePermissionA'

defineOptions({ layout: AdminLayout })

const urlParams = new URLSearchParams(window.location.search)

const props = defineProps({
  title: String,
  companies: Object,
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

const unacceptedJobs = ref(props.unacceptedJobs)
const activeJobs = ref(props.activeJobs)
const inActionJobs = ref(props.inActionJobs)

const map = ref(null)
const markers = ref([])

setMarkers(props.unacceptedJobs, 'unaccepted')
setMarkers(props.activeJobs, 'active')
setMarkers(props.inActionJobs, 'inAction')

const allBranchesSelected = ref(true)
const searchURL = ref(window.location.search)
const activeTab = ref(urlParams.get('tab') || 'inActions')
const mapConfig = useLeafletConfig()
const openMarkerPopup = ref(false)
const selectedMarkerJob = ref({})

const mapCenter = [55.938817691492545, 37.79296875000001]

let selectedBranches = []
let selectedEstablishments = []

if([...urlParams.getAll('branches[]')].length > 0) {
  for(let brancheID in [...urlParams.getAll('branches[]')]) {
    selectedBranches.push(+[...urlParams.getAll('branches[]')][brancheID])
  }
  allBranchesSelected.value = false
}

if([...urlParams.getAll('establishments[]')].length > 0) {
  for(let id in [...urlParams.getAll('establishments[]')]) {
    selectedEstablishments.push([...urlParams.getAll('establishments[]')][id])
  }
}


const search = ref({
  text: urlParams.get('text'),
  activeTab: activeTab,
  company_id: urlParams.get('company_id'),
  branches: selectedBranches,
  establishments: selectedEstablishments,
})

const leafletReady = ref(false)
const leafletObject = ref(null)

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
  markers.value = []
  router.get(route('company_jobs'), {
    search: true,
    text: currentValue.text,
    company_id: currentValue.company_id,
    branches: currentValue.branches,
    tab: currentValue.activeTab,
    establishments: currentValue.establishments,
  }, {
    preserveState: true
  })

  allBranchesSelected.value = currentValue.branches.length > 0 ? false : true
});

watch(() => props?.unacceptedJobs, (first, second) => {
  setMarkers(first, 'unaccepted')
});

watch(() => props?.activeJobs, (first, second) => {
  setMarkers(first, 'active')
});

watch(() => props?.inActionJobs, (first, second) => {
  setMarkers(first, 'inAction')
});

function setMarkers(data, type) {
  if(data.length > 0) {
    for(let i in data) {
      if(data[i]?.branche?.lat && data[i]?.branche?.lng) {
        markers.value.push({
          id: data[i].id,
          lat: data[i].branche.lat,
          lng: data[i].branche.lng,
          title: data[i].branche.title_b,
          data: data[i],
          type: type
        })
      }
    }
  }
}

const changeCompany = (e) => {
  search.value.branches = []
}

const openMarkerJobPopup = (marker) => {
  openMarkerPopup.value = true
  selectedMarkerJob.value = marker?.data
}

const onLeafletReady = async () => {
  // await nextTick();
  leafletObject.value = map.value.leafletObject
  leafletReady.value = true;
}

const sidebarToggle = document.body.querySelector('#sidebarToggle');
if (sidebarToggle) {
  sidebarToggle.addEventListener('click', event => {
    event.preventDefault();
    setMap();
  });
}

const setMap = () => {
  setTimeout(function() { window.dispatchEvent(new Event('resize')) }, 150);
}

</script>
