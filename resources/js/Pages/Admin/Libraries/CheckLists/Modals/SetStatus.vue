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
              <h4 class="modal-title">{{ type == 'CONFIRMED' ? $t("general.confirm") : $t("general.revoke") }} ?</h4>
              <button
                type="button"
                class="btn-close d-inline-block"
                @click="closeModal"
              ></button>
            </div>
            <div class="modal-body" v-if="errorMsg || type == 'CANCELED'">
              <div
                v-if="errorMsg"
                class="alert alert-danger alert-dismissible ml-0 mr-0 mb-2"
                role="alert"
              >
                {{ errorMsg }}
              </div>

              <div class="col-md-12" v-if="type == 'CANCELED'">
                <textarea
                  class="form-control"
                  rows="4"
                  v-model="form.reason"
                  :placeholder="`${$t('general.reason')}`"
                ></textarea>
              </div>
            </div>
            <div class="modal-footer text-center">
               <div class="col-md-12 text-center">
                <button v-if="type == 'CONFIRMED'" type="button" class="btn btn-primary me-3" @click="submit">{{ $t('general.confirm') }}</button>
                <button v-if="type == 'CANCELED'" type="button" class="btn btn-danger" @click="submit">{{ $t('general.revoke') }}</button>
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

const props = defineProps({
  job: Object,
  type: String,
});

const emit = defineEmits(['close'])

const errorMsg = ref('')

const form = ref({
  job: props?.job,
  type: props?.type,
  reason: '',
});

const submit = (status) => {
  const routeUrl = route('admin.set_check_list_bonuses')

  const data = {
    status: props?.type,
    company_id: props?.job?.company_id,
    employee_id: props?.job?.jb_user_id,
    job_id: props?.job?.id,
    amount: props?.job?.bonus,
    reason: form?.value.reason
  }



  axios.post(routeUrl, data).then((response) => {
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

const closeModal = () => {
  emit("close")
}
</script>
