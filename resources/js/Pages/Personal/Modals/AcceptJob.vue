<template>
  <Teleport to="body">
    <div
      style="display: block; z-index: 9999999;"
      class="modal fade show accept-modal"
      id="addModeratorModal"
      tabindex="-1"
      aria-labelledby="addModeratorModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <form @submit.prevent="submit">
            <div class="modal-header text-center">
              <h4 class="modal-title">{{ $t("general.confirmAction") }}</h4>
              <button
                type="button"
                class="btn-close d-inline-block"
                @click="closeModal"
              ></button>
            </div>
            <div class="modal-body" v-if="errorMsg">
              <div
                v-if="errorMsg"
                class="alert alert-danger alert-dismissible ml-0 mr-0 mb-2"
                role="alert"
              >
                {{ errorMsg }}
              </div>
              <div class="row g-3">
              </div>
            </div>
            <div class="modal-footer text-center">
              <div class="col-md-12 text-center">
                <button type="submit" class="btn me-3" :class="job?.applications[0]?.id ? 'btn-danger' : 'btn-success'">{{ job?.applications[0]?.id ? $t('general.leave') : $t('general.accept') }}</button>
                <button type="button" class="btn btn-secondary" @click="closeModal">{{ $t('general.revoke') }}</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade show" v-click-away="onClickAway" style="z-index: 999999;"></div>
  </Teleport>
</template>

<script setup>
import axios from 'axios'
import { useForm } from '@inertiajs/vue3'
import useVuelidate from '@vuelidate/core'
import { Required, Email, MinLength } from '../../../Utils/validationMessages'
import { ref, defineEmits } from 'vue'
import { notify } from '@kyvg/vue3-notification'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  job: Object,
});

const emit = defineEmits(['close'])

const errorMsg = ref('')

const form = useForm({
  id: props?.job?.id,
  application_id: props?.job?.applications[0]?.id
});

const rules = {

};

const v$ = useVuelidate(rules, form);

const submit = () => {
  v$.value.$touch();
  if (v$._value.$invalid) return false;

  axios.post(route('accept_job'), form).then((response) => {
    notify({
      title: response.data.message,
      type: 'success'
    })
    closeModal()
    router.get(route('active_works'))
  }).catch(error => {
    errorMsg.value = error.response.data.message
  });
}

const onClickAway = (event) => {
  if(event.target.classList.contains('accept-modal')) {
    console.log(123)
    emit("close")
  }
}

const closeModal = () => {
  emit("close")
}

</script>
