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
          <Link class="edit-action-link" :href="route('admin.districts.create')">
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
    <div class="card mt-3" v-for="district in districts" :key="district.id">
      <div class="card-body p-3 position-relative">
        <div class="row bg-white align-items-center">
          <div class="col-md-1 col-sm-12 border-end">{{ district.id }}</div>
          <div class="col-md-3 col-sm-12 border-end">{{ district?.country?.name?.ru }}</div>
          <div class="col-md-6 col-sm-12 border-end">{{ district.name.ru }}</div>
          <div class="col-md-2 col-sm-12 actions">
            <Link
            class="edit-action-link"
            :href="route('admin.districts.edit', district.id)"
          >
            <font-awesome-icon icon="pen-to-square" class="edit" />
          </Link>
          <font-awesome-icon
            icon="trash"
            class="trash"
            @click="
              (deleteModal.isOpen.value = true),
              (deleteModal.data = district),
              (deleteModal.url.value = 'admin.districts.destroy')
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

defineOptions({ layout: Layout })

defineProps({
  title: String,
  districts: Object
})

const deleteModal = useModalDelete()
</script>

