<template>
  <Head :title="title" />

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

    <div class="col-md-4 ">
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

    <div class="col-md-4" v-if="false">
      <div class="row">
        <div class="col-md-6">
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

  <div class="card">
    <div class="card-body">
      <div class="row bg-white">
        <div class="col-md-4">
          Текущий баланс
          <template v-if="amountStatus">
            <span class="h4 ms-1 text-warning">
              +{{ +totalAmountSum }} {{ $t('general.rub') }}
            </span>
          </template>
          <template v-else>
            <template v-if="totalAmount">
              <span class="h4 ms-1" :class="totalAmount > 0 ? 'text-success' : 'text-danger'">
                {{ totalAmount > 0 ? '+' : '' }}{{ useFixedNumber(totalAmount) }}
              </span>
              <span class="ms-1" :class="totalAmount > 0 ? 'text-success' : 'text-danger'">руб</span>
            </template>
          </template>

        </div>
        <div class="col-md-2">
          <a class="text-decoration-none" target="_blank" :href="route('profile', { id: employee?.id })">
            {{ employee?.name }}
          </a>
          <span class="edit-action-link ms-3 cursor-pointer text-primary" @click="backRedirect()">
            <font-awesome-icon icon="arrow-left" />
          </span>
        </div>
      </div>
    </div>
  </div>

  <div class="mt-4" v-if="usePermissionA([8])">
    <input
      class="form-check-input ms-4"
      type="checkbox"
      :true-value="1"
      :false-value="0"
      :checked="checkedAll"
      @change="checkAll"
    >

    <button v-if="checkAllCheckboxes.length > 0" type="button" class="btn-sm btn-primary mb-2 ms-4 border-0" @click="rememberIds">{{ $t('general.remember') }}</button>
    <button v-if="amountStatus" type="button" class="btn-sm btn-danger mb-2 ms-4 border-0" @click="forgetIds">{{ $t('general.reset') }}</button>
  </div>

  <div class="table-responsive table-list">
    <div class="card mt-3" v-for="list in employeeBalancelists" :key="list.id">
      <div class="card-body p-4 position-relative ">
        <div class="row bg-white">
          <div class="col-1 border-end" v-if="usePermissionA([8])">
            <input
              class="form-check-input"
              type="checkbox"
              :value="list.id"
              :true-value="list.id"
              :false-value="0"
              :checked="checkAllCheckboxes.some(elem => elem == (list.id))"
              @change="checkOne($event, `${list.id}`)"
            >
          </div>
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
            <a class="text-decoration-none" target="_blank" :href="route('admin.show_job_list', list.job.id)">
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
import Layout from '@/Layouts/Admin/AppLayout.vue'
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import moment from 'moment'
import { DatePicker } from 'v-calendar'
import { useBalanceTypes } from '@/Hooks/useBalanceTypes'
import { useFixedNumber } from '@/Hooks/useFixedNumber'
import { usePermissionA } from '@/Hooks/usePermissionA'

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
  totalAmount: Number
});

const balanceTypes = useBalanceTypes()

const checkAllCheckboxes = ref([])
const allCheckboxes = ref([])
const checkedAll = ref(false)

if(props?.employeeBalancelists) {
  for(let i in props?.employeeBalancelists) {
    allCheckboxes.value.push(props?.employeeBalancelists[i].id)
  }
}

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
  employeeId: urlParams.get('employeeId') || props?.employeeId,
  isPaid: urlParams.get('isPaid'),
});

const openBlockIds = ref([])

const openBlock = (id) => {
  if(openBlockIds.value.includes(id)){
      openBlockIds.value.splice(openBlockIds.value.indexOf(id), 1);
      return;
  }
  openBlockIds.value.push(id);
}

const employeeBalancelists = ref(props.employeeBalancelists)

const totalAmountSum = ref(0)
const amountStatus = ref(false)

const setTotal = (color = true) => {
  let totalSum = 0;
  props?.employeeBalancelists.forEach(obj => {
    if(checkAllCheckboxes.value.includes(obj.id)) {
      totalSum += +obj.amount;
    }
  })

  if(color)
    amountStatus.value = true

  totalAmountSum.value = useFixedNumber(totalSum)
}

const checkOne = (e, val) => {
  if(e.target.checked) {
    checkAllCheckboxes.value.push(+val)
  } else {
    checkAllCheckboxes.value = checkAllCheckboxes.value.filter(function(value, index, arr) {
      return value != val
    })
  }

  checkedAll.value = employeeBalancelists.value.length == checkAllCheckboxes.value.length ? true : false
  setTotal()
}

