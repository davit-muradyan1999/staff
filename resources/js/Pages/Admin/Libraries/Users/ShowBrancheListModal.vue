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
              <h4 class="modal-title">{{ $t("general.specialVacancies") }}</h4>
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
                    <th>{{ $t("general.branche") }}</th>
                  </thead>
                  <tbody>
                    <tr v-for="row in userBranche?.branches" :key="row.id">
                      <td class="ps-0">{{ row?.title_b }}</td>
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
  user: Object,
});

const emit = defineEmits(['close'])

const userBranche = ref([])
const errorMsg = ref('')

const onClickAway = (event) => {
  if(event.target.classList.contains('modal')) {
    emit("close")
  }
}

const getLists = () => {
  axios.get(route('admin.get_user_branche_lists', {'user_id': props.user.user_table_id})).then((response) => {
    if(response.data.status == 'success')
      userBranche.value = response.data.userBranche
  }).catch(error => {

  });
}

getLists()

const closeModal = () => {
  emit("close")
}

</script>
