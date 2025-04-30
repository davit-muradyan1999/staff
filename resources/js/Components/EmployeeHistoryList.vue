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
      <div class="modal-dialog modal-xl modal-xl-size">
        <div class="modal-content">
          <form @submit.prevent="submit">
            <div class="modal-header text-center">
              <h4 class="modal-title">{{ $t("general.history") }}</h4>
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
                <div class="row align-items-center h-100 p-2 bg-white border rounded mt-2" style="margin-left: 0;" :class="job.bl_status == 'ADDED' ? 'black-list-row' : ''"
                   v-for="job in historyList" :key="job.id"
                >
                  <div class="col-md-1 mt-1">
                    <img class="img-fluid img-responsive img-rounded" src="/images/user.png">
                  </div>
                  <div class="col-md-3 mt-1">
                    <div class="border-end">
                      <p>{{ job?.user_name }}</p>
                    </div>
                  </div>
                  <div class="col-md-3 mt-1">
                    <div class="border-end">
                      <p>
                        <a class="text-decoration-none" target="_blank" :href="route('company_jobs_show_list', job.id)">
                          {{ job?.establishment?.name_e }} <span class="job-id">#{{ job.id }}</span>
                        </a>
                      </p>
                    </div>
                  </div>
                  <div class="col-md-3 mt-1">
                    <div class="border-end">
                      <p>{{ job?.branche?.title_b || job?.establishment?.user?.company_name }}</p>
                    </div>
                  </div>
                  <div class="col-md-2 mt-1">
                    <div class="">
                      <p>{{ $t('general.accepted') }} {{ job?.jb_created_at }}</p>
                    </div>
                  </div>
                </div>
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
import EmployeeList from '@/Components/EmployeeList.vue'

const props = defineProps({
  job: Object,
  redirectURL: String
});

const emit = defineEmits(['close'])

const errorMsg = ref('')
const historyList = ref({})

const onClickAway = (event) => {
  if(event.target.classList.contains('modal')) {
    emit("close")
  }
}

const getEmployeeStatuses = () => {
  axios.get(route('get_employee_history', {'employee_id': props?.job?.jb_user_id})).then((response) => {
    if(response.data.status == 'success')
      historyList.value = response.data.historyList
  }).catch(error => {

  });
}

getEmployeeStatuses()

const closeModal = () => {
  emit("close")
}
</script>
