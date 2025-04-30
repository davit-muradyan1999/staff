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
              <h4 class="modal-title">{{ $t("general.employeeList") }}</h4>
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
                <table class="table">
                  <thead>
                    <th>{{ $t("general.firstName") }}</th>
                    <th>{{ $t("general.phone") }}</th>
                    <th>{{ $t("general.dateOfApplication") }}</th>
                  </thead>
                  <tbody>
                    <tr v-for="row in data" :key="row.id">
                      <td class="ps-0">
                        <a class="text-decoration-none" target="_blank" :href="route('profile', { id: row?.user?.id })">
                          {{ row?.user?.name }}
                        </a>
                      </td>
                      <td class="ps-0">{{ row?.user?.phone }}</td>
                      <td class="ps-0">{{ moment(row?.created_at).format("YYYY-MM-DD HH:mm:ss") }}</td>
                    </tr>
                  </tbody>
                </table>
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
import { ref, defineEmits } from 'vue'
import { notify } from '@kyvg/vue3-notification'
import { router } from '@inertiajs/vue3'
import moment from 'moment'

const props = defineProps({
  jobID: Number,
});

const emit = defineEmits(['close'])

const data = ref([])
const errorMsg = ref('')

const onClickAway = (event) => {
  if(event.target.classList.contains('modal')) {
    emit("close")
  }
}

const getLists = () => {
  axios.get(route('company_jobs_get_worker_lists', {'id': props.jobID})).then((response) => {
    if(response.data.status == 'success')
      data.value = response.data.data
  }).catch(error => {

  });
}

getLists()

const closeModal = () => {
  emit("close")
}

</script>
