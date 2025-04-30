<template>
  <Head :title="title" />

  <AcceptJob
    v-if="openAcceptJobPopup"
    :job="selectedAcceptJob"
    @close="openAcceptJobPopup = false"
  />

  <MarkerJob
    v-if="openMarkerPopup"
    :job="selectedMarkerJob"
    :editable="false"
    @close="openMarkerPopup = false"
  />

  <JobGraphic
    v-if="openGraphicPopup"
    :job="selectedJob"
    @close="openGraphicPopup = false"
  />

  <div class="d-flex justify-content-center">
    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link" :class="{'active': activeTab == 'list'}" @click="activeTab = 'list'" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-home" type="button" role="tab" aria-controls="pills-home" aria-selected="true">Активние вакансии <span class="job-tab-count">{{ activeJobsCount }}</span></button>
      </li>
      <li class="nav-item" role="presentation" @click="setMap">
        <button class="nav-link" :class="{'active': activeTab == 'map'}"  @click="activeTab = 'map'" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-profile" type="button" role="tab" aria-controls="pills-profile" aria-selected="false">{{ $t('general.map') }} <span class="job-tab-count"></span></button>
      </li>
    </ul>
  </div>

  <div class="tab-content">
    <div class="tab-pane fade" :class="{'active': activeTab == 'list', 'show': activeTab == 'list'}" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
      <div class="row mb-3">
        <div class="col-md-3">
          <div class="input-group">
            <Multiselect
              v-if="false"
              v-model="search.establishments"
              valueProp="id"
              :options="establishments"
              :searchable="true"
              mode="tags"
              label="name_e"
              track-by="name_e"
              :placeholder="`${$t('general.establishment')}`"
            />
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
        </div>
        <div class="col-md-3">
          <div class="form-check is-favorite mt-2">
            <input
              class="form-check-input"
              type="checkbox"
              id="is-favorite"
              :true-value="1"
              :false-value="0"
              v-model="search.isFavorite"
            >
            <label class="is-favorite" for="is-favorite">{{ $t('general.favorites') }}</label>
          </div>
        </div>
      </div>

      <JobInfo v-for="job in jobs.data" :key="job.id" :job="job" :editable="false" :searchURL="''" :showFavorite="true" />

      <Pagination :links="jobs?.links" />
    </div>
    <div class="tab-pane fade" :class="{'active': activeTab == 'map', 'show': activeTab == 'map'}" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">
      <div class="row">
        <div class="col-md-3" v-if="false">
          <Multiselect
            v-model="mapFilter.establishments"
            valueProp="id"
            :options="establishments"
            :searchable="true"
            mode="tags"
            label="name_e"
            track-by="name_e"
            :placeholder="`${$t('general.establishment')}`"
          />
        </div>
        <div class="col-md-3" style="z-index: 9999;">
          <Multiselect
            v-model="mapFilter.positions"
            valueProp="id"
            :options="positions"
            :searchable="true"
            mode="tags"
            label="name_p"
            track-by="name_p"
            :placeholder="`${$t('general.positions')}`"
          />
        </div>
        <div class="col-4 col-md-3">
          <div class="form-check is-favorite-map mt-2">
            <input
              class="form-check-input"
              type="checkbox"
              id="is-favorite-map"
              :true-value="1"
              :false-value="0"
              v-model="mapFilter.isFavorite"
            >
            <label class="is-favorite-map" for="is-favorite-map">{{ $t('general.favorites') }}</label>
          </div>
        </div>
        <div class="col-md-12 mt-3">
          <div class="job-map" :style="{height: (windowHeight - 350) + 'px' }">
            <l-map ref="map" @ready="onLeafletReady" :zoom="mapConfig.zoom" :center="[mapConfig.center[0], mapConfig.center[1]]" :minZoom="1" :maxZoom="18" :zoomAnimation="true">
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
              <template v-if="leafletReady">
                <marker-cluster :options="{ showCoverageOnHover: false, chunkedLoading: true }">
                  <template v-for="marker in markers" :key="marker.id">
                    <l-marker :lat-lng="[marker?.lat, marker?.lng]" @click="openMarkerJobPopup(marker)"></l-marker>
                  </template>
                </marker-cluster>
              </template>
            </l-map>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import Layout from '@/Layouts/Personal/AppLayout.vue'
