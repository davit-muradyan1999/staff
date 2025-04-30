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
          <form @submit.prevent="submit">
            <div class="modal-header text-center">
              <h4 class="modal-title">{{ job?.bl_status == 'ADDED' ? $t("general.removeBlackList") : $t("general.addBlackList") }}</h4>
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
              <div class="col-md-12">
                <p class="mb-1">{{ $t("general.reason") }}</p>
              </div>
              <div class="col-md-12">
                <textarea
                  class="form-control"
                  rows="4"
                  v-model="form.reason"
                  :class="{ 'is-invalid': v$.reason.$errors.length }"
                ></textarea>
                <div class="invalid-feedback">
                  {{
                    v$?.reason?.$errors?.[0]?.$message || v$?.reason?.$errors?.[0]?.$params?.message
                  }}
                </div>
              </div>
            </div>
            <div class="modal-footer text-center">
               <div class="col-md-12 text-center">
                <button type="submit" class="btn btn-primary me-3" >{{ job?.bl_status == 'ADDED' ? $t("general.return") : $t("general.save") }}</button>
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
import { defineEmits } from 'vue'
import useVuelidate from '@vuelidate/core'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import { ref } from 'vue'
import moment from 'moment'
import { Required } from '@/Utils/validationMessages'
import { notify } from '@kyvg/vue3-notification'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  job: Object,
  redirectURL: String
});

const errorMsg = ref('')

console.log(props?.job)

const form = ref({
  employee_id: props?.job?.jb_user_id,
  status: props?.job?.bl_status == 'ADDED' ? 'REMOVED' : 'ADDED',
  reason: ''
});

const rules = {
  reason: { Required },
};

const emit = defineEmits(['close'])

const v$ = useVuelidate(rules, form);

const submit = (status) => {
  v$.value.$touch();
  if (v$._value.$invalid) return false;

  axios.post(route('set_black_list'), form.value).then((response) => {
    console.log(response.data)
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

const onClickAway = (event) => {
  if(event.target.classList.contains('modal')) {
    emit("close")
  }
}

const closeModal = () => {
  emit("close")
}

</script>
