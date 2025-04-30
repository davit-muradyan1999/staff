<template>
  <Teleport to="body">
    <div
      style="display: block; z-index: 999999;"
      class="modal fade show modal-marker-job"
      id="addModeratorModal"
      tabindex="-1"
      aria-labelledby="addModeratorModalLabel"
      aria-hidden="true"
    >
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
          <div class="modal-header text-center">
            <button
              type="button"
              class="btn-close d-inline-block"
              @click="closeModal"
            ></button>
          </div>
          <div class="modal-body">
            <JobInfo :key="job.id" :job="job" :searchURL="''" :editable="editable" :showFavorite="false" :showMorePopup="true" />
          </div>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade show" v-click-away="onClickAway"></div>
  </Teleport>
</template>

<script setup>
import { ref, defineEmits } from 'vue'
import JobInfo from '@/Components/JobInfo.vue'

const props = defineProps({
  job: Object,
  editable: Boolean
});

const emit = defineEmits(['close'])

const errorMsg = ref('')

const onClickAway = (event) => {
  if(event.target.classList.contains('modal-marker-job')) {
    emit("close")
  }
}

const closeModal = () => {
  emit("close")
}

</script>
