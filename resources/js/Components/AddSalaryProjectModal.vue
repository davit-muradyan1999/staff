<template>
  <Teleport to="body">
    <div
      style="display: block"
      class="modal fade show"
      id="addModeratorModal"
      tabindex="-1"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-xl modal-xl-size">
        <div class="modal-content">
          <form>
            <div class="modal-header text-center">
              <h4 class="modal-title">{{ $t("general.salaryProject") }} (Тестовый)</h4>
              <button
                type="button"
                class="btn-close d-inline-block"
                @click="closeModal"
              ></button>
            </div>
            <div class="modal-body">
              <div
                class="alert alert-danger alert-dismissible ml-0 mr-0 mb-2"
                role="alert"
                v-if="errorMsg"
              >
                {{ errorMsg }}
              </div>
              <div class="row">
                <div class="col-md-12 mb-3">
                  <div class="row">
                    <div class="col-md-4">
                      {{ $t("general.addEmployeeRecipient") }}
                    </div>
                    <div class="col-md-2">
                      <button v-if="addRecipientInfo?.status == 'SUCCESS'" type="button" class="btn btn-sm btn-primary me-3" disabled>{{ $t('general.added') }}</button>
                      <button v-else type="button" class="btn btn-sm btn-primary me-3" @click="addEmployeeRecipient">{{ $t('general.add') }}</button>
                    </div>
                    <div class="col-md-6">
                      <span v-if="addRecipientInfo?.created_at">{{ moment(addRecipientInfo.created_at).format("YYYY-MM-DD HH:mm:ss") }}</span>
                    </div>
                  </div>
                </div>

                <div class="col-md-12 mb-3">
                  <div class="row">
                    <div class="col-md-4">
                      {{ $t("general.addEmployeeRecipientResult") }}
                    </div>
                    <div class="col-md-2">
                      <button type="button" v-if="addRecipientInfo?.received_correlation_id" class="btn btn-sm btn-primary me-3" @click="getEmployeeRecipientResult(addRecipientInfo?.received_correlation_id)">{{ $t('general.getResult') }}</button>
                    </div>
                    <div class="col-md-6">
                      <pre v-if="addRecipientResult.length > 0">
                        {{ addRecipientResult }}
                      </pre>
                    </div>
                  </div>
                </div>

                <hr>

                <div class="col-md-12 mb-3">
                  <div class="row">
                    <div class="col-md-4">
                      {{ $t("general.salaryCreateEmployee") }}
                    </div>
                    <div class="col-md-2">
                      <button v-if="salaryCreateEmployeeInfo?.status == 'SUCCESS'" type="button" class="btn btn-sm btn-primary me-3" disabled>{{ $t('general.added') }}</button>
                      <button v-else type="button" class="btn btn-sm btn-primary me-3" @click="addSalaryCreateEmployee">{{ $t('general.add') }}</button>
                    </div>
                    <div class="col-md-6">
                      <span v-if="salaryCreateEmployeeInfo?.created_at">{{ moment(salaryCreateEmployeeInfo.created_at).format("YYYY-MM-DD HH:mm:ss") }}</span>
                    </div>
                  </div>
                </div>

                <div class="col-md-12 mb-3">
                  <div class="row">
                    <div class="col-md-4">
                      {{ $t("general.salaryGetEmployeesCreateResult") }}
                    </div>
                    <div class="col-md-2">
                      <button type="button" v-if="salaryCreateEmployeeInfo?.received_correlation_id" class="btn btn-sm btn-primary me-3" @click="getSalaryCreateEmployeeResult(salaryCreateEmployeeInfo?.received_correlation_id)">{{ $t('general.getResult') }}</button>
                    </div>
                    <div class="col-md-6">
                      <pre v-if="salaryCreateEmployeeResult.length > 0">
                        {{ salaryCreateEmployeeResult }}
                      </pre>
                    </div>
                  </div>
                </div>

                <hr>

                <div class="col-md-12 mb-3">
                  <div class="row">
                    <div class="col-md-4">
                      {{ $t("general.salaryGetEmployeesList") }}
                    </div>
                    <div class="col-md-2">
                      <button type="button" v-if="salaryCreateEmployeeInfo?.received_correlation_id" class="btn btn-sm btn-primary me-3" @click="salaryGetEmployeeList">{{ $t('general.getResult') }}</button>
                    </div>
                    <div class="col-md-6">
                      <pre v-if="salaryGetEmployeeListResult.length > 0">
                        {{ salaryGetEmployeeListResult }}
                      </pre>
                    </div>
                  </div>
                </div>

                <hr>

                <div class="col-md-12 mb-3">
                  <div class="row">
                    <div class="col-md-4">
                      {{ $t("general.salaryCreatePaymentRegistry") }}
                    </div>
                    <div class="col-md-2">
                      <button v-if="salaryCreatePaymentRegistryInfo?.status == 'SUCCESS'" type="button" class="btn btn-sm btn-primary me-3" disabled>{{ $t('general.added') }}</button>
                      <button v-else type="button" class="btn btn-sm btn-primary me-3" @click="addSalaryCreatePaymentRegistry">{{ $t('general.add') }}</button>
                    </div>
                    <div class="col-md-6">
                      <span v-if="salaryCreatePaymentRegistryInfo?.created_at">{{ moment(salaryCreatePaymentRegistryInfo.created_at).format("YYYY-MM-DD HH:mm:ss") }}</span>
                    </div>
                  </div>
                </div>

                <hr>

                <div class="col-md-12 mb-3">
                  <div class="row">
                    <div class="col-md-4">
                      {{ $t("general.salaryGetPaymentRegistryCreateResult") }}
                    </div>
                    <div class="col-md-2">
                      <button type="button" v-if="salaryCreatePaymentRegistryResultInfo?.status == 'SUCCESS'" disabled class="btn btn-sm btn-primary me-3">{{ $t('general.getResult') }}</button>
                      <button type="button" v-else class="btn btn-sm btn-primary me-3" @click="getSalaryGetPaymentRegistryCreateResult(salaryCreateEmployeeInfo?.received_correlation_id)">{{ $t('general.getResult') }}</button>
                    </div>
                    <div class="col-md-6">
                      <pre v-if="salaryGetPaymentRegistryCreateResult?.status">
                        {{ salaryGetPaymentRegistryCreateResult }}
                      </pre>
                      <span v-if="salaryCreatePaymentRegistryResultInfo?.created_at">{{ moment(salaryCreatePaymentRegistryResultInfo.created_at).format("YYYY-MM-DD HH:mm:ss") }}</span>
                    </div>
                  </div>
                </div>

                <div class="col-md-12 mb-3">
                  <div class="row">
                    <div class="col-md-4">
                      {{ $t("general.salaryGetPaymentRegistry") }}
                    </div>
                    <div class="col-md-2">
                      <button type="button" :disabled="!salaryCreatePaymentRegistryResultInfo?.created_at" class="btn btn-sm btn-primary me-3" @click="getSalaryGetPaymentRegistry">{{ $t('general.getResult') }}</button>
                    </div>
                    <div class="col-md-6">
                      <pre v-if="salaryGetPaymentRegistry?.status">
                        {{ salaryGetPaymentRegistry }}
                      </pre>
                    </div>
                  </div>
                </div>

                <div class="col-md-12 mb-3">
                  <div class="row">
                    <div class="col-md-4">
                      {{ $t("general.salaryPaymentRegistrySubmit") }}
                    </div>
                    <div class="col-md-2">
                      <button v-if="salaryPaymentRegistrySubmitInfo?.status == 'SUCCESS'" type="button" class="btn btn-sm btn-primary me-3" disabled>{{ $t('general.added') }}</button>
                      <button v-else type="button" class="btn btn-sm btn-primary me-3" @click="salaryPaymentRegistrySubmitPost">{{ $t('general.add') }}</button>
                    </div>
                    <div class="col-md-6">
                      <span v-if="salaryPaymentRegistrySubmitInfo?.created_at">{{ moment(salaryPaymentRegistrySubmitInfo.created_at).format("YYYY-MM-DD HH:mm:ss") }}</span>
                    </div>
                  </div>
                </div>

                <hr>

                <div class="col-md-12 mb-3">
                  <div class="row">
                    <div class="col-md-4">
                      {{ $t("general.salaryPaymentRegistrySubmitResult") }}
                    </div>
                    <div class="col-md-2">
                      <button type="button" v-if="salaryPaymentRegistrySubmitInfo?.status == 'SUCCESS'" class="btn btn-sm btn-primary me-3" @click="getSalaryPaymentRegistrySubmitResult(addRecipientInfo?.received_correlation_id)">{{ $t('general.getResult') }}</button>
                      <button type="button" v-else class="btn btn-sm btn-primary me-3" disabled>{{ $t('general.getResult') }}</button>
                    </div>
                    <div class="col-md-6">
                      <pre v-if="salaryPaymentRegistrySubmitResult?.status">
                        {{ salaryPaymentRegistrySubmitResult }}
                      </pre>
                    </div>
                  </div>
                </div>

              </div>
            </div>
            <div class="modal-footer text-center">
              <div class="col-md-12 text-center">
                <button type="button" class="btn btn-secondary" @click="closeModal">{{ $t('general.revoke') }}</button>
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
import { Required, Email, MinLength } from '@/Utils/validationMessages'
import { useUserStatuses } from '@/Hooks/useUserStatuses'
import { ref, defineEmits } from 'vue'
import { notify } from '@kyvg/vue3-notification'
import { router } from '@inertiajs/vue3'
import moment from 'moment'

