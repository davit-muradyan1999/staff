<template>
  <Head :title="title" />
  <ModalDelete
    :show="deleteModal"
    @close="deleteModal.isOpen.value = false"
  />
  <div class="card mb-4">
    <div class="card-body">
      <div class="row bg-white align-items-center">
        <div class="col-6">
          <span class="fw-bold text-success">{{ title }}</span>
        </div>
        <div class="col-6">
          <Link class="edit-action-link" :href="route('admin.language_skills.create')">
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
    <div class="card mt-3" v-for="languageSkill in languageSkills" :key="languageSkill.id">
      <div class="card-body p-3 position-relative">
        <div class="row bg-white align-items-center">
          <div class="col-md-1 col-sm-12 border-end">{{ languageSkill.id }}</div>
          <div class="col-md-9 col-sm-12 border-end">{{ languageSkill.name.ru }}</div>
          <div class="col-md-2 col-sm-12 actions">
            <Link
            class="edit-action-link"
            :href="route('admin.language_skills.edit', languageSkill.id)"
          >
            <font-awesome-icon icon="pen-to-square" class="edit" />
          </Link>
          <font-awesome-icon
            icon="trash"
            class="trash"
            @click="
              (deleteModal.isOpen.value = true),
              (deleteModal.data = languageSkill),
              (deleteModal.url.value = 'admin.language_skills.destroy')
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
  languageSkills: Object
})

const deleteModal = useModalDelete()
</script>

