<template>
  <Teleport to="body">
    <div
      style="display: block"
      class="modal fade show"
      id="addModeratorModal"
      tabindex="-1"
      aria-labelledby="addModeratorModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <form @submit.prevent="submit">
            <div class="modal-header text-center">
              <h4 class="modal-title">{{ $t("general.addShift") }}</h4>
            </div>
            <div class="modal-body">
              <div
                v-if="errorMsg"
                class="alert alert-danger alert-dismissible ml-0 mr-0 mb-2"
                role="alert"
              >
                {{ errorMsg }}
              </div>

              <div class="row g-3">
                <div class="row mb-3 mt-3">
                  <div class="col-md-2">
                    <label for="city" class="col-form-label">{{ $t("general.jobs") }}*</label>
                  </div>
                  <div class="col-md-10">
                    <Multiselect
                      v-model="form.job_id"
                      valueProp="id"
                      :options="jobs"
                      track-by="full_name"
                      :searchable="true"
                      :class="{ 'is-invalid': v$.job_id.$errors.length }"
                      @change="changeJob"
                    >

                      <template v-slot:singlelabel="{ value }">
                        <div class="multiselect-single-label">
                          {{ value.establishment_name }} #{{ value.id }}
                        </div>
                      </template>

                      <template v-slot:option="{ option }">
                        {{ option.establishment_name }} #{{ option.id }}
                      </template>

                    </Multiselect>
                    <div class="invalid-feedback">
                      {{ v$?.dt?.$errors?.[0]?.$message || v$?.dt?.$errors?.[0]?.$params?.message }}
                    </div>

                    <template v-if="selectedEmployees">
                      <div class="row mt-2">
                        <template v-for="employee in selectedEmployees[0]?.applications" :key="employee.id">
                          <div class="col-md-12" :class="[{'opacity-50' : !form.employeeIds.includes(employee?.user?.id)}]">
                            <input
                              type="checkbox"
                              v-model="form.employeeIds"
                              :value="employee?.user?.id"
                              :true-value="[]"
                              autocomplete="off"
                            >
                            {{ employee?.user?.name }}
                          </div>
                          <div class="invalid-feedback" :class="v$?.employeeIds?.$errors?.[0]?.$message || v$?.employeeIds?.$errors?.[0]?.$params?.message ? 'd-block' : ''">
                            {{ v$?.employeeIds?.$errors?.[0]?.$message || v$?.employeeIds?.$errors?.[0]?.$params?.message }}
                          </div>
                          <!-- <div class="col-md-2 text-end">
                            <font-awesome-icon v-if="form.employeeIds.includes(employee?.user?.id)" :icon="['fas', 'xmark']" class="text-danger me-1 cursor-pointer" @click="deleteEmployee(employee?.user?.id)" />
                            <font-awesome-icon v-else icon="check" class="text-success me-1 cursor-pointer" @click="deleteEmployee(employee?.user?.id)" />
                          </div> -->
                        </template>
                      </div>
                  </template>
                  </div>

                </div>

                <div class="row mb-3">
                  <div class="col-md-2">
                    <label for="city" class="col-form-label">{{ $t("general.date") }}*</label>
                  </div>
                  <div class="col-md-10">
                    <DatePicker v-model="form.dt" :model-config="pickerConfig" :masks="masks" mode="date" :popover="{ visibility: 'focus' }" locale="ru">
                      <template v-slot="{ inputValue, inputEvents }">
                        <input
                          class="form-control"
                          :class="{ 'is-invalid': v$.dt.$errors.length }"
                          id="inputClothingSize"
                          :value="inputValue"
                          v-on="inputEvents"
                        />
                      </template>
                    </DatePicker>
                    <div class="invalid-feedback">
                      {{ v$?.dt?.$errors?.[0]?.$message || v$?.dt?.$errors?.[0]?.$params?.message }}
                    </div>
                  </div>
                </div>

                <div class="row mb-3">
                  <div class="col-md-2">
                    <label for="city" class="col-form-label">{{ $t("general.beginning") }}*</label>
                  </div>
                  <div class="col-md-10">
                    <VueDatePicker v-model="form.started_hour_time" :cancelText="`${$t('general.cancel')}`" :selectText="`${$t('general.select')}`" :class="{ 'is-invalid': v$?.started_hour_time?.$errors.length }" time-picker disable-time-range-validation />
                    <div class="invalid-feedback">
                      {{ v$?.started_hour_time?.$errors?.[0]?.$message || v$?.started_hour_time?.$errors?.[0]?.$params?.message }}
                    </div>
                  </div>
                </div>

                <div class="row mb-3">
                  <div class="col-md-2">
                    <label for="city" class="col-form-label">{{ $t("general.end") }}*</label>
                  </div>
                  <div class="col-md-10">
                    <VueDatePicker v-model="form.finished_hour_time" :cancelText="`${$t('general.cancel')}`" :selectText="`${$t('general.select')}`" :class="{ 'is-invalid': v$?.finished_hour_time?.$errors.length }" time-picker disable-time-range-validation />
                    <div class="invalid-feedback">
                      {{ v$?.finished_hour_time?.$errors?.[0]?.$message || v$?.finished_hour_time?.$errors?.[0]?.$params?.message }}
                    </div>
                  </div>
                </div>

                <div class="row mb-3">
                  <div class="col-md-2">
                    <label for="city" class="col-form-label">{{ $t("general.lunchMin") }}*</label>
                  </div>
                  <div class="col-md-10">
                    <input
                      type="text"
                      class="form-control"
                      v-model="form.lunch"
                      :class="{ 'is-invalid': v$?.lunch?.$errors.length }"
                    >
                    <div class="invalid-feedback">
                      {{ v$?.lunch?.$errors?.[0]?.$message || v$?.lunch?.$errors?.[0]?.$params?.message }}
                    </div>
                  </div>
                </div>
              </div>
              <div class="modal-footer text-center">
               <div class="col-md-12 text-center">
                <button type="submit" class="btn btn-primary me-3">{{ $t('general.save') }}</button>
                <button type="button" class="btn btn-danger me-3" @click="closeModal">{{ $t('general.cancel') }}</button>
              </div>
            </div>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade show" v-click-away="onClickAway"></div>
  </Teleport>
