<template>
  <Head :title="title" />

  <div class="card total-info">
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          Отработанные часы (за все время)
          <span class="h4" v-if="employeeBalance?.time">
            {{ useMinuteToHours(Math.round(employeeBalance?.time)) }}
          </span> ч
        </div>
        <div class="col-md-6">
          Текущий баланс
          <template v-if="employeeBalance?.balance">
            <span class="h4 ms-1" :class="employeeBalance?.balance > 0 ? 'text-success' : 'text-danger'">
              {{ employeeBalance?.balance > 0 ? '+' : '' }}{{ useFixedNumber(employeeBalance?.balance) }}
            </span>
            <span class="ms-1" :class="employeeBalance?.balance > 0 ? 'text-success' : 'text-danger'">руб</span>
          </template>
        </div>
      </div>
    </div>
  </div>

  <div class="row mt-3 mb-3">
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

  <div class="table-responsive table-list">
    <div class="card mt-3" v-for="list in employeeBalancelists" :key="list.id">
      <div class="card-body p-4 position-relative testimonial-group">
        <div class="row bg-white">
          <div class="col border-end">
            {{ moment(list?.job_check_list?.created_at).format("YYYY-MM-DD") }} {{ moment(list?.job_check_list?.created_at).format("HH:mm") }}
          </div>
          <div class="col border-end">
            <a class="text-decoration-none" target="_blank" :href="route('company_profile', { id: list?.company?.id })">
              {{ list?.company?.company_name }}
            </a>
          </div>
          <div class="col border-end">
            {{ list?.job?.branche?.title_b || list?.job?.establishment?.company?.company_name }}
          </div>
          <div class="col border-end">
            <a class="text-decoration-none" target="_blank" :href="route('job_show_list', list.job.id)">
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
import { useMinuteToHours } from '@/Hooks/useMinuteToHours'
import { useFixedNumber } from '@/Hooks/useFixedNumber'
import { usePage } from '@inertiajs/vue3'

defineOptions({ layout: Layout })

const props = defineProps({
  title: String,
  totalJobs: Object,
  acceptedJobs: Object,
  startedAt: String,
  finishedAt: String,
  filterShiftReasons: Object,
  companies: Object,
  positions: Object,
  shiftReasons: Object,
  employeeBalance: Object,
  employeeBalancelists: Object,
  employee: Object,
});

const balanceTypes = useBalanceTypes()

const urlParams = new URLSearchParams(window.location.search)

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
}

if([...urlParams.getAll('shiftReasons[]')].length > 0) {
  for(let reason in [...urlParams.getAll('shiftReasons[]')]) {
    selectedShiftReasons.push(+[...urlParams.getAll('shiftReasons[]')][reason])
  }
}

const search = ref({
  text: urlParams.get('text'),
  status: urlParams.get('status'),
  type: urlParams.get('type'),
  positions: selectedPositions,
  establishments: selectedEstablishments,
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
  router.get(route('balance'), {
    search: true,
    text: currentValue.text,
    type: currentValue.type,
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

const pickerConfig = {
  type: 'string',
  mask: 'YYYY-MM-DD',
}

const masks = {
  input: 'YYYY-MM-DD',
}
</script>
