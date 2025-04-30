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
      <div class="modal-dialog">
        <div class="modal-content">
          <form>
            <div class="modal-header text-center">
              <h4 class="modal-title">{{ $t("general.pay") }} ?</h4>
              <button
                type="button"
                class="btn-close d-inline-block"
                @click="closeModal"
              ></button>
            </div>
            <div class="modal-body">
              <div
                v-if="errorMsg"
                class="alert alert-danger alert-dismissible ml-0 mr-0 mb-2 w-full"
                role="alert"
              >
                {{ errorMsg }}
              </div>

              <p><span class="fw-bold">{{ $t('general.totalAmount') }}</span> - <span class="text-success fw-bold">{{ setTotalAmount() }} {{ $t('general.rub') }}</span></p>
              <div class="col-md-12">
                <textarea
                  class="form-control"
                  rows="4"
                  v-model="form.note"
                  :class="{ 'is-invalid': v$.note.$errors.length }"
                  :placeholder="`${$t('general.target')}`"
                ></textarea>
                <div class="invalid-feedback">
                  {{
                    v$?.note?.$errors?.[0]?.$message || v$?.note?.$errors?.[0]?.$params?.message
                  }}
                </div>
              </div>
            </div>
            <div class="modal-footer text-center">
                <div class="col-md-12 text-center">
                <button type="button" class="btn btn-success me-3" @click="submit">{{ $t('general.pay') }}</button>
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
import { ref, defineEmits } from 'vue'
import { notify } from '@kyvg/vue3-notification'
import { router } from '@inertiajs/vue3'
import { useFixedNumber } from '@/Hooks/useFixedNumber'
import useVuelidate from '@vuelidate/core'
import { Required } from '@/Utils/validationMessages'

const props = defineProps({
  listIds: Object,
  employeeIds: Object,
  notPaidEmployees: Object,
});

const emit = defineEmits(['close'])

const errorMsg = ref('')

const form = ref({
  note: ''
});

const rules = {
  note: { Required },
};

const v$ = useVuelidate(rules, form);

const submit = () => {
  v$.value.$touch();
  if (v$._value.$invalid) return false;

  const getData = localStorage.getItem("notPaids")

  if(props.employeeIds.length > 0) {
    let data = null;
    if(getData) {
      data = JSON.parse(getData)
    }

    axios.post(route('admin.employee_balance_confirm'), {
      data: data,
      employeeIds: props.employeeIds,
      note: form.value.note
    }).then((response) => {
        if(response.data.status == 'success') {
          notify({
            title: response.data.message,
            type: 'success'
          })
          localStorage.removeItem("notPaids")
          closeModal()
          router.get(window.location.href)
        }
      }).catch(error => {

      });
  }
}

const setTotalAmount = () => {
  // let getData = localStorage.getItem("notPaids");
  // let totalSum = 0;

  // console.log(props.employeeIds)
  // console.log(props.notPaidEmployees)

  // if(getData) {
  //   JSON.parse(getData).forEach(obj => {
  //     totalSum += +obj.amount;
  //   })
  // }

  let totalSum = 0;

  props.notPaidEmployees.data.forEach(obj => {
    if(props.employeeIds.includes(obj.id)) {
      totalSum += +obj.not_paid_sum;
    }
  })

  return useFixedNumber(totalSum)
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
