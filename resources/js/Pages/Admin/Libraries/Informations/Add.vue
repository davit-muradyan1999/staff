<template>
  <Head :title="title" />

  <div class="card mb-4 card-table-list">
    <div class="card-header">
      <font-awesome-icon icon="table" />
      {{ title }}
      <Link class="edit-action-link" :href="route('admin.informations.index')">
        <button type="button" class="btn float-end btn-primary">
          <font-awesome-icon icon="arrow-left" />
        </button>
      </Link>
    </div>
    <div class="card-body card-table-list">
      <form @submit.prevent="submitForm" @keyup.13="submitForm">
        <div class="mb-3"  v-for="(lang, index) in $page.props.languages" :key="lang.id">
          <label :for="`title-${lang.code}`" class="form-label">
            {{ $t("general.heading") }}
            <template v-if="$page.props.languages.length > 1">({{ lang.name }})</template>
          </label>
          <input
            type="text"
            class="form-control"
            v-model="information.title[lang.code]"
            :id="`title-${lang.code}`"
            :class="{ 'is-invalid': v$?.title?.[lang.code]?.$errors.length }"
          >
          <div class="invalid-feedback">
            {{
              v$?.title?.[lang.code]?.$errors?.[0]?.$message || v$?.title?.[lang.code]?.$errors?.[0]?.$params?.message
            }}
          </div>
        </div>

        <div class="mb-3"  v-for="(lang, index) in $page.props.languages" :key="lang.id">
          <label :for="`name-${lang.code}`" class="form-label">
            {{ $t("general.title") }}
            <template v-if="$page.props.languages.length > 1">({{ lang.name }})</template>
          </label>
          <input
            type="text"
            class="form-control"
            v-model="information.name[lang.code]"
            :id="`name-${lang.code}`"
            :class="{ 'is-invalid': v$?.name?.[lang.code]?.$errors.length }"
          >
          <div class="invalid-feedback">
            {{
              v$?.name?.[lang.code]?.$errors?.[0]?.$message || v$?.name?.[lang.code]?.$errors?.[0]?.$params?.message
            }}
          </div>
        </div>

        <div class="mb-3"  v-for="(lang, index) in $page.props.languages" :key="lang.id">
          <label :for="`description-${lang.code}`" class="form-label">
            {{ $t("general.description") }}
            <template v-if="$page.props.languages.length > 1">({{ lang.description }})</template>
          </label>
          <ckeditor
            :editor="editor"
            :config="editorConfig"
            v-model="information.description[lang.code]"
            :id="`description-${lang.code}`"
            :class="{ 'is-invalid': v$?.description?.[lang.code]?.$errors.length }"
          >

          </ckeditor>
          <div class="invalid-feedback">
            {{
              v$?.description?.[lang.code]?.$errors?.[0]?.$message || v$?.description?.[lang.code]?.$errors?.[0]?.$params?.message
            }}
          </div>
        </div>

        <div class="mb-3"  v-for="(lang, index) in $page.props.languages" :key="lang.id">
          <label :for="`info-${lang.code}`" class="form-label">
            {{ $t("general.info") }}
            <template v-if="$page.props.languages.length > 1">({{ lang.info }})</template>
          </label>
          <ckeditor
            :editor="editor"
            :config="editorConfig"
            v-model="information.info[lang.code]"
            :id="`info-${lang.code}`"
            :class="{ 'is-invalid': v$?.info?.[lang.code]?.$errors.length }"
          >

          </ckeditor>
          <div class="invalid-feedback">
            {{
              v$?.info?.[lang.code]?.$errors?.[0]?.$message || v$?.info?.[lang.code]?.$errors?.[0]?.$params?.message
            }}
          </div>
        </div>

        <div class="mb-3">
          <label for="img" class="form-label">
            {{ $t("general.img") }}
          </label>
          <input
            type="file"
            class="form-control d-none"
            id="img"
            @change="updatePhotoPreview"
            ref="img"
            :class="{ 'is-invalid': v$?.img.$errors.length }"
          >
          <input
            v-if="!imgPreview && !information?.img"
            type="text"
            @click="openUploadFiles"
            value="Выбрать изображение"
            readonly
            class="form-control cursor-pointer"
          >
          <div class="invalid-feedback">
            {{
              v$?.img?.$errors?.[0]?.$message || v$?.img?.$errors?.[0]?.$params?.message
            }}
          </div>
          <div v-show="imgPreview || information?.img">
            <div class="image-block">
              <img class="choosed-image object-cover" :src="imgPreview ? imgPreview : information?.img ?  '/storage/' + information?.img : ''">
               <button v-if="imgPreview || information?.img" type="button" class="btn-sm btn-primary mt-1 d-inline" @click="openUploadFiles">
                {{ $t("general.change") }}
              </button>
              <button v-if="imgPreview || information?.img" type="button" class="btn-sm btn-danger mt-1" @click="removePreview">
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
import ClassicEditor from '@ckeditor/ckeditor5-build-classic'

defineOptions({ layout: Layout })

const props = defineProps({
  title: String,
  data: Object
})

const editor = ClassicEditor

const img = ref(null)
const imgPreview = ref(null)
const defaultimgPreview = ref(null)

const information = useForm({
  _method: route().current('admin.informations.create') ? 'POST' : 'PUT',
  id: props?.data?.id,
  img: props?.data?.img,
  delete_img: false,
  title: {
    'ru': props.data?.title?.ru,
  },
  name: {
    'ru': props.data?.name?.ru,
  },
  description: {
    'ru': props.data?.description?.ru,
  },
  info: {
    'ru': props.data?.info?.ru,
  },
})

const editorConfig = {
}


const rules = {
  title: {
    ru: { Required },
  },
  name: {
    ru: { Required },
  },
  description: {
    ru: { Required },
  },
  info: {
    ru: { Required },
  },
  img: {  }
}

const v$ = useVuelidate(rules, information)

const submitForm = () => {
  v$.value.$touch()
  if (v$._value.$invalid) return false

  route().current('admin.informations.create')
    ? information.post(route('admin.informations.store'), information)
    : information.post(route('admin.informations.update', information.id), information)
}

const updatePhotoPreview = (event) => {
  const file = img.value.files[0]

  const reader = new FileReader();
  reader.onload = (e) => {
    imgPreview.value = e.target.result;
    information.img = file;
    event.target.value = "";
  }

  reader.readAsDataURL(file);
}

const removePreview = (event) => {
  imgPreview.value = defaultimgPreview.value = information.img = img.value = null
  information.delete_img = true
}

const openUploadFiles = () => {
  document.getElementById('img').click()
}
</script>
