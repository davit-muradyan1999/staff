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

  <SetCheckListStatus
    v-if="openSetCheckListStatusPopup"
    :job="selectedUpdatedJob"
    :jobs="selectedUpdatedJobs"
    :type="selectedStatusType"
    :all="selectedAll"
    :checkeds="checkAllCheckboxes"
    @close="openSetCheckListStatusPopup = false"
  />

  <div class="row">

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
    <div class="col-md-4 mb-3" v-if="usePermissionM([1,3,4])">
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
  </div>

  <div class="mt-3">
    <div class="table-responsive table-list-medium">
      <div class="card mt-3" v-for="(bonus, key) in histories.data" :key="key">
        <div class="card-body p-4 position-relative ">
          <div class="row bg-white">
            <div class="col border-end">
              {{ moment(bonus?.created_at).format("YYYY-MM-DD") }}
            </div>
            <div class="col border-end">
              <a class="text-decoration-none" target="_blank" :href="route('profile', { id: bonus?.employee?.id })">
                {{ bonus?.employee?.name }}
              </a>
            </div>
            <div class="col border-end">
              {{ bonus?.job?.branche?.title_b }}
            </div>
            <div class="col border-end">
              <a class="text-decoration-none" target="_blank" :href="route('company_jobs_show_list', bonus.job_id)">
                {{ bonus?.job?.establishment?.name_e }} <span class="job-id">#{{ bonus.job_id }}</span>
              </a>
            </div>
            <div class="col border-end">
              {{ bonus?.amount }} {{ $t('general.rub') }}
            </div>
            <div class="col">
              <span :class="bonus?.status == 'CONFIRMED' ? 'text-success' : 'text-danger'">{{ bonus?.status == 'CONFIRMED' ? $t('general.confirmed') : $t('general.canceled') }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="paginate-block-end mt-3">
    <Pagination :links="bonusHistories?.links" />
  </div>
</template>

<script setup>
import Layout from '@/Layouts/Personal/AppLayout.vue'
import { ref, watch } from 'vue'
import AddShifReason from './Modals/AddShifReason.vue'
import SetCheckListStatus from './Modals/SetCheckListStatus.vue'
import { router } from '@inertiajs/vue3'
import moment from 'moment'
import { DatePicker } from 'v-calendar'
import { useBonusStatuses } from '@/Hooks/useBonusStatuses'
import { usePermissionM } from '@/Hooks/usePermissionM'

defineOptions({ layout: Layout })

const urlParams = new URLSearchParams(window.location.search)

const bonusStatuses = useBonusStatuses()

const allBranchesSelected = ref(true)

let selectedEstablishments = []
let selectedPositions = []
let selectedBranches = []
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

if([...urlParams.getAll('branches[]')].length > 0) {
  for(let brancheID in [...urlParams.getAll('branches[]')]) {
    selectedBranches.push(+[...urlParams.getAll('branches[]')][brancheID])
  }
  allBranchesSelected.value = false
}

if([...urlParams.getAll('shiftReasons[]')].length > 0) {
  for(let reason in [...urlParams.getAll('shiftReasons[]')]) {
    selectedShiftReasons.push(+[...urlParams.getAll('shiftReasons[]')][reason])
  }
}

const props = defineProps({
  title: String,
  bonusHistories: Object,
  positions: Object,
  branches: Object,
  establishments: Object,
  startedAt: String,
  finishedAt: String,
});

const search = ref({
  text: urlParams.get('text'),
  status: urlParams.get('status'),
  positions: selectedPositions,
  establishments: selectedEstablishments,
  branches: selectedBranches,
  startedAt: urlParams.get('startedAt') || props?.startedAt,
  finishedAt: urlParams.get('finishedAt') || props?.finishedAt,
});

const checkAllCheckboxes = ref([])
const checkedAll = ref(false)

const histories = ref(props.bonusHistories)

const setAllBranches = () => {
  search.value.branches = []
}

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

const openSetCheckListStatusPopup = ref(false)
const selectedStatusType = ref('')
const selectedAll = ref('')
const selectedUpdatedJob = ref({})
const selectedUpdatedJobs = ref({})

watch(search.value, (currentValue, oldValue) => {
  router.get(route('check_list_bonuses'), {
    search: true,
    text: currentValue.text,
    status: currentValue.status,
    positions: currentValue.positions,
    establishments: currentValue.establishments,
    branches: currentValue.branches,
    shiftReasons: currentValue.shiftReasons,
    startedAt: currentValue.startedAt,
    finishedAt: currentValue.finishedAt,
  }, {
    preserveState: true
  })
});

watch(() => props?.bonusHistories, (first, second) => {
  histories.value = first
});

const pickerConfig = {
  type: 'string',
  mask: 'YYYY-MM-DD',
};

const masks = {
  input: 'YYYY-MM-DD',
};

</script>
