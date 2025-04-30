<template>
  <div class="modal fade" id="mailMessageModal" tabindex="-1" aria-labelledby="mailMessageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="mailMessageModalLabel">{{ $t("general.notification") }}</h5>
          <button type="button" id="close-modal" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div v-if="$page?.props?.successMessage" class="alert alert-success alert-dismissible fade show m-0 mb-2 alert-message" role="alert">
            {{ $page?.props?.successMessage }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
