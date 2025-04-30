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
                <table class="table" v-if="blackLists.length > 0">
                  <thead>
                    <th>{{ $t("general.status") }}</th>
                    <th>{{ $t("general.reason") }}</th>
                    <th>{{ $t("general.hasCreated") }}</th>
                    <th>{{ $t("general.created") }}</th>
                  </thead>
                  <tbody>
                    <tr
                      class="table-active"
                      v-for="list in blackLists"
                      :key="list.id"
                      :class="{'table-success': list.status == 'REMOVED', 'table-danger': list.status == 'ADDED'}"
                    >
                      <td>
                        <template v-if="list.status == 'ADDED'">{{ $t("general.added") }}</template>
                        <template v-else-if="list.status == 'REMOVED'">{{ $t("general.hasBeenRemoved") }}</template>
                      </td>
                      <td>{{ list?.reason }}</td>
                      <td>{{ list?.created_user?.name }}</td>
                      <td>{{ moment(list?.created_at).format("YYYY-MM-DD HH:mm:ss") }}</td>
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
  employee: Object,
  redirectURL: String
});

const emit = defineEmits(['close'])

const errorMsg = ref('')
const blackLists = ref({})

const onClickAway = (event) => {
  if(event.target.classList.contains('modal')) {
    emit("close")
  }
}

const getEmployeeStatuses = () => {
  const employeeID = props?.employee?.jb_user_id ? props.employee.jb_user_id : props?.employee?.id
  const companyID = props?.employee?.company_id ? props.employee.company_id : ''

  let data = {
    'employee_id': employeeID
  }

  if(companyID)
    data.company_id = companyID

  axios.get(route('get_employee_black_list_statuses', data)).then((response) => {
    if(response.data.status == 'success')
      blackLists.value = response.data.blackLists
  }).catch(error => {

  });
}

getEmployeeStatuses()

const closeModal = () => {
  emit("close")
}
</script>
