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
              <h4 class="modal-title">{{ $t("general.moderators") }}</h4>
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
                    <th>{{ $t("general.email") }}</th>
                    <th>{{ $t("general.phone") }}</th>
                    <th>{{ $t("general.branche") }}</th>
                  </thead>
                  <tbody v-if="moderators.length > 0">
                    <tr class="table-active" v-for="moderator in moderators" :key="moderator.id">
                      <td>{{ moderator?.name }}</td>
                      <td>{{ moderator?.email }}</td>
                      <td>{{ moderator?.phone }}</td>
                      <td>{{ branche?.title_n }}</td>
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
import { ref, defineEmits } from 'vue'
import moment from 'moment'

const props = defineProps({
  branche: Object,
  redirectURL: String
});

const emit = defineEmits(['close'])

const errorMsg = ref('')
const moderators = ref([])

const onClickAway = (event) => {
  if(event.target.classList.contains('modal')) {
    emit("close")
  }
}

const getModerators = () => {
  axios.get(route('get_moderators_by_branche', {'branche_id': props.branche.id})).then((response) => {
    if(response.data.status == 'success')
      moderators.value = response.data.moderators
  }).catch(error => {

  });
}

getModerators()

const closeModal = () => {
  emit("close")
}

</script>
