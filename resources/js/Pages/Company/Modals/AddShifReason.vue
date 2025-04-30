<template>
  <Teleport to="body">
    <div
      style="display: block"
      class="modal fade show"
      id="addEstablishmentModal"
      tabindex="-1"
      aria-labelledby="addEstablishmentModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <form>
            <div class="modal-header text-center">
              <h4 class="modal-title">
                <template v-if="type == 'confirm'">
                  {{ $t("general.shiftEditing") }}
                </template>
                <template v-else-if="type == 'cancel'">
                  {{ $t("general.cancelShift") }}
                </template>
                <template v-else-if="type == 'history'">
                  {{ $t("general.history") }}
                </template>
              </h4>
              <button
                type="button"
                class="btn-close d-inline-block"
                @click="closeModal"
              ></button>
            </div>
            <div class="modal-body">
              <div
                v-if="errorMsg"
                class="alert alert-danger alert-dismissible ml-0 mr-0 mb-2"
                role="alert"
              >
                {{ errorMsg }}
              </div>

              <div class="row">
                <template v-if="type == 'history'">
                  <p><span class="fw-bold">Редактировано</span> - {{ job?.jcl_user_name }} / {{ job?.jcl_created_at }}</p>
                  <p v-if="+job?.jtg_is_late_added"><span class="fw-bold">{{ $t('general.addedManually') }}</span></p>
                </template>
                <template v-if="type == 'confirm' || (type == 'history' && form.job.jcl_status == 'CONFIRMED')">
                  <template v-if="!+job?.jtg_is_late_added">
                    <p class="mb-1" v-if="type == 'history' && form.job.jcl_status == 'CONFIRMED'">Время на графике ({{ useMinuteToHours(job?.total_minutes) }} часов)</p>
                    <div class="col-md-4 mb-2">
                      <VueDatePicker :disabled="type == 'history' ? true : false" v-model="form.started_at" :cancelText="`${$t('general.cancel')}`" :selectText="`${$t('general.select')}`" :class="{ 'is-invalid': v$?.started_at?.$errors.length }" time-picker disable-time-range-validation />
                      <div class="invalid-feedback">
                        {{ v$?.started_at?.$errors?.[0]?.$message || v$?.started_at?.$errors?.[0]?.$params?.message }}
                      </div>
                    </div>

                    <div class="col-md-4 mb-2">
                      <VueDatePicker :disabled="type == 'history' ? true : false" v-model="form.finished_at" :cancelText="`${$t('general.cancel')}`" :selectText="`${$t('general.select')}`" :class="{ 'is-invalid': v$?.finished_at?.$errors.length }" time-picker disable-time-range-validation />
                      <div class="invalid-feedback">
                        {{ v$?.finished_at?.$errors?.[0]?.$message || v$?.finished_at?.$errors?.[0]?.$params?.message }}
                      </div>
                    </div>

                    <div class="col-md-4 mb-2">
                      <input
                        :disabled="type == 'history' ? true : false"
                        :placeholder="`${$t('general.lunchMin')}`"
                        type="text"
                        class="form-control"
                        v-model="form.lunch"
                        :class="{ 'is-invalid': v$?.lunch?.$errors.length }"
                      >
                      <div class="invalid-feedback">
                        {{ v$?.lunch?.$errors?.[0]?.$message || v$?.lunch?.$errors?.[0]?.$params?.message }}
                      </div>
                    </div>
                  </template>


                  <template v-if="type == 'history' && form.job.jcl_status == 'CONFIRMED'">
                    <p class="mb-1"> {{ !+job?.jtg_is_late_added ? 'Измененное время' : 'Добавленное время' }} ({{ useMinuteToHours(job?.list_total_minutes) }} часов)</p>
                    <div class="col-md-4 mb-2">
                      <VueDatePicker :disabled="type == 'history' ? true : false" v-model="form.graphic_started_at" :cancelText="`${$t('general.cancel')}`" :selectText="`${$t('general.select')}`" time-picker disable-time-range-validation />
                    </div>

                    <div class="col-md-4 mb-2">
                      <VueDatePicker :disabled="type == 'history' ? true : false" v-model="form.graphic_finished_at" :cancelText="`${$t('general.cancel')}`" :selectText="`${$t('general.select')}`" time-picker disable-time-range-validation />
                    </div>

                    <div class="col-md-4 mb-2">
                      <input
                        :disabled="type == 'history' ? true : false"
                        :placeholder="`${$t('general.lunchMin')}`"
                        type="text"
                        class="form-control"
                        v-model="form.graphic_lunch"
                        :class="{ 'is-invalid': v$?.lunch?.$errors.length }"
                      >
                    </div>
                  </template>
                </template>

                <div class="mb-1 row" :class="type == 'confirm' ? 'mt-3' : 'mt-1'">
                  <template v-for="reason in shiftReasons.filter(function (element) {
                      if(type == 'confirm' || (type == 'history' && form.job.jcl_status == 'CONFIRMED')) {
                        return element.type == 'CONFIRMATION'
                      } else if(type == 'cancel' || (type == 'history' && form.job.jcl_status == 'CANCELED')) {
                        return element.type == 'CANCELED'
                      }
                    })" :key="reason.id"
                  >
                    <div class="checkbox">
                      <label>
                        <input
                          class="form-check-input me-1"
                          type="checkbox"
                          :id="`reason${reason.id}`"
                          :value="reason.id"
                          :true-value="1"
                          :false-value="0"
                          v-model="form.reasons"
                          :disabled="type == 'history' ? true : false"
                        >
                          {{ reason.name_s }}
                      </label>
                    </div>
                  </template>

                  <template v-for="reason in shiftReasons.filter(function (element) {
                        return element.type == 'OTHER'
                    })" :key="reason.id"
                  >
                    <div class="checkbox">
                      <label>
                        <input
                          class="form-check-input me-1"
                          type="checkbox"
                          :id="`reason${reason.id}`"
                          :value="reason.id"
                          :true-value="1"
                          :false-value="0"
                          v-model="form.reasons"
                          @change="showDescription"
                          :disabled="type == 'history' ? true : false"
                        >
                        {{ reason.name_s }}
                      </label>
                    </div>
                  </template>
                </div>

                <div class="col-md-12">
                  <textarea
                    class="form-control"
                    rows="4"
                    :disabled="showDesc"
                    v-model="form.description"
                  ></textarea>
                </div>
              </div>

            </div>
            <div class="modal-footer text-center">
               <div class="col-md-12 text-center">
                <button type="button" class="btn btn-primary me-3" @click="setDataModal" v-if="type == 'confirm'">{{ $t('general.save') }}</button>
                <button type="button" class="btn btn-danger me-3" @click="setCancelled" v-else-if="type == 'cancel'">{{ $t('general.cancel') }}</button>
                <button type="button" class="btn btn-secondary" @click="closeModal">{{ $t('general.close') }}</button>
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
import { useForm } from '@inertiajs/vue3'
import useVuelidate from '@vuelidate/core'
import { Required, Email, MinLength, Numeric, AboveZero } from '../../../Utils/validationMessages'
import { ref, defineEmits, watch } from 'vue'
import { notify } from '@kyvg/vue3-notification'
import { router } from '@inertiajs/vue3'
import { DatePicker } from 'v-calendar'
import VueDatePicker from '@vuepic/vue-datepicker'
import moment from 'moment'
import { useMinuteToHours } from '@/Hooks/useMinuteToHours'

