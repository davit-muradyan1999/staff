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
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <form>
            <div class="modal-header text-center">
              <h4 class="modal-title">{{ $t("general.specialVacancies") }}</h4>
              <button
                type="button"
                class="btn-close d-inline-block"
                @click="closeModal"
              ></button>
            </div>

            <div class="modal-body">
              <div class="mb-3 row">
                <label for="inputName" class="col-sm-2 col-form-label">{{ $t('general.specialVacancies') }}</label>
                <div class="col-sm-10">
                  <Multiselect
                    v-model="form.job_type_id"
                    valueProp="id"
                    :options="jobTypes"
                    :searchable="true"
                    label="name_t"
                    track-by="name_t"
                    :class="{ 'is-invalid': v$.job_type_id.$errors.length }"
                    @change="changeSpecialVacancy"
                  />
                  <div class="invalid-feedback">
                    {{ v$?.job_type_id?.$errors?.[0]?.$message || v$?.job_type_id?.$errors?.[0]?.$params?.message }}
                  </div>
                </div>
              </div>

              <template v-if="showSpecialWorkerBlock">
                <div class="mb-3 row">
                  <label for="inputName" class="col-sm-2 col-form-label">{{ $t('general.branche') }} ({{ $t('general.special') }})</label>
                  <div class="col-sm-10">
                    <Multiselect
                      v-model="form.specialBranches"
                      valueProp="id"
                      :options="branches"
                      :searchable="true"
                      label="title_b"
                      track-by="title_b"
                      mode="tags"
                      @change="changeSpecialBranch"
                    />
                  </div>
                </div>

                <div class="mb-3 row" v-if="form.job_type_id == 5">
                  <label for="inputName" class="col-sm-2 col-form-label">{{ $t('general.collaborator') }} ({{ $t('general.special') }})</label>
                  <div class="col-sm-10">
                    <Multiselect
                      v-model="form.specialEmployees"
                      valueProp="id"
                      :options="specialEmployeeDatas"
                      :searchable="true"
                      label="name"
                      track-by="name"
                      mode="tags"
                    />
                  </div>
                </div>
              </template>
            </div>
            <div class="modal-footer text-center">
               <div class="col-md-12 text-center">
                <button type="button" class="btn btn-primary me-3" @click="submit('save')">{{ $t('general.confirm') }}</button>
                <button type="button" class="btn btn-danger me-3" @click="submit('delete')" v-if="form.job_type_id">{{ $t('general.delete') }}</button>
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
import { Required, Email, MinLength, Numeric, ImageExtension } from '../../../Utils/validationMessages'
import { ref, defineEmits } from 'vue'
import { notify } from '@kyvg/vue3-notification'
import { router } from '@inertiajs/vue3'
import { DatePicker } from 'v-calendar'
import VueDatePicker from '@vuepic/vue-datepicker'
import moment from 'moment'

const props = defineProps({
  jobTypeId: Number,
  jobTypes: Object,
  branches: Object,
});

const emit = defineEmits(['close'])

const specialBrancheDatas = ref([])
const specialEmployeeDatas = ref([])
const showSpecialWorkerBlock = ref(false)
const specialBranches = ref([])
const errorMsg = ref('')
const showDesc = ref(true)

const form = ref({
  job_type_id: props?.jobTypeId,
  specialBranches: [],
  specialEmployees: []
});

const rules = {
  job_type_id: { Required },
};

if(props?.jobTypeId == 3 || props?.jobTypeId == 4 || props?.jobTypeId == 5)
  showSpecialWorkerBlock.value = true

const v$ = useVuelidate(rules, form);

const submit = (type) => {
  if(type == 'delete') {
    form.value.job_type_id = ''
    form.value.specialBranches = []
    form.value.specialEmployees = []
    form.value.selectedBrancheOptions = []
    form.value.selectedEmployeeOptions = []

    emit("close", form.value)
    return true
  }

  v$.value.$touch();
  if (v$._value.$invalid || errorMsg.value) return false

  let selectedBranches = []
  let selectedEmployees = []

  if(props?.branches.length > 0) {
    for(let branche in props.branches) {
      for(let i in form.value.specialBranches) {
        if(form.value.specialBranches[i] == props.branches[branche].id) {
          selectedBranches.push(props.branches[branche])
        }
      }
    }
  }

  if(specialEmployeeDatas.value.length > 0) {
    for(let employee in specialEmployeeDatas.value) {
      for(let i in form.value.specialEmployees) {
        if(form.value.specialEmployees[i] == specialEmployeeDatas.value[employee].id) {
          selectedEmployees.push(specialEmployeeDatas.value[employee])
        }
      }
    }
  }

  form.value.selectedBrancheOptions = selectedBranches
  form.value.selectedEmployeeOptions = selectedEmployees

  emit("close", form.value)
}

const changeSpecialVacancy = async (value) => {
  if(value == 3 || value == 4 || value == 5) {
    showSpecialWorkerBlock.value = true
  } else {
    showSpecialWorkerBlock.value = false
  }
}

const changeSpecialBranch = async (ids) => {
  if(form.value.job_type_id != 5) return false

  specialBranches.value = ids

  axios.get(route('get_company_employees'), {
    params: {
      branche_ids: specialBranches.value
    }
  }
  ).then((response) => {
    specialEmployeeDatas.value = response.data.users
  }).catch(error => {
    errorMsg.value = error.response.data.message
  });
}

const onClickAway = (event) => {
  if(event.target.classList.contains('modal')) {
    emit("close")
  }
}

const closeModal = () => {
  emit("close")
}

const masks = {
  inputDateTime24hr: "YYYY-MM-DD HH:mm",
}
</script>