</template>

<script setup>
import axios from 'axios'
import { ref, defineEmits, watch } from 'vue'
import moment from 'moment'
import { useForm } from '@inertiajs/vue3'
import useVuelidate from '@vuelidate/core'
import { Required,Numeric } from '../../../Utils/validationMessages'
import VueDatePicker from '@vuepic/vue-datepicker'
import { DatePicker } from 'v-calendar'
import { useTimeFormat } from '@/Hooks/useTimeFormat'
import { notify } from '@kyvg/vue3-notification'
import { router } from '@inertiajs/vue3'

const form = useForm({
  job_id: '',
  lunch: 30,
  dt: moment().format("YYYY-MM-DD"),
  started_hour_time: { "hours": 9, "minutes": 0, "seconds": 0 },
  finished_hour_time: { "hours": 18, "minutes": 0, "seconds": 0 },
  is_late_added: 1,
  started_at: '',
  finished_at: '',
  employeeIds: [],
});

const rules = {
  job_id: { Required },
  dt: { Required },
  started_hour_time: { Required },
  finished_hour_time: { Required },
  lunch: { Required, Numeric },
  employeeIds: { Required },
};

const v$ = useVuelidate(rules, form);

const props = defineProps({
  branche: Object,
  redirectURL: String
});

const submit = () => {
  v$.value.$touch();
  if (v$._value.$invalid || errorMsg.value) return false;

  form.started_hour = useTimeFormat(form.started_hour_time)
  form.finished_hour = useTimeFormat(form.finished_hour_time)

  form.started_at = form.dt + ' ' + form.started_hour
  form.finished_at = form.dt + ' ' + form.finished_hour

  axios.post(route('add_company_job_graphic'), form).then((response) => {
    notify({
      title: response.data.message,
      type: 'success'
    })
    closeModal()
    router.get(window.location.href)
  }).catch(error => {
    console.log(error)
    errorMsg.value = error.response.data.message
  });
}

const emit = defineEmits(['close'])

const errorMsg = ref('')
const jobs = ref([])
const selectedEmployees = ref([])

const onClickAway = (event) => {
  if(event.target.classList.contains('modal')) {
    emit("close")
  }
}

const changeJob = (e) => {
  selectedEmployees.value = []
  form.employeeIds = []

  if(e) {
    selectedEmployees.value = jobs.value.filter((value) => value.id == e)

    for(let employee in selectedEmployees.value?.[0]?.applications) {
      form.employeeIds.push(selectedEmployees.value?.[0]?.applications[employee].user?.id)
    }
  }
}

const deleteEmployee = (userId) => {
  if(form.employeeIds.includes(userId)) {
    form.employeeIds.splice(form.employeeIds.indexOf(userId), 1)
    return
  }

  form.employeeIds.push(userId)
}

const getJobs = () => {
  axios.get(route('get_add_shift_jobs')).then((response) => {
    if(response.data.status == 'success')
      jobs.value = response.data.jobs
  }).catch(error => {

  });
}

getJobs()

const pickerConfig = {
  type: 'string',
  mask: 'YYYY-MM-DD',
}

const masks = {
  input: 'YYYY-MM-DD',
};

const closeModal = () => {
  emit("close")
}

watch(form, (currentValue, oldValue) => {
  if(currentValue.started_hour_time?.hours && currentValue.finished_hour_time?.hours) {
    if((currentValue.finished_hour_time.hours < currentValue.started_hour_time.hours) || (currentValue.finished_hour_time.hours == currentValue.started_hour_time.hours && currentValue.finished_hour_time.minutes < currentValue.started_hour_time.minutes)) {
      errorMsg.value = "Конец не может быть больше, чем начало"
    } else {
      errorMsg.value = ""
    }
  }
});

</script>
