<template>
  <div
    v-if="successMessages"
    class="alert alert-success alert-dismissible fade show m-0 mb-2 alert-message"
    role="alert"
  >
    {{ successMessages }}
    <button
      type="button"
      class="btn-close"
      @click="closeMessage"
    ></button>
  </div>
  <div
    v-if="errorMessage"
    class="alert alert-danger alert-dismissible m-0 mb-2 alert-message"
    role="alert"
  >
    {{ errorMessage }}
    <!-- <button
      type="button"
      @click="closeMessage"
    ></button> -->
  </div>
  <div
    class="alert alert-danger alert-dismissible fade show m-0 mb-2 alert-message"
    role="alert"
    v-if="errorMessages && Object.keys(errorMessages).length > 0"
  >
    <div
      v-if="errorMessages"
      v-for="error in errorMessages"
      @scrollUp="scrollUp"
    >
      <div v-if="Object.keys(error)[0] != 0" v-for="e in error">{{ e }}</div>
      <div v-else>{{ error }}</div>
    </div>
    <!-- <button
      type="button"
      @click="closeMessage"
    ></button> -->
  </div>
</template>

<script>
import { computed, watchEffect, ref } from 'vue'
import { usePage } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3'

export default {
  setup() {

    const a = computed(() => usePage().props?.successMessage)
    const b = computed(() => usePage().props?.errorMessage)
    const c = computed(() => usePage().props?.errors)

    const successMessages = ref(a)
    const errorMessage = ref(b)
    const errorMessages = ref(c)

    watchEffect(() => {
      setTimeout(() => {
        // successMessages.value = ''
        // errorMessage.value = ''
        // errorMessages.value = ''
      }, 1000);

      if (errorMessages || errorMessage) {
        const root = document.getElementById('content-wrapper');
        if(root !== null) {
          root.scrollTo({
          top: 0,
          left: 0,
          behavior: 'smooth',
        });
        }
      }
    })

    const closeMessage = () => {
      router.get(window.location)
    }

    return {
      successMessages,
      errorMessage,
      errorMessages,
      closeMessage
     }
  }
}
</script>