const props = defineProps({
  user: Object,
  redirectURL: String
});

const emit = defineEmits(['close'])

const errorMsg = ref('')
const userStatuses = ref({})

const addRecipientInfo = ref({})
const addRecipientResult = ref({})

const salaryCreateEmployeeInfo = ref({})
const salaryCreateEmployeeResult = ref({})

const salaryGetEmployeeListResult = ref({})

const salaryCreatePaymentRegistryInfo = ref({})
const salaryCreatePaymentRegistryResultInfo = ref({})

const salaryGetPaymentRegistryCreateResult = ref({})
const salaryGetPaymentRegistry = ref({})

const salaryPaymentRegistrySubmitInfo = ref({})

const salaryPaymentRegistrySubmitResult = ref({})

const form = useForm({
  user_id: props?.user?.id,
});

const rules = {
  user_id: { Required },
};

const v$ = useVuelidate(rules, form);

const getInfo = () => {
  axios.get(route('admin.get_operations_info', {'user_id': props.user.id})).then((response) => {
    addRecipientInfo.value = response?.data?.addRecipientInfo
    salaryCreateEmployeeInfo.value = response?.data?.salaryCreateEmployeeInfo
    salaryCreatePaymentRegistryInfo.value = response?.data?.salaryCreatePaymentRegistryInfo
    salaryCreatePaymentRegistryResultInfo.value = response?.data?.salaryCreatePaymentRegistryResultInfo
    salaryPaymentRegistrySubmitInfo.value = response?.data?.salaryPaymentRegistrySubmitInfo
  }).catch(error => {

  });
}

