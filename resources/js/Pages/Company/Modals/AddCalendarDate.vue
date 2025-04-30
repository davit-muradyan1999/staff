<template>
  <Teleport to="body">
    <div
      style="display: block"
      class="modal fade show"
      id="addBranchModal"
      tabindex="-1"
      aria-labelledby="addBranchModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <form @submit.prevent="submit">
            <div class="modal-header text-center">
              <h4 class="modal-title">{{ !data?.id ? $t("general.addTimeGraphic") : $t("general.addTimeGraphic") }}</h4>
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

              <div class="row mb-3">
                <div class="col-md-2 col-12">
                  <label for="city" class="col-form-label">{{ $t("general.beginning") }}*</label>
                </div>
                <div class="col-md-8 col-12 mb-2">
                  <VueDatePicker v-model="form.started_at" :cancelText="`${$t('general.cancel')}`" :selectText="`${$t('general.select')}`" :class="{ 'is-invalid': v$?.started_at?.$errors.length }" time-picker disable-time-range-validation />
                  <div class="invalid-feedback">
                    {{ v$?.started_at?.$errors?.[0]?.$message || v$?.started_at?.$errors?.[0]?.$params?.message }}
                  </div>
                </div>
                <div class="col-md-2 col-12">
                  <button v-if="rememberedFields.includes('started_at')" type="button" class="btn btn-secondary" @click.prevent="rememberField('started_at', 'remove')">{{ $t('general.reset') }}</button>
                  <button v-else type="button" class="btn btn-warning" @click.prevent="rememberField('started_at', 'add')">{{ $t('general.remember') }}</button>
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-2 col-12">
                  <label for="city" class="col-form-label">{{ $t("general.end") }}*</label>
                </div>
                <div class="col-md-8 col-12 mb-2">
                  <VueDatePicker v-model="form.finished_at" :cancelText="`${$t('general.cancel')}`" :selectText="`${$t('general.select')}`" :class="{ 'is-invalid': v$?.finished_at?.$errors.length }" time-picker disable-time-range-validation />
                  <div class="invalid-feedback">
                    {{ v$?.finished_at?.$errors?.[0]?.$message || v$?.finished_at?.$errors?.[0]?.$params?.message }}
                  </div>
                </div>
                <div class="col-md-2 col-12">
                  <button v-if="rememberedFields.includes('finished_at')" type="button" class="btn btn-secondary" @click.prevent="rememberField('finished_at', 'remove')">{{ $t('general.reset') }}</button>
                  <button v-else type="button" class="btn btn-warning" @click.prevent="rememberField('finished_at', 'add')">{{ $t('general.remember') }}</button>
                </div>
              </div>

               <div class="row mb-3">
                <div class="col-md-2 col-12 ">
                  <label for="city" class="col-form-label">{{ $t("general.lunchMin") }}*</label>
                </div>
                <div class="col-md-8 col-12 mb-2">
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
                <div class="col-md-2 col-12">
                  <button v-if="rememberedFields.includes('lunch')" type="button" class="btn btn-secondary" @click.prevent="rememberField('lunch', 'remove')">{{ $t('general.reset') }}</button>
                  <button v-else type="button" class="btn btn-warning" @click.prevent="rememberField('lunch', 'add')">{{ $t('general.remember') }}</button>
                </div>
              </div>

            </div>
            <div class="modal-footer">
              <input type="submit" class="btn btn-primary" :value="!data?.id ? $t('general.add') : $t('general.edit')" />
              <button type="button" class="btn btn-secondary" @click.prevent="closeModal">{{ $t('general.revoke') }}</button>
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
import VueDatePicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'
import moment from 'moment'

const props = defineProps({
  data: Object,
  row: Object,
  rememberData: Object
});

const emit = defineEmits(['close', 'remember'])

const errorMsg = ref('')

let startedAt = '';
let finishedAt = '';
let lunch = '';

const rememberedFields = ref([]);

if(props?.data && props.row?.event?._def?.publicId) {
  for(let data in props?.data.graphics) {
    if(props?.data?.graphics[data].id == props.row?.event?._def?.publicId) {
      startedAt = {
        "hours": moment(props?.data?.graphics[data].started_at).format("HH"),
        "minutes": moment(props?.data?.graphics[data].started_at).format("mm"),
        "seconds": 0
      }
      finishedAt = {
        "hours": moment(props?.data?.graphics[data].finished_at).format("HH"),
        "minutes": moment(props?.data?.graphics[data].finished_at).format("mm"),
        "seconds": 0
      }
      lunch = props?.data?.graphics[data].lunch
    }
  }
}

if(!props?.data && props.row?.event?._def?.publicId) {

  if(props.row?.event?._def?.extendedProps?.startTimeValue && props.row?.event?._def?.extendedProps?.endTimeValue) {
    let startSplit = props.row?.event?._def?.extendedProps?.startTimeValue.split(':')
    let endSplit = props.row?.event?._def?.extendedProps?.endTimeValue.split(':')

    startedAt = {
      "hours": startSplit[0],
      "minutes": startSplit[1],
      "seconds": 0
    }
    finishedAt = {
      "hours": endSplit[0],
      "minutes": endSplit[1],
      "seconds": 0
    }

    lunch = props.row?.event?._def?.extendedProps?.lunch
  }
}

const form = useForm({
  row: props?.data,
  event: props?.row,
  lunch: (Number.isInteger(parseInt(lunch)) ? lunch : (props?.rememberData?.lunch || 30)),
  started_at: startedAt ||  props?.rememberData?.started_at || { "hours": 9, "minutes": 0, "seconds": 0 },
  finished_at: finishedAt ||  props?.rememberData?.finished_at || { "hours": 18, "minutes": 0, "seconds": 0 },
});

const rules = {
  started_at: { Required },
  finished_at: { Required },
  lunch: { Required, Numeric },
};

const v$ = useVuelidate(rules, form);

const submit = () => {
  v$.value.$touch();
  if (v$._value.$invalid || errorMsg.value) return false;

  emit("close", form)
}

if(props?.rememberData?.started_at) rememberedFields.value.push('started_at')
if(props?.rememberData?.finished_at) rememberedFields.value.push('finished_at')
if(props?.rememberData?.lunch) rememberedFields.value.push('lunch')

const rememberField = (field, type) => {
  if(type == 'add') {
    rememberedFields.value.push(field)
  } else {
    rememberedFields.value.splice(rememberedFields.value.indexOf(field), 1);
  }

  const data = {
    'field': field,
    'type': type,
    'value': form[field]
  }
  emit("remember", data)
}

const onClickAway = (event) => {
  if(event.target.classList.contains('modal')) {
    emit("close")
  }
}

const closeModal = () => {
  emit("close")
}

watch(form, (currentValue, oldValue) => {
  if(currentValue.started_at?.hours && currentValue.finished_at?.hours) {
    if((currentValue.finished_at.hours < currentValue.started_at.hours) || (currentValue.finished_at.hours == currentValue.started_at.hours && currentValue.finished_at.minutes < currentValue.started_at.minutes)) {
      errorMsg.value = "Конец не может быть больше, чем начало"
    } else {
      errorMsg.value = ""
    }
  }
});

</script>
