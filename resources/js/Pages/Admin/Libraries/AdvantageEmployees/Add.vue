<template>
  <Head :title="title" />

  <div class="card mb-4 card-table-list">
    <div class="card-header">
      <font-awesome-icon icon="table" />
      {{ title }}
      <Link class="edit-action-link" :href="route('admin.advantage_employees.index')">
        <button type="button" class="btn float-end btn-primary">
          <font-awesome-icon icon="arrow-left" />
        </button>
      </Link>
    </div>
    <div class="card-body card-table-list">
      <form @submit.prevent="submitForm" @keyup.13="submitForm">
        <div class="mb-3"  v-for="(lang, index) in $page.props.languages" :key="lang.id">
          <label :for="`name-${lang.code}`" class="form-label">
            {{ $t("general.title") }}
            <template v-if="$page.props.languages.length > 1">({{ lang.name }})</template>
          </label>
          <input
            type="text"
            class="form-control"
            v-model="advantageEmploye.name[lang.code]"
            :id="`name-${lang.code}`"
            :class="{ 'is-invalid': v$?.name?.[lang.code]?.$errors.length }"
          >
          <div class="invalid-feedback">
            {{
              v$?.name?.[lang.code]?.$errors?.[0]?.$message || v$?.name?.[lang.code]?.$errors?.[0]?.$params?.message
            }}
          </div>
        </div>

        <div class="mb-3">
          <label for="icon" class="form-label">
            {{ $t("general.icon") }}
          </label>
          <input
            type="file"
            class="form-control d-none"
            id="icon"
            @change="updatePhotoPreview"
            ref="icon"
            :class="{ 'is-invalid': v$?.icon.$errors.length }"
          >
          <input
            v-if="!imgPreview && !advantageEmploye?.icon"
            type="text"
            @click="openUploadFiles"
            value="Выбрать изображение"
            readonly
            class="form-control cursor-pointer"
          >
          <div class="invalid-feedback">
            {{
              v$?.icon?.$errors?.[0]?.$message || v$?.icon?.$errors?.[0]?.$params?.message
            }}
          </div>
          <div v-show="iconPreview || advantageEmploye?.icon">
            <div class="image-block">
              <img class="choosed-image object-cover" :src="iconPreview ? iconPreview : advantageEmploye?.icon ?  '/storage/' + advantageEmploye?.icon : ''">
              <button v-if="imgPreview || advantageEmploye?.icon" type="button" class="btn-sm btn-primary mt-1 d-inline" @click="openUploadFiles">
                {{ $t("general.change") }}
              </button>
              <button v-if="iconPreview || advantageEmploye?.icon" type="button" class="btn-sm btn-danger mt-1" @click="removePreview">
                {{ $t("general.delete") }}
              </button>
            </div>
          </div>
        </div>
        <button type="submit" class="btn btn-primary">{{ $t("general.save") }}</button>
      </form>
    </div>
  </div>
</template>

<script setup>
import Layout from '@/Layouts/Admin/AppLayout.vue'
import { Inertia } from '@inertiajs/inertia'
import useVuelidate from '@vuelidate/core'
import { Required, MinLength } from '@/Utils/validationMessages'
import { helpers } from '@vuelidate/validators'
import { reactive, ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

defineOptions({ layout: Layout })

const props = defineProps({
  title: String,
  data: Object
})

const icon = ref(null)
const iconPreview = ref(null)
const defaultIconPreview = ref(null)

const advantageEmploye = useForm({
  _method: route().current('admin.advantage_employees.create') ? 'POST' : 'PUT',
  id: props?.data?.id,
  icon: props?.data?.icon,
  delete_icon: false,
  name: {
    'ru': props.data?.name?.ru,
  },
})

const rules = {
  name: {
    ru: { Required },
  },
  icon: {  }
}

const v$ = useVuelidate(rules, advantageEmploye)

const submitForm = () => {
  v$.value.$touch()
  if (v$._value.$invalid) return false

  route().current('admin.advantage_employees.create')
    ? advantageEmploye.post(route('admin.advantage_employees.store'), advantageEmploye)
    : advantageEmploye.post(route('admin.advantage_employees.update', advantageEmploye.id), advantageEmploye)
}

const updatePhotoPreview = (event) => {
  const file = icon.value.files[0]

  const reader = new FileReader();
  reader.onload = (e) => {
    iconPreview.value = e.target.result;
    advantageEmploye.icon = file;
    event.target.value = "";
  }

  reader.readAsDataURL(file);
}

const removePreview = (event) => {
  iconPreview.value = defaultIconPreview.value = advantageEmploye.icon = icon.value = null
  advantageEmploye.delete_icon = true
}

const openUploadFiles = () => {
  document.getElementById('icon').click()
}
</script>
