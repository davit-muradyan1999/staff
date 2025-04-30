<template>
  <AcceptJob
    v-if="openAcceptJobPopup"
    :job="selectedJob"
    @close="openAcceptJobPopup = false"
  />
  <ListApplications
    v-if="openListApplicationsPopup"
    :jobID="selectedJobID"
    @close="openListApplicationsPopup = false"
  />
  <ListSpecialVacancies
    v-if="openListSpecialVacanciesPopup"
    :jobID="selectedJobID"
    @close="openListSpecialVacanciesPopup = false"
  />
  <JobGraphic
    v-if="openGraphicPopup"
    :job="selectedGraphicJob"
    @close="openGraphicPopup = false"
  />
  <Employees
    v-if="employeeModal"
    :job="selectedEmployeeJob"
    @close="employeeModal = false"
  />
  <div class="row d-flex h-100 p-2 bg-white border rounded mb-3 position-relative" style="margin-left: 0; margin-right: 0;">
    <span v-if="$page.props.auth?.user?.role == 'WORKER' && job.job_type_id" class="special-star">
      <font-awesome-icon icon="star" />
    </span>
    <span class="special-bookmark" v-if="$page.props.auth?.user?.role == 'WORKER' && !props?.showMore && props?.showFavorite" :style="{ right: ($page.props.auth?.user?.role == 'ADMIN' ? 45 : 20) + 'px' }" :class="{'text-warning' : isFavorite}" @click="addToFavorite(job)">
      <font-awesome-icon icon="bookmark" />
    </span>
    <span v-if="$page.props.auth?.user?.role == 'ADMIN'" class="add-employee text-success cursor-pointer" title="Добавить сотрудников" @click="openEmployeePopup(job)">
      <font-awesome-icon icon="user-plus" class="fs-3 me-2" />
    </span>
    <span v-if="usePermissionM([3,4]) && usePermissionA([]) && editable && editLink" class="job-edit">
      <Link class="text-decoration-none" :href="route('company_jobs_add', job.id) + searchURL">
        <font-awesome-icon icon="pen-to-square" class="fs-3 me-2" />
      </Link>
    </span>
    <div class="col-md-2 mt-1 p-0">
      <img class="img-fluid img-responsive" :src="'/storage/' + job?.establishment?.img">
    </div>
    <div class="col-md-8 mt-1 ps-3">
      <div class="">
        <h2 class="media-title font-weight-semibold mb-3">
          <p class="text-decoration-none link-primary">{{ job?.establishment?.name_e }} <span class="job-id">#{{ job.id }}</span></p>
        </h2>
        <div class="row">
          <div class="col-md-6 col-sm-12">
            <div class="row">
              <div class="col-lg-4 col-md-5 col-sm-12"><strong>{{ $page.props.auth?.user?.role == 'COMPANY' ? 'Филиал' : 'Имя компании' }}</strong></div>
              <div class="col-lg-8 col-md-7 col-sm-12 mb-2">
                <template v-if="$page.props.auth?.user?.role == 'COMPANY'">
                  {{ $page.props.auth?.user?.role == 'COMPANY' ? job?.branche?.title_b : job?.establishment?.company?.company_name }}
                </template>
                <template v-else>
                  <a class="text-decoration-none" target="_blank" :href="route('company_profile', { id: job?.company_id })">
                    {{ $page.props.auth?.user?.role == 'COMPANY' ? job?.branche?.title_b : job?.establishment?.company?.company_name }}
                  </a>
                </template>
              </div>
              <div class="col-lg-4 col-md-5 col-sm-12"><strong>Адрес</strong></div>
              <div class="col-lg-8 col-md-7 col-sm-12 mb-2">{{ job?.branche?.address_b || job?.establishment?.company?.company_info?.email }}</div>
              <div class="col-lg-4 col-md-5 col-sm-12"><strong>Телефон</strong></div>
              <div class="col-lg-8 col-md-7 col-sm-12 mb-2">{{ job?.branche?.phone_b || job?.establishment?.company?.company_phone }}</div>
            </div>
          </div>
          <div class="col-md-6 col-sm-12">
            <div class="row">
              <div class="col-lg-4 col-md-5 col-sm-12"><strong>Старт</strong></div>
              <div class="col-lg-8 col-md-7 col-sm-12 mb-2">{{ job?.graphics[0]?.started_at?.slice(0, -3) }}</div>
              <div class="col-lg-4 col-md-5 col-sm-12"><strong>Посмотреть график</strong></div>
              <div class="col-lg-8 col-md-7 col-sm-12 mb-2"><span class="cursor-pointer text-primary" @click="openGraphic(job)">{{ job?.total_days }} смен, {{ useMinuteToHours(job?.total_minutes) }} часов</span></div>
            </div>
          </div>
        </div>

      </div>
    </div>
    <div class="col-md-2 mt-1 position-relative d-flex justify-content-end mb-2">
      <div class="row align-items-center" style="position: relative; right: -8px;">
        <div class="col-md-12 text-end pe-0 job-buttons">
          <div class="d-block mb-1">
            <button v-if="job?.establishment?.salary || job?.establishment?.tax || job?.establishment?.commission" type="button" class="btn-xs btn-primary text-white rounded-button-end rounded-3 border-0">
              <span class="float-start">
                {{ ((+job?.establishment?.salary || 0) + ($page.props.auth?.user?.role == 'COMPANY' || $page.props.auth?.user?.role == 'ADMIN' ? (+job?.establishment?.tax || 0) + (+job?.establishment?.commission || 0) : 0)) }} {{ $t('general.rubTime') }}
              </span>
            </button>
          </div>

          <div class="d-block mb-1">
            <button v-if="job?.bonus" type="button" class="btn-xs btn-primary text-white rounded-button-end rounded-3 border-0">
              <span class="float-start">{{ job?.bonus }} {{ $t('general.rub') }}: {{ $t('general.bonus') }}</span>
            </button>
          </div>

          <template v-if="editable">
            <div class="d-block mb-1">
              <button type="button" class="btn-xs btn-primary text-white rounded-button-end rounded-3 border-0">
                <span class="float-start">{{ setTotalPerEmployee(job) }} {{ $t('general.rub') }}: {{ $page.props.auth?.user?.role == 'WORKER' ? $t('general.perVacancy') : $t('general.forAnEmployee') }}</span>
              </button>
            </div>

            <div class="d-block mb-1">
              <button type="button" class="btn-xs btn-primary text-white rounded-button-end rounded-3 border-0">
                <span class="float-start">{{ setTotalPerEmployee(job, 'all') }} {{ $t('general.rub') }}: {{ $t('general.total') }}</span>
              </button>
            </div>
          </template>
          <template v-else>
            <div class="d-block mb-1">
              <button type="button" class="btn-xs btn-primary text-white rounded-button-end rounded-3 border-0">
                <span class="float-start">{{ setTotalPerEmployeeForWorker(job) }} {{ $t('general.rub') }}: {{ $page.props.auth?.user?.role == 'WORKER' ? $t('general.perVacancy') : $t('general.forAnEmployee') }}</span>
              </button>
            </div>
          </template>

          <div class="d-block mb-1" v-if="!showMore && !editable && type != 'history' && $page.props.auth?.user?.role != 'ADMIN'">
            <button type="button" class="btn text-white border-0 rounded-button-end rounded-3" :class="job?.applications?.[0]?.id ? 'btn-danger' : 'btn-success'" @click="acceptJob(job)">
              <span class="">{{ job?.applications?.[0]?.id ? $t('general.leave') : $t('general.accept') }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="text-muted text-sm-center cursor-pointer job-more-block">
      <a data-bs-toggle="collapse" class="text-decoration-none text-muted"  @click.prevent="openBlock(job.id)" :href="`#collapse${job.id}`">
        {{ openBlockIds.includes(job.id) ? $t('general.hide') : $t('general.more') }}
        <font-awesome-icon :icon="openBlockIds.includes(job.id) ? 'chevron-up' : 'chevron-down'" class="ms-4 job-more-icon" />
      </a>
    </div>

    <div :id="`collapse${job.id}`" class="collapse multi-collapse mt-2" :class="{'show': showMore || showMorePopup}">
      <hr>

      <!-- <div class="col-md-12" v-if="job?.establishment?.bonus">
        <p class="fw-bold mb-0">Бонус</p>
        <p>{{ job?.establishment?.bonus }}</p>
      </div> -->

      <div class="col-md-12" v-if="job?.establishment?.obligation_e">
        <p class="fw-bold mb-0">Обязанности</p>
        <p>{{ job?.establishment?.obligation_e }}</p>
      </div>
      <div class="col-md-12" v-if="job?.establishment?.requirement_e">
        <p class="fw-bold mb-0">Требования</p>
        <p>{{ job?.establishment?.requirement_e }}</p>
      </div>

      <div class="col-md-12">
        <p class="fw-bold mb-0">Требуется стажировка</p>
        <p>{{ +job?.is_need_internship ? 'Да' : 'Нет' }}</p>
      </div>

      <div class="col-md-12">
        <p class="fw-bold mb-0">Время перерыва оплачивается</p>
        <p>{{ +job?.is_paid_lunch ? 'Да' : 'Нет' }}</p>
      </div>

      <div class="col-md-12" v-if="job?.advantage_employees?.length > 0">
        <p class="fw-bold mb-0">Преимущество для сотрудников</p>
        <p>
          <template v-for="(advantage, index) in job?.advantage_employees" :key="advantage.id">
            {{ advantage?.advantage_employee?.name_e }}{{ index + 1 == job?.advantage_employees.length ? '' : ', ' }}
          </template>
        </p>
      </div>

      <div class="col-md-12" v-if="job?.establishment?.gender">
        <p class="fw-bold mb-0">Пол</p>
        <p>{{ useGenderText(job?.establishment?.gender) }}</p>
      </div>

      <template v-if="$page.props.auth?.user?.role == 'COMPANY' || $page.props.auth?.user?.role == 'ADMIN'">
        <div class="col-md-12" v-if="false">
          <p class="fw-bold mb-0">{{ $t('general.spending') }}</p>
          <p>{{ job?.establishment?.tax }}</p>
        </div>

        <div class="col-md-12" v-if="false">
          <p class="fw-bold mb-0">{{ $t('general.income') }}</p>
          <p>{{ job?.establishment?.commission }}</p>
        </div>

        <div class="col-md-12" v-if="job?.employee_qnt">
          <p class="fw-bold mb-0">Необходимое количество сотрудников</p>
          <p>{{ job?.employee_qnt }}</p>
        </div>

        <div class="col-md-12" v-if="job?.employee_qnt">
          <p class="fw-bold mb-0">{{ $t('general.employeeList') }}</p>
          <p class="cursor-pointer text-primary" @click="showListApplications(job.id)">{{ job?.employee_qnt }} / {{ job?.total_applications }}</p>
        </div>

        <div class="col-md-12" v-if="job?.job_type_id">
          <p class="fw-bold mb-0">{{ $t('general.specialVacancies') }}</p>
          <p v-if="+job?.job_type_id <= 2">{{ job?.job_type?.name_j }}</p>
          <p v-else class="cursor-pointer text-primary" @click="showListSpecialVacancies(job.id)">{{ job?.job_type?.name_j }}</p>
        </div>
      </template>


      <div class="col-md-12" v-if="job?.establishment?.driver_license_lists.length > 0">
        <p class="fw-bold mb-0">Водительские права</p>
        <p>
          <template v-for="(license, index) in job?.establishment?.driver_license_lists" :key="license.id">
            {{ license?.driver_license?.name_d }}{{ index + 1 == job?.establishment?.driver_license_lists.length ? '' : ', ' }}
          </template>
        </p>
      </div>
      <div class="col-md-12" v-if="job?.establishment?.mean_of_transport_lists.length > 0">
        <p class="fw-bold mb-0">Транспортные средства</p>
        <p>
          <template v-for="(transport, index) in job?.establishment?.mean_of_transport_lists" :key="transport.id">
            {{ transport?.mean_of_transport?.name_e }}{{ index + 1 == job?.establishment?.mean_of_transport_lists.length ? '' : ', ' }}
          </template>
        </p>
      </div>

      <div class="col-md-12">
        <p class="fw-bold mb-0">Отсутствие инвалидности</p>
        <p>{{ +job?.establishment?.absence_disability ? 'Да' : 'Нет' }}</p>
      </div>

      <div class="col-md-12" v-if="$page.props.auth?.user?.role != 'WORKER'">
        <p class="fw-bold mb-0">{{ $t("general.hasCreated") }}</p>
        <p>{{ job?.user?.name }}</p>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useGenderText } from '@/Hooks/useGenderText'