import axios from 'axios'
import { ref, watch, nextTick } from 'vue'
import JobInfo from '@/Components/JobInfo.vue'
import JobGraphic from '@/Components/JobGraphic.vue'
import AcceptJob from '@/Pages/Personal/Modals/AcceptJob.vue'
import MarkerJob from '@/Pages/Personal/Modals/MarkerJob.vue'
import { useLeafletConfig } from '@/Hooks/useLeafletConfig'
import { useGenderText } from '@/Hooks/useGenderText'
import MarkerCluster from "@/Components/MarkerCluster.vue"
import { router } from '@inertiajs/vue3'
import { useFixedNumber } from '@/Hooks/useFixedNumber'
import { useMinuteToHours } from '@/Hooks/useMinuteToHours'

defineOptions({ layout: Layout })

const props = defineProps({
  title: String,
  jobs: Object,
  activeJobsCount: Number,
  establishments: Object,
  positions: Object
})

let url = decodeURIComponent(window.location.search)

const urlParams = new URLSearchParams(url)

const activeTab = ref('list')
const mapConfig = useLeafletConfig()
const map = ref(null)
const markers = ref([])

const leafletReady = ref(false)
const leafletObject = ref(null)

const openGraphicPopup = ref(false)
const selectedJob = ref({})

const openAcceptJobPopup = ref(false)
const selectedAcceptJob = ref({})

const openMarkerPopup = ref(false)
const selectedMarkerJob = ref({})

let selectedEstablishments = []

const windowHeight = ref(window.screen.height);

urlParams.forEach((value, key) => {
  if(key.search("establishments") != -1) {
    selectedEstablishments.push(value)
  }
});

const search = ref({
  establishments: selectedEstablishments,
  isFavorite: false,
  positions: [],
})

if([...urlParams.getAll('positions[]')].length > 0) {
  for(let positionID in [...urlParams.getAll('positions[]')]) {
    search.value.positions.push([...urlParams.getAll('positions[]')][positionID])
  }
}

if(urlParams.get('isFavorite')) {
  search.value.isFavorite = urlParams.get('isFavorite')
}

const mapFilter = ref({
  search: false,
  establishments: [],
  isFavorite: 0,
  positions: [],
})


const onLeafletReady = async () => {
  await nextTick();
  leafletObject.value = map.value.leafletObject
  leafletReady.value = true;
}

const setMap = () => {
  map.value.leafletObject.invalidateSize()
}

const openGraphic = (data) => {
  openGraphicPopup.value = true
  selectedJob.value = data
}

watch(mapFilter.value, (currentValue, oldValue) => {
  axios.get(route('filter_map'), {
    params: {
      data: currentValue
    }
  }
  ).then((response) => {
    markers.value = []
    const data = response.data.data
    if(data.length > 0) {
      for(let i in data) {
        if(data[i]?.branche?.lat && data[i]?.branche?.lng) {
          markers.value.push({
            id: data[i].id,
            lat: data[i].branche.lat,
            lng: data[i].branche.lng,
            title: data[i].branche.title_b,
            data: data[i]
          })
        }
      }
    }

  }).catch(error => {
    errorMsg.value = error.response.data.message
  });
});

mapFilter.value.search = true


const setTotalPerEmployeeForWorker = (job) => {
  const salary = job?.establishment?.salary ? job?.establishment?.salary : 0
  const totalHours = job?.total_minutes ? useFixedNumber(+job?.total_minutes / 60, 4) : 0
  const bonus = job?.bonus ? job?.bonus : 0

  return useFixedNumber((+salary * +totalHours) + +bonus)
}

const acceptJob = (job) => {
  openAcceptJobPopup.value = true
  selectedAcceptJob.value = job
}

const openMarkerJobPopup = (marker) => {
  openMarkerPopup.value = true
  selectedMarkerJob.value = marker?.data
}

watch(search.value, (currentValue, oldValue) => {
  router.get(route('jobs'), {
    search: true,
    establishments: currentValue.establishments,
    positions: currentValue.positions,
    isFavorite: currentValue.isFavorite,
  }, {
    preserveState: true
  })
});
</script>

<style scoped>

</style>
