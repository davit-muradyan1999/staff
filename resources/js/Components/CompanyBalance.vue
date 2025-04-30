<template>
  <Head :title="title" />

  <div class="card mt-3 total-info">
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          Текущий баланс
          <template v-if="$page.props.auth?.user?.role == 'MODERATOR'">
            <template v-if="usePermissionM([2,4])">
              <span class="h4 ms-1" :class="moderatorBalancelistSum > 0 ? 'text-success' : 'text-danger'">
                {{ moderatorBalancelistSum > 0 ? '+' : '' }}{{ useFixedNumber(moderatorBalancelistSum) }}
              </span>
              <span class="ms-1" :class="cmoderatorBalancelistSum > 0 ? 'text-success' : 'text-danger'">руб</span>
            </template>
          </template>
          <template v-else-if="companyBalance?.balance">
            <span class="h4 ms-1" :class="companyBalance?.balance > 0 ? 'text-success' : 'text-danger'">
              {{ companyBalance?.balance > 0 ? '+' : '' }}{{ useFixedNumber(companyBalance?.balance) }}
            </span>
            <span class="ms-1" :class="companyBalance?.balance > 0 ? 'text-success' : 'text-danger'">руб</span>
          </template>
        </div>
      </div>
    </div>
  </div>

  <div class="row mt-3 mb-1">
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
        v-model="search.type"
        valueProp="value"
        :options="balanceTypes.types"
        :searchable="true"
        label="name"
        track-by="name"
        :placeholder="`${$t('general.type')}`"
      />
    </div>

    <div class="col-md-4">
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

  <div class="table-responsive table-list-medium">
    <h3 v-if="$page.props.auth?.user?.role == 'ADMIN'" class="text-primary">{{ company?.company_name }}</h3>
    <div class="card mt-3" v-for="list in companyBalancelists" :key="list.id">
      <div class="card-body p-4 position-relative ">
        <div class="row bg-white">
          <div class="col border-end">
            {{ moment(list?.job_check_list?.created_at).format("YYYY-MM-DD") }} {{ moment(list?.job_check_list?.created_at).format("HH:mm") }}
          </div>
          <div class="col border-end">
            <a class="text-decoration-none" target="_blank" :href="route('profile', { id: list?.employee?.id })">
              {{ list?.employee?.name }}
            </a>
          </div>
          <div class="col border-end">
            {{ list?.job?.branche?.title_b }}
          </div>
          <div class="col border-end">
            <a
              v-if="list?.job?.id"
              class="text-decoration-none"
              target="_blank"
              :href="$page.props.auth?.user?.role == 'COMPANY' ? route('company_jobs_show_list', list.job.id) : route('admin.show_job_list', list.job.id)"
            >
              {{ list.job?.establishment?.name_e }} <span class="job-id">#{{ list.job.id }}</span>
            </a>
          </div>
          <div class="col border-end">
            <template v-if="list.type == 'SHIFT'">
              Смена {{ moment(list?.job_check_list?.started_at).format("YYYY-MM-DD") }}
              <span :class="list?.job_check_list?.job_time_graphic?.started_at != list?.job_check_list?.started_at ? 'text-danger' : ''">{{ moment(list?.job_check_list?.started_at).format("HH:mm") }}</span> -
              <span :class="list?.job_check_list?.job_time_graphic?.finished_at != list?.job_check_list?.finished_at ? 'text-danger' : ''">{{ moment(list?.job_check_list?.finished_at).format("HH:mm") }}</span>
            </template>
            <template v-if="list.type == 'BONUS'">
              Бонус
            </template>
          </div>
          <div class="col">
            <span class="float-start" :class="list?.action == 'PLUS' ? 'text-success' : 'text-danger'">
              <span>{{ list?.action == 'PLUS' ? '+' : '' }}</span>
              {{ useFixedNumber(list?.amount) }} {{ $t('general.rub') }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import Layout from '@/Layouts/Personal/AppLayout.vue'
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import moment from 'moment'
import { DatePicker } from 'v-calendar'
import { useBalanceTypes } from '@/Hooks/useBalanceTypes'
import { useFixedNumber } from '@/Hooks/useFixedNumber'
import { usePermissionM } from '@/Hooks/usePermissionM'

defineOptions({ layout: Layout })

const props = defineProps({
  title: String,
  companyBalance: Object,
  companyBalancelists: Object,
  establishments: Object,
  branches: Object,
  startedAt: String,
  finishedAt: String,
  company: Object,
  moderatorBalancelistSum: Number,
});

const balanceTypes = useBalanceTypes()

const urlParams = new URLSearchParams(window.location.search)

let selectedBranches = []
let selectedEstablishments = []
let selectedPositions = []
let selectedCompanies = []
let selectedShiftReasons = []

if([...urlParams.getAll('establishments[]')].length > 0) {
  for(let id in [...urlParams.getAll('establishments[]')]) {
    selectedEstablishments.push([...urlParams.getAll('establishments[]')][id])
  }
}

if([...urlParams.getAll('branches[]')].length > 0) {
  for(let id in [...urlParams.getAll('branches[]')]) {
    selectedBranches.push([...urlParams.getAll('branches[]')][id])
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
  // allCompaniesSelected.value = false
}

if([...urlParams.getAll('shiftReasons[]')].length > 0) {
  for(let reason in [...urlParams.getAll('shiftReasons[]')]) {
    selectedShiftReasons.push(+[...urlParams.getAll('shiftReasons[]')][reason])
  }
}

const search = ref({
  text: urlParams.get('text'),
  companyId: urlParams.get('companyId'),
  status: urlParams.get('status'),
  type: urlParams.get('type'),
  positions: selectedPositions,
  establishments: selectedEstablishments,
  branches: selectedBranches,
  companies: selectedCompanies,
  shiftReasons: selectedShiftReasons,
  startedAt: urlParams.get('startedAt') || props?.startedAt,
  finishedAt: urlParams.get('finishedAt') || props?.finishedAt,
});

const openBlockIds = ref([])

const openBlock = (id) => {
  if(openBlockIds.value.includes(id)){
      openBlockIds.value.splice(openBlockIds.value.indexOf(id), 1);
      return;
  }
  openBlockIds.value.push(id);
}

watch(search.value, (currentValue, oldValue) => {
  router.get(route('balance_info'), {
    search: true,
    text: currentValue.text,
    companyId: currentValue.companyId,
    type: currentValue.type,
    // positions: currentValue.positions,
    establishments: currentValue.establishments,
    branches: currentValue.branches,
    // companies: currentValue.companies,
    // shiftReasons: currentValue.shiftReasons,
    startedAt: currentValue.startedAt,
    finishedAt: currentValue.finishedAt,
  }, {
    preserveState: true
  })
});

const pickerConfig = {
  type: 'string',
  mask: 'YYYY-MM-DD',
}

const masks = {
  input: 'YYYY-MM-DD',
}

const getTimeFromMins = (totalMinutes) => {
  if(!totalMinutes) return false

  const minutes = totalMinutes % 60;
  const hours = Math.floor(totalMinutes / 60);

  return `${padTo2Digits(hours)}:${padTo2Digits(minutes)}`;
}

function padTo2Digits(num) {
  return num.toString().padStart(2, '0');
}
</script>