import AcceptJob from '@/Pages/Personal/Modals/AcceptJob.vue'
import ListApplications from '@/Pages/Personal/Modals/ListApplications.vue'
import ListSpecialVacancies from '@/Pages/Personal/Modals/ListSpecialVacancies.vue'
import JobGraphic from '@/Components/JobGraphic.vue'
import Employees from '@/Components/Employees.vue'
import axios from 'axios'
import { notify } from '@kyvg/vue3-notification'
import { useMinuteToHours } from '@/Hooks/useMinuteToHours'
import { useFixedNumber } from '@/Hooks/useFixedNumber'
import { usePermissionM } from '@/Hooks/usePermissionM'
import { usePermissionA } from '@/Hooks/usePermissionA'

const props = defineProps({
  job: Object,
  searchURL: String,
  editable: Boolean,
  editLink: Boolean,
  type: String,
  showMore: Boolean,
  showMorePopup: Boolean,
  showFavorite: Boolean
});

const isFavorite = ref(props?.job.favorite ? true : false)

const openAcceptJobPopup = ref(false)
const selectedJob = ref({})

const openListApplicationsPopup = ref(false)
const openListSpecialVacanciesPopup = ref(false)
const selectedJobID = ref('')

const openGraphicPopup = ref(false)
const employeeModal = ref(false)
const selectedGraphicJob = ref({})
const selectedEmployeeJob = ref({})

