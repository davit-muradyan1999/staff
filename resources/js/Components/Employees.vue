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
              <h4 class="modal-title">{{ $t("general.employees") }}</h4>
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

              <div class="mb-3 row align-items-center">
                <div class="col-sm-12">
                  <Multiselect
                    v-model="form.employees"
                    mode="tags"
                    valueProp="id"
                    :options="employees"
                    :searchable="true"
                    label="name"
                    track-by="name"
                    :class="{ 'is-invalid': v$.employees.$errors.length }"
                  />

                  <div class="invalid-feedback">
                    {{ v$?.employees?.$errors?.[0]?.$message || v$?.employees?.$errors?.[0]?.$params?.message }}
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer text-center">
              <div class="col-md-12 text-center">
                <button type="submit" class="btn btn-primary me-3">{{ $t('general.save') }}</button>
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
import { ref, defineEmits } from 'vue'
import { notify } from '@kyvg/vue3-notification'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  job: Object,
});

const emit = defineEmits(['close'])

const errorMsg = ref('')
const employees = ref({})

const form = useForm({
  status: '',
  employees: [],
  job_id: props?.job?.id,
  company_id: props?.job?.company_id,
  branche_id: props?.job?.branche_id,
});

let passwordRules = {MinLength: MinLength(7)}

const rules = {
  employees: { Required },
};

const v$ = useVuelidate(rules, form);

const submit = () => {
  v$.value.$touch();
  if (v$._value.$invalid) return false;

  axios.post(route('admin.set_employees'), form).then((response) => {
    notify({
      title: response.data.message,
      type: 'success'
    })
    closeModal()
    router.get(window.location.href)
  }).catch(error => {
    errorMsg.value = error.response.data.message
  });
}

const onClickAway = (event) => {
  if(event.target.classList.contains('modal')) {
    emit("close")
  }
}

const getAllEmployees = () => {
  axios.get(route('admin.get_all_employees', {job_id: props?.job?.id, ignore_existing_employees: true})).then((response) => {
    if(response.data.status == 'success')
      employees.value = response.data.employees
  }).catch(error => {

  });
}

getAllEmployees()

const deleteModerator = () => {

}

const closeModal = () => {
  emit("close")
}

</script>
