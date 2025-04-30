<template>
  <template v-if="isOpen">
    <Teleport to="body">
      <div
        ref="deleteModal"
        class="modal fade show"
        id="exampleModal"
        tabindex="-1"
        aria-labelledby="exampleModalLabel"
        aria-hidden="true"
        :style="[isOpen ? {'display': 'block'} : {'display': 'none'}]"
      >
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLabel">
                <font-awesome-icon icon="trash" style="color: red" />
                {{ $t('general.confirmAction') }}
              </h5>
            </div>
            <div class="modal-body">
              <p>
                {{ $t('general.confirmDelete') }}
                <strong v-if="show.data.name?.ru">({{ show.data.name?.ru }})</strong>
                <strong v-else-if="show.data.title?.ru">({{ show.data.title?.ru }})</strong>
              </p>
            </div>
            <div class="modal-footer">
              <button
                type="button"
                class="btn btn-danger"
                data-bs-dismiss="modal"
                @click="deleteData"
              >
                {{ $t('general.yes') }}
              </button>
              <button type="button" class="btn btn-primary" @click="closeModal">{{ $t('general.no') }}</button>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-backdrop fade show" v-click-away="onClickAway"></div>
    </Teleport>
  </template>
</template>
<script>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3';

export default {
  emits: ["close"],
  props: {
    show: Object,
  },
  setup(props, { emit }) {
    const isOpen = ref(props.show.isOpen)

    const onClickAway = (event) => {
      if(event.target.classList.contains('modal')) {
        isOpen.value = false
        emit("close")
      }
    }

    const form = useForm({});

    const deleteData = () => {
      form.delete(route(props.show.url.value, props.show.data.id), {
        onFinish: () => {
           isOpen.value = false
           emit("close")
        },
      })
    }

    const closeModal = (event) => {
      isOpen.value = false
      emit("close")
    }

    return {
      isOpen,
      onClickAway,
      closeModal,
      deleteData
    }
  }
};
</script>
