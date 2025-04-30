<template>
  <Head :title="title" />
  <ModalDelete
    :show="deleteModal"
    @close="deleteModal.isOpen.value = false"
  />

  <div class="card mb-4">
    <div class="card-body">
      <div class="row bg-white align-items-center">
        <div class="col-md-4">
          <span class="fw-bold text-success">{{ title }}</span>
        </div>
        <div class="col-md-4"></div>
        <div class="col-md-4">
          <Link class="edit-action-link" :href="route('admin.permissions.create')">
            <button type="button" class="btn float-end btn-success">
              <font-awesome-icon icon="plus" />
              {{ $t("general.add") }}
            </button>
          </Link>
        </div>
      </div>
    </div>
  </div>

  <div class="mt-3">
    <div class="card mt-3" v-for="permission in permissions" :key="permission.id">
      <div class="card-body p-3 position-relative">
        <div class="row bg-white align-items-center">
          <div class="col-md-1 col-sm-12 border-end">{{ permission.id }}</div>
          <div class="col-md-2 col-sm-12 border-end">{{ usePermissionsText(permission.type) }}</div>
          <div class="col-md-7 col-sm-12 border-end">{{ permission.name.ru }}</div>
          <div class="col-md-2 col-sm-12 actions">
            <Link
            class="edit-action-link"
            :href="route('admin.permissions.edit', permission.id)"
          >
            <font-awesome-icon icon="pen-to-square" class="edit" />
          </Link>
          <font-awesome-icon
            icon="trash"
            class="trash"
            @click="
              (deleteModal.isOpen.value = true),
              (deleteModal.data = permission),
              (deleteModal.url.value = 'admin.permissions.destroy')
            "
          />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import Layout from '@/Layouts/Admin/AppLayout.vue'
import { useModalDelete } from '@/Hooks/useModalDelete'
import { usePermissionsText } from '@/Hooks/usePermissionsText'

defineOptions({ layout: Layout })

defineProps({
  title: String,
  permissions: Object
})

const deleteModal = useModalDelete()
</script>

