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
              <h4 class="modal-title">{{ $t("general.updateStatus") }}</h4>
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
                <table class="table" v-if="userStatuses.length > 0">
                  <thead>
                    <th>{{ $t("general.status") }}</th>
                    <th>{{ $t("general.created") }}</th>
                  </thead>
                  <tbody>
                    <tr
                      class="table-active"
                      v-for="status in userStatuses"
                      :key="status.id"
                      :class="{'table-warning': status.status == 'PROGRESS', 'table-success': status.status == 'CONFIRMED', 'table-danger': status.status == 'REJECTED'}"
                    >
                      <td>
                        <template v-if="status.status == 'PROGRESS'">{{ $t("general.progress") }}</template>
                        <template v-else-if="status.status == 'CONFIRMED'">{{ $t("general.confirmed") }}</template>
                        <template v-else-if="status.status == 'REJECTED'">{{ $t("general.rejected") }}</template>
                      </td>
                      <td>{{ moment(status?.created_at).format("YYYY-MM-DD HH:mm:ss") }}</td>
                    </tr>
                  </tbody>
                </table>

                <div class="col-md-12 mt-0 mb-3">
                  <label for="countryId" class="form-label">
                    {{ $t("general.updateStatus") }}
                  </label>
                  <Multiselect
                    valueProp="value"
                    v-model="form.status"
                    :options="userStatusData.statuses"
                    :searchable="true"
                    label="name"
                    track-by="name"
                    :placeholder="`${$t('general.status')}`"
                    :class="{ 'is-invalid': v$.status.$errors.length }"
                  />
                  <div class="invalid-feedback">
                    {{ v$?.status?.$errors?.[0]?.$message || v$?.status?.$errors?.[0]?.$params?.message }}
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

const userStatusData = useUserStatuses()

const form = useForm({
  status: '',
  user_id: props?.user?.id,
});

let passwordRules = {MinLength: MinLength(7)}

const rules = {
  status: { Required },
};

const v$ = useVuelidate(rules, form);

const submit = () => {
  v$.value.$touch();
  if (v$._value.$invalid) return false;

  axios.post(route('admin.add_user_status'), form).then((response) => {
    notify({
      title: response.data.message,
      type: 'success'
    })
    closeModal()
    router.get(props?.redirectURL)
  }).catch(error => {
    errorMsg.value = error.response.data.message
  });
}

const onClickAway = (event) => {
  if(event.target.classList.contains('modal')) {
    emit("close")
  }
}

const getUserStatuses = () => {
  axios.get(route('admin.get_user_statuses', {'user_id': props.user.id})).then((response) => {
    if(response.data.status == 'success')
      userStatuses.value = response.data.userStatuses
  }).catch(error => {

  });
}

getUserStatuses()

const deleteModerator = () => {

}

const closeModal = () => {
  emit("close")
}

</script>