getInfo()

const addEmployeeRecipient = () => {
  v$.value.$touch();
  if (v$._value.$invalid) return false;

  axios.post(route('admin.add_employee_recipient_post'), form).then((response) => {
    // closeModal()
    // router.get(window.location.href)
    notify({
      title: response.data.message,
      type: 'success'
    })
    getInfo()
  }).catch(error => {
    errorMsg.value = error.response.data.message
  });
}

const addSalaryCreateEmployee = () => {
  v$.value.$touch();
  if (v$._value.$invalid) return false;

  axios.post(route('admin.salary_create_employee_post'), form).then((response) => {
    notify({
      title: response.data.message,
      type: 'success'
    })
    getInfo()
  }).catch(error => {
    errorMsg.value = error.response.data.message
  });
}

const getEmployeeRecipientResult = (correlationId) => {
  axios.get(route('admin.add_employee_recipient_result', {'correlation_id': correlationId})).then((response) => {
    addRecipientResult.value = response.data.employeesResults
  }).catch(error => {

  });
}

const getSalaryCreateEmployeeResult = (correlationId) => {
  axios.get(route('admin.get_salary_create_employee_result', {'correlation_id': correlationId})).then((response) => {
    salaryCreateEmployeeResult.value = response.data?.employeeResults
  }).catch(error => {

  });
}

const salaryGetEmployeeList = () => {
  axios.post(route('admin.salary_get_employee_list_post'), {'user_id': props.user?.id}).then((response) => {
    salaryGetEmployeeListResult.value = response.data?.employees
  }).catch(error => {

  });
}

const addSalaryCreatePaymentRegistry = () => {
  v$.value.$touch();
  if (v$._value.$invalid) return false;

  axios.post(route('admin.salary_create_payment_registry_post'), form).then((response) => {
    notify({
      title: response.data.message,
      type: 'success'
    })
    getInfo()
  }).catch(error => {
    errorMsg.value = error.response.data.message
  });
}

const salaryPaymentRegistrySubmitPost = () => {
  v$.value.$touch();
  if (v$._value.$invalid) return false;

  console.log(salaryCreatePaymentRegistryResultInfo.value.response_log)

  const paymentRegistryId = JSON.parse(salaryCreatePaymentRegistryResultInfo.value.response_log)?.paymentRegistryId

  // console.log(paymentRegistryId, addRecipientInfo.value.sended_correlation_id)

  axios.post(route('admin.salary_payment_registry_submit_post'), { paymentRegistryId: paymentRegistryId, correlationId: addRecipientInfo.value.sended_correlation_id, 'userId': props.user.id }).then((response) => {
    notify({
      title: response.data.message,
      type: 'success'
    })
    getInfo()
  }).catch(error => {
    errorMsg.value = error.response.data.message
  });
}


const getSalaryGetPaymentRegistryCreateResult = (correlationId) => {
  axios.get(route('admin.salary_get_payment_registry_create_result', {'correlation_id': correlationId, 'user_id': props.user.id})).then((response) => {
    salaryGetPaymentRegistryCreateResult.value = response.data
    getInfo()
  }).catch(error => {

  });
}

const getSalaryGetPaymentRegistry = () => {
  const paymentRegistryId = JSON.parse(salaryCreatePaymentRegistryResultInfo.value.response_log)?.paymentRegistryId
  axios.get(route('admin.salary_get_payment_registry', {'payment_registry_id': paymentRegistryId})).then((response) => {
    salaryGetPaymentRegistry.value = response.data
  }).catch(error => {

  });
}

const getSalaryPaymentRegistrySubmitResult = (correlationId) => {
  axios.get(route('admin.salary_payment_registry_submit_result', {'correlation_id': correlationId, 'user_id': props.user.id})).then((response) => {
    salaryPaymentRegistrySubmitResult.value = response.data
  }).catch(error => {

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

</script>