const props = defineProps({
  jobs: Object,
  job: Object,
  shiftReasons: Object,
  type: String,
  all: String,
  checkeds: Array,
});

const emit = defineEmits(['close'])

const errorMsg = ref('')
const showDesc = ref(true)

let graphicLunch = ''
let graphicStartedAt = ''
let graphicFinishedAt = ''
let lunch = ''
let startedAt = ''
let finishedAt = ''

graphicLunch = props?.job.jcl_lunch

graphicStartedAt = {
  "hours": moment(props?.job.jcl_started_at).format("HH"),
  "minutes": moment(props?.job.jcl_started_at).format("mm"),
  "seconds": 0
}

graphicFinishedAt = {
  "hours": moment(props?.job?.jcl_finished_at).format("HH"),
  "minutes": moment(props?.job?.jcl_finished_at).format("mm"),
  "seconds": 0
}

lunch = props?.job.jtg_lunch

startedAt = {
  "hours": moment(props?.job.jtg_started_at).format("HH"),
  "minutes": moment(props?.job.jtg_started_at).format("mm"),
  "seconds": 0
}
finishedAt = {
  "hours": moment(props?.job?.jtg_finished_at).format("HH"),
  "minutes": moment(props?.job?.jtg_finished_at).format("mm"),
  "seconds": 0
}

const form = ref({
  id: props?.job?.jtg_id,
  user_name: props?.job?.user_name,
  jobs: props?.jobs,
  job: props?.job,
  graphic_lunch: graphicLunch,
  graphic_started_at: graphicStartedAt,
  graphic_finished_at: graphicFinishedAt,
  lunch: lunch,
  started_at: startedAt,
  finished_at: finishedAt,
  all: props?.all,
  checkeds: props.checkeds,
  reasons: [],
  description: ''
});

if(props.type == 'history') {
  for(let reason in props?.job?.shiftReasons) {
    form.value.reasons.push(props?.job?.shiftReasons[reason].shift_reason_id)

    if(props?.job?.shiftReasons[reason].description) {
      form.value.description = props?.job?.shiftReasons[reason].description
    }
  }
}


const rules = {
  started_at: { Required },
  finished_at: { Required },
  lunch: { Required, Numeric },
};

const v$ = useVuelidate(rules, form);

const submit = (status) => {
  v$.value.$touch();
  if (v$._value.$invalid) return false;

}

const showDescription = (e) => {
  showDesc.value = e.target.checked ? false : true

  if(!e.target.checked) {
    form.value.description = ''
  }
}

const onClickAway = (event) => {
  if(event.target.classList.contains('modal')) {
    emit("close")
  }
}

const setCancelled = () => {
  const routeUrl = route('set_check_list_status')

  axios.post(routeUrl, form.value).then((response) => {
      notify({
        title: response.data.message,
        type: 'success'
      })
      closeModal()
      router.get(route('check_lists'))
    }).catch(error => {
      errorMsg.value = error.response.data.message
    });
}

const setDataModal = () => {
  v$.value.$touch();
  if (v$._value.$invalid) return false;

  if(form.value?.started_at?.hours == form.value?.finished_at?.hours && form.value?.started_at?.minutes == form.value?.finished_at?.minutes) {
    errorMsg.value = 'Часы не могут быть равны'
  }

  if (errorMsg.value) return false;
  emit("close", {form: form.value})
}

const closeModal = () => {
  emit("close")
}

const masks = {
  inputDateTime24hr: "YYYY-MM-DD HH:mm",
}

watch(form.value, (currentValue, oldValue) => {
  if(currentValue.started_at?.hours && currentValue.finished_at?.hours) {
    if((currentValue.finished_at.hours < currentValue.started_at.hours) || (currentValue.finished_at.hours == currentValue.started_at.hours && currentValue.finished_at.minutes < currentValue.started_at.minutes)) {
      errorMsg.value = "Конец не может быть больше, чем начало"
    } else {
      errorMsg.value = ""
    }
  }
});
</script>
