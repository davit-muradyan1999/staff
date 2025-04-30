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
              <h4 class="modal-title">{{ !data?.id ? $t("general.attachCreditCard") : $t("general.attachCreditCard") }}</h4>
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
              <div class="row g-3">

              </div>
            </div>
            <div class="modal-footer text-center">
              <div class="col-md-12 text-center">
                <button type="submit" class="btn btn-primary me-3">{{ !data?.id ? $t('general.save') : $t('general.edit') }}</button>
                <button type="button" class="btn btn-secondary" @click="closeModal">{{ $t('general.revoke') }}</button>
              </div>
              <div class="col-md-12 text-center" v-if="data?.id">
                <a href="#" class="text" @click.prevent="deleteModerator"><i>{{ $t('general.deleteModerator') }}</i></a>
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
import { Required, Email, MinLength } from '../../../Utils/validationMessages'
import { ref, defineEmits } from 'vue'
import { notify } from '@kyvg/vue3-notification'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  data: Object,
});

const emit = defineEmits(['close'])

const errorMsg = ref('')

const form = useForm({
  id: props?.data?.id,
});

let passwordRules = {MinLength: MinLength(7)}

const rules = {

};

const v$ = useVuelidate(rules, form);

const submit = () => {
  v$.value.$touch();
  if (v$._value.$invalid) return false;


}

const onClickAway = (event) => {
  if(event.target.classList.contains('modal')) {
    emit("close")
  }
}

const deleteModerator = () => {

}

const closeModal = () => {
  emit("close")
}

</script>