const openBlockIds = ref([])
const openBlock = (id) => {
  if(openBlockIds.value.includes(id)){
    openBlockIds.value.splice(openBlockIds.value.indexOf(id), 1);
    return;
  }
  openBlockIds.value.push(id);
}

if(props?.showMore || props?.showMorePopup) {
  openBlock(props?.job?.id)
}

const acceptJob = (job) => {
  openAcceptJobPopup.value = true
  selectedJob.value = job
}

const openGraphic = (data) => {
  openGraphicPopup.value = true
  selectedGraphicJob.value = data
}

const openEmployeePopup = (data) => {
  employeeModal.value = true
  selectedEmployeeJob.value = data
}

const setTotalPerEmployee = (job, type = null) => {
  const salary = job?.establishment?.salary ? job?.establishment?.salary : 0
  const tax = job?.establishment?.tax ? job?.establishment?.tax : 0
  const commission = job?.establishment?.commission ? job?.establishment?.commission : 0
  const totalHours =  job?.total_minutes ? useFixedNumber(+job?.total_minutes / 60, 4): 0
  const bonus = job?.bonus ? job?.bonus : 0
  const employeeQnt = job?.employee_qnt ? job?.employee_qnt : 0

  if(type == 'all') {
    return  useFixedNumber((((+salary + +tax + +commission) * +totalHours) + +bonus) * +employeeQnt)
  }

  return useFixedNumber(((+salary + +tax + +commission) * +totalHours) + +bonus)
}

const setTotalPerEmployeeForWorker = (job) => {
  const salary = job?.establishment?.salary ? job?.establishment?.salary : 0
  const totalHours = job?.total_minutes ? useFixedNumber(+job?.total_minutes / 60, 4) : 0
  const bonus = job?.bonus ? job?.bonus : 0

  return useFixedNumber((+salary * +totalHours) + +bonus)
}

const showListApplications = (jobID) => {
  openListApplicationsPopup.value = true
  selectedJobID.value = jobID
}

const showListSpecialVacancies = (jobID) => {
  openListSpecialVacanciesPopup.value = true
  selectedJobID.value = jobID
}

const addToFavorite = (job) => {
  axios.post(route('add_to_favorite'), job).then((response) => {
    isFavorite.value = response.data.isFavorite
    // notify({
    //   title: response.data.message,
    //   type: 'success'
    // })
  }).catch(error => {
    // notify({
    //   title: error.response.data.message,
    //   type: 'error'
    // })
  });
}

</script>
