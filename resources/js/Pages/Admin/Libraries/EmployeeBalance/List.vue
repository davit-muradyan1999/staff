<template>
  <Head :title="title" />

  <ConfirmUnpaidsModal
    v-if="showconfirmUnpaidsModal"
    :listIds="listIds"
    :employeeIds="checkAllCheckboxes"
    :notPaidEmployees="notPaidEmployees"
    @close="showconfirmUnpaidsModal = false"
  />

  <div class="row">
    <div class="d-flex justify-content-center">
      <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link" :class="{'active': activeTab == 'unpaid'}" @click="activeTab = 'unpaid'" id="pills-unpaid-tab" data-bs-toggle="pill" data-bs-target="#pills-unpaid" type="button" role="tab" aria-controls="pills-unpaid" aria-selected="true">{{ $t('general.unpaid') }} <span class="job-tab-count">{{ notPaidEmployeesCount }}</span></button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" :class="{'active': activeTab == 'paidUp'}" @click="activeTab = 'paidUp'" id="pills-paidUp-tab" data-bs-toggle="pill" data-bs-target="#pills-paidUp" type="button" role="tab" aria-controls="pills-paidUp" aria-selected="true">{{ $t('general.paidUp') }} <span class="job-tab-count">{{ transfersCount }}</span></button>
        </li>
      </ul>
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
    <div class="col-md-4 mb-3" v-if="false">
      <Multiselect
        v-model="search.companies"
        valueProp="id"
        :options="companies"
        :searchable="true"
        mode="tags"
        label="company_name"
        track-by="company_name"
        :placeholder="`${$t('general.company')}`"
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

    <div class="col-md-4">
      <label class="flex items-center mt-2">
        <input
          type="checkbox"
          name="remember"
          v-model="search.bonus"
          class="form-check-input me-2"
          :true-value="1"
          :false-value="0"
        />
        <span class="ml-2 text-sm text-gray-600">{{ $t('general.bonus') }}</span>
      </label>
    </div>
  </div>

  <div class="mt-3">
    <div class="tab-content">
      <div class="tab-pane" :class="{'active': activeTab == 'unpaid', 'show': activeTab == 'unpaid'}" id="pills-unpaid" role="tabpanel" aria-labelledby="pills-unpaid-tab">
        <input
          v-if="usePermissionA([8])"
          class="form-check-input ms-4"
          type="checkbox"
          :true-value="1"
          :false-value="0"
          :checked="checkedAll"
          @change="checkAll"
        >
        <template v-if="usePermissionA([8])">
          <div class="float-end" v-if="checkAllCheckboxes.length > 0">
            <button type="button" class="btn-sm btn-success mb-2 border-0" @click="confirmUnpaids">{{ $t('general.pay') }}</button>
          </div>
        </template>
        <div class="table-responsive table-list">
          <div class="card mt-3" v-for="(employee, key) in notPaidEmployees.data" :key="key">
            {{ employee.started_at }}
            <div class="card-body p-4 position-relative testimonial-group">
              <div class="row">
                <div class="col-1 border-end" v-if="usePermissionA([8])">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    :value="employee.id"
                    :true-value="employee.id"
                    :false-value="0"
                    :checked="checkAllCheckboxes.some(elem => elem == (employee.id))"
                    @change="checkOne($event, `${employee.id}`)"
                  >
                </div>
                <div class="col border-end">
                  <Link :href="route('balance') + `?search=true&employeeId=${employee?.id}&tab&=${activeTab}&listText=${search.text}&isPaid=0&startedAt=${setDate(employee.o_started_at)}&finishedAt=${setDate(employee.l_finished_at)}&filterStartedAt=${setDate(search.startedAt)}&filterFinishedAt=${setDate(search.finishedAt)}&bonus=${search.bonus}`">{{ employee?.name }}</Link>
                </div>
                <div class="col border-end">
                  {{ employee.o_started_at ? moment(employee.o_started_at).format("YYYY-MM-DD HH:mm:ss") : '' }}
                </div>
                <div class="col border-end">
                  {{ employee.l_finished_at ? moment(employee.l_finished_at).format("YYYY-MM-DD HH:mm:ss") : '' }}
                </div>
                <div class="col">
                  <span class="text-success" :class="checkAmount(employee?.id) ? 'text-warning' : 'text-success'">
                    {{ setAmount(employee?.id, employee?.not_paid_sum) }} {{ $t('general.rub') }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="tab-pane" :class="{'active': activeTab == 'paidUp', 'show': activeTab == 'paidUp'}" id="pills-paidUp" role="tabpanel" aria-labelledby="pills-paidUp-tab">
        <div class="table-responsive table-list">
          <div class="card mt-3" v-for="(transfer, key) in transfers.data" :key="transfer.id">
            <div class="card-body p-4 position-relative table-list testimonial-group">
              <div class="row">
                <div class="col-1 border-end">
                  {{ moment(transfer?.created_at).format("YYYY-MM-DD HH:mm:ss") }}
                </div>
                <div class="col border-end">
                  {{ transfer?.paying_user?.company_name || transfer?.paying_user?.name }}
                </div>
                <div class="col border-end">
                  <a class="text-decoration-none" target="_blank" :href="route('profile', { id: transfer?.paid_employee?.id })">
                  {{ transfer?.paid_employee?.name }}
                </a>
                </div>
                <div class="col border-end">
                  {{ transfer?.note }}
                </div>
                <div class="col">
                  <span class="text-success">{{ transfer?.amount }} {{ $t('general.rub') }}</span>
                </div>
              </div>
              <div class="collapse multi-collapse" :id="`multiCollapseExample${key}`">
                <hr class="mt-4 mb-4">
                <div class="row bg-white" v-for="detail in transfer.details" :key="detail.id">
                  <div class="col border-end">
                    {{ moment(detail?.created_at).format("YYYY-MM-DD HH:mm:ss") }}
                  </div>
                  <div class="col border-end">
                    {{ detail?.employee_balance_list?.job_check_list?.company?.company_name }}
                  </div>
                  <div class="col border-end">
                    {{ detail?.employee_balance_list?.job_check_list?.branche?.title_b }}
                  </div>
                  <div class="col border-end">
                    <template v-if="detail?.employee_balance_list?.job_check_list?.job.id">
                      <a class="text-decoration-none" target="_blank" :href="route('admin.show_job_list', detail?.employee_balance_list?.job_check_list?.job?.id)">
                        {{ detail?.employee_balance_list?.job_check_list?.job?.establishment?.name_e }} <span class="job-id">#{{ detail?.employee_balance_list?.job_check_list?.job?.id }}</span>
                      </a>
                    </template>
                  </div>
                  <div class="col border-end">
                    <template v-if="detail?.employee_balance_list?.type == 'SHIFT'">
                      Смена {{ moment(detail?.employee_balance_list?.job_check_list?.started_at).format("YYYY-MM-DD") }}
                      <span :class="detail?.employee_balance_list?.job_check_list?.job_time_graphic?.started_at != detail?.employee_balance_list?.job_check_list?.started_at ? 'text-danger' : ''">{{ moment(detail?.employee_balance_list?.job_check_list?.started_at).format("HH:mm") }}</span> -
                      <span :class="detail?.employee_balance_list?.job_check_list?.job_time_graphic?.finished_at != detail?.employee_balance_list?.job_check_list?.finished_at ? 'text-danger' : ''">{{ moment(detail?.employee_balance_list?.job_check_list?.finished_at).format("HH:mm") }}</span>
                    </template>
                    <template v-if="detail?.employee_balance_list?.type == 'BONUS'">
                      Бонус
                    </template>
                  </div>
                  <div class="col">
                    <span class="text-success">{{ detail?.employee_balance_list?.amount }} {{ $t('general.rub') }}</span>
                  </div>
                </div>
              </div>
              <div class="text-muted text-sm-center cursor-pointer" style="position: absolute; bottom: 5px; font-size: 11px; right: 7px;">
                <a data-bs-toggle="collapse" @click.prevent="openBlock(key)" :href="`#multiCollapseExample${key}`" class="text-decoration-none text-muted p-2">
                  {{ openBlockIds.includes(key) ? $t('general.hide') : $t('general.more') }}
                  <font-awesome-icon :icon="openBlockIds.includes(key) ? 'chevron-up' : 'chevron-down'" class="ms-4" />
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</template>

<script setup>
import Layout from '@/Layouts/Admin/AppLayout.vue'
import { ref, watch } from 'vue'
import moment from 'moment'
import ConfirmUnpaidsModal from './ConfirmUnpaidsModal.vue'
import { useFixedNumber } from '@/Hooks/useFixedNumber'
import { DatePicker } from 'v-calendar'
import { router } from '@inertiajs/vue3'
import { usePermissionA } from '@/Hooks/usePermissionA'

defineOptions({ layout: Layout })

const props = defineProps({
  title: String,
  companies: Object,
  notPaidEmployees: Object,
  transfers: Object,
  notPaidEmployeesCount: Number,
  transfersCount: Number,
})

const notPaidEmployees = ref(props.notPaidEmployees)

const urlParams = new URLSearchParams(window.location.search)
const activeTab = ref(urlParams.get('tab') || 'unpaid')
const showconfirmUnpaidsModal = ref(false)

let selectedCompanies = []

if([...urlParams.getAll('companies[]')].length > 0) {
  for(let id in [...urlParams.getAll('companies[]')]) {
    selectedCompanies.push(+[...urlParams.getAll('companies[]')][id])
  }
}

const confirmUnpaids = () => {
  showconfirmUnpaidsModal.value = true
}

const setDate = (dateTime) => {
  return dateTime ? moment(dateTime).format("YYYY-MM-DD") : ''
}

const listIds = ref(localStorage.getItem('notPaids') || {})

const checkAllCheckboxes = ref([])
const checkedAll = ref(false)

const search = ref({
  text: urlParams.get('text'),
  activeTab: activeTab,
  companies: selectedCompanies,
  startedAt: urlParams.get('startedAt'),
  finishedAt: urlParams.get('finishedAt'),
  bonus: urlParams.get('bonus') || 1,
})

watch(search.value, (currentValue, oldValue) => {
  router.get(route('admin.employee_balance'), {
    search: true,
    tab: currentValue.activeTab,
    text: currentValue.text,
    companies: currentValue.companies,
    startedAt: currentValue.startedAt,
    finishedAt: currentValue.finishedAt,
    bonus: currentValue.bonus,
  }, {
    preserveState: true
  })
});

watch(() => props?.notPaidEmployees, (first, second) => {
  notPaidEmployees.value = first
});

const checkOne = (e, val) => {
  if(e.target.checked) {
    checkAllCheckboxes.value.push(+val)
  } else {
    checkAllCheckboxes.value = checkAllCheckboxes.value.filter(function(value, index, arr) {
      return value != val
    })
  }

  checkedAll.value = props.notPaidEmployees.data.length == checkAllCheckboxes.value.length ? true : false
}

const checkAll = (e, all = false) => {
  if(e?.target?.checked || all) {
    props.notPaidEmployees.data.filter(function (obj) {
      checkAllCheckboxes.value.push(+obj.id)
    })
    checkedAll.value = true
  } else {
    checkAllCheckboxes.value = []
    checkedAll.value = false
  }
}

checkAll(null, true)

const checkAmount = (employeeId) => {
  let getData = localStorage.getItem("notPaids");
  if(getData) {
    return JSON.parse(getData).some(elem => elem.employee_id == (employeeId))
  }

  return false
}

const setAmount = (employeeId, sum) => {
  let getData = localStorage.getItem("notPaids");
  let totalSum = 0;

  if(getData) {
    JSON.parse(getData).forEach(obj => {
      if(obj.employee_id == employeeId) {
        totalSum += +obj.amount;
      }
    })

    if(totalSum) {
      notPaidEmployees.value.data.map(item => {
        if(item.id == employeeId) {
          item.not_paid_sum = totalSum
        }
      })
    }
  }

  return +useFixedNumber(totalSum) || +useFixedNumber(sum)
}

const pickerConfig = {
  type: 'string',
  mask: 'YYYY-MM-DD',
}

const masks = {
  input: 'YYYY-MM-DD',
}

const openBlockIds = ref([])
const openBlock = (id) => {
  if(openBlockIds.value.includes(id)){
      openBlockIds.value.splice(openBlockIds.value.indexOf(id), 1);
      return;
   }
   openBlockIds.value.push(id);
}

</script>
