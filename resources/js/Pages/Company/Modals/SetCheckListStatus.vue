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
              <h4 class="modal-title">{{ type == 'confirm' ? $t("general.confirm") : $t("general.cancelShift") }} ?</h4>
              <button
                type="button"
                class="btn-close d-inline-block"
                @click="closeModal"
              ></button>
            </div>
            <div class="modal-body" v-if="errorMsg">
              <div
                class="alert alert-danger alert-dismissible ml-0 mr-0 mb-2 w-full"
                role="alert"
              >
                {{ errorMsg }}
              </div>
            </div>
            <div class="modal-footer text-center">
               <div class="col-md-12 text-center">
                <button type="button" class="btn btn-primary me-3" @click="submit">{{ $t('general.confirm') }}</button>
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
import { Required, Email, MinLength, Numeric, ImageExtension } from '../../../Utils/validationMessages'
import { ref, defineEmits } from 'vue'
import { notify } from '@kyvg/vue3-notification'
import { router } from '@inertiajs/vue3'
import { DatePicker } from 'v-calendar'
import VueDatePicker from '@vuepic/vue-datepicker'
import moment from 'moment'

const props = defineProps({
  jobs: Object,
  job: Object,
  type: String,
  all: String,
  checkeds: Array,
});

const emit = defineEmits(['close'])

const errorMsg = ref('')
const showDesc = ref(true)

const form = ref({
  job: props?.job,
  jobs: props?.jobs,
  type: props?.type,
  all: props?.all,
  checkeds: props.checkeds
});

const submit = (status) => {
  // v$.value.$touch();
  // if (v$._value.$invalid) return false;

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