const checkAll = (e, all = false, setYellow = true) => {
  checkAllCheckboxes.value = []
  if(e?.target?.checked || all) {
    employeeBalancelists.value.filter(function (obj) {
      checkAllCheckboxes.value.push(+obj.id)
    })
    checkedAll.value = true
  } else {
    checkAllCheckboxes.value = []
    checkedAll.value = false
  }

  setTotal(setYellow);
}


const checkSetIds = (employeeId) => {
  let getData = localStorage.getItem("notPaids");
  let findData = false
  if(getData) {
    let checkedStorageIds = []

    JSON.parse(getData).forEach(obj => {
      if(obj.employee_id == employeeId) {
        findData = true
        checkedStorageIds.push(obj.id)
      }
    })

    if(findData) {
      checkAllCheckboxes.value = []
      checkAllCheckboxes.value = checkedStorageIds

      if(checkAllCheckboxes.value.length == employeeBalancelists.value.length) {
        checkedAll.value = true
      } else {
        checkedAll.value = false
      }
    }
  }

  if(!findData) {
    checkAll(null, true, false)
  }
}

checkSetIds(urlParams.get('employeeId'))

// localStorage.removeItem("notPaids");



const checkAmount = (employeeId) => {
  let getData = localStorage.getItem("notPaids");
  if(getData) {
    if(JSON.parse(getData).some(elem => elem.employee_id == (employeeId))) {
      amountStatus.value = true
    }
  } else {
    amountStatus.value = false
  }
}

const setAmount = (employeeId, redirect = false) => {
  let getData = localStorage.getItem("notPaids");
  let totalSum = 0;

  if(getData) {
    let isFind = false
    JSON.parse(getData).forEach(obj => {
      if(obj.employee_id == employeeId) {
        isFind = true
        totalSum += +obj.amount;
      }
    })

    if(isFind) {
      totalAmountSum.value = useFixedNumber(totalSum)
      amountStatus.value = true
    }
  }

  if(redirect) {

  }
}

checkAmount(urlParams.get('employeeId'))
setAmount(urlParams.get('employeeId'))

const rememberIds = () => {
  let data = [];
  let getData = localStorage.getItem("notPaids");
  if(getData) {
    data = JSON.parse(getData)
    data = data.filter(function (obj) {
      return obj.employee_id != urlParams.get('employeeId')
    });


    localStorage.setItem('notPaids', JSON.stringify(data))
  }

  for(let i in props?.employeeBalancelists) {
    for(let x in checkAllCheckboxes.value) {
      if(checkAllCheckboxes.value[x] == props?.employeeBalancelists[i].id) {
        data.push({
          id: props?.employeeBalancelists[i].id,
          employee_id: props?.employeeBalancelists[i].employee_id,
          amount: props?.employeeBalancelists[i].amount
        })
      }
    }
  }

  localStorage.setItem('notPaids', JSON.stringify(data))

  checkAmount(urlParams.get('employeeId'))
  setAmount(urlParams.get('employeeId'), true)

  backRedirect()
}

const backRedirect = () => {
  router.get('admin/employee_balance', {
    'search': true,
    'tab': urlParams.get('tab'),
    'text': urlParams.get('listText') && urlParams.get('listText') != 'null' ? urlParams.get('listText') : '',
    'startedAt': urlParams.get('filterStartedAt') && urlParams.get('filterStartedAt') != 'null' ? urlParams.get('filterStartedAt') : '',
    'finishedAt': urlParams.get('filterFinishedAt') && urlParams.get('filterFinishedAt') != 'null' ? urlParams.get('filterFinishedAt') : '',
    'bonus': urlParams.get('bonus') && urlParams.get('bonus') != 'null' ? urlParams.get('bonus') : 0
  })
}

const forgetIds = () => {
  let data = [];
  let getData = localStorage.getItem("notPaids");
  if(getData) {
    data = JSON.parse(getData)
    data = data.filter(function (obj) {
      return obj.employee_id != urlParams.get('employeeId')
    });

    localStorage.setItem('notPaids', JSON.stringify(data))
  }

  totalAmountSum .value= 0
  amountStatus.value = false

  router.get(window.location.href)

  // backRedirect()
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
    employeeId: currentValue.employeeId,
    isPaid: currentValue.isPaid,
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
