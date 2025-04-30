<template>
  <Head :title="title" />

  <div class="card mb-4 card-table-list">
    <div class="card-header">
      <font-awesome-icon icon="table" />
      {{ title }}
      <Link class="edit-action-link" :href="route('admin.partners.index')">
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
            v-model="partner.name[lang.code]"
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
          <label :for="`url`" class="form-label">
            URL
          </label>
          <input
            type="text"
            class="form-control"
            v-model="partner.url"
            :id="`url`"
            :class="{ 'is-invalid': v$?.url?.$errors.length }"
          >
          <div class="invalid-feedback">
            {{
              v$?.url?.$errors?.[0]?.$message || v$?.url?.$errors?.[0]?.$params?.message
            }}
          </div>
        </div>

        <div class="mb-3">
          <label for="logo" class="form-label">
            {{ $t("general.logo") }}
          </label>
          <input
            type="file"
            class="form-control d-none"
            id="logo"
            @change="updatePhotoPreview"
            ref="logo"
            :class="{ 'is-invalid': v$?.logo.$errors.length }"
          >
          <input
            v-if="!imgPreview && !partner?.logo"
            type="text"
            @click="openUploadFiles"
            value="Выбрать изображение"
            readonly
            class="form-control cursor-pointer"
          >
          <div class="invalid-feedback">
            {{
              v$?.logo?.$errors?.[0]?.$message || v$?.logo?.$errors?.[0]?.$params?.message
            }}
          </div>
          <div v-show="logoPreview || partner?.logo">
            <div class="image-block">
              <img class="choosed-image object-cover" :src="logoPreview ? logoPreview : partner?.logo ?  '/storage/' + partner?.logo : ''">
              <button v-if="imgPreview || partner?.logo" type="button" class="btn-sm btn-primary mt-1 d-inline" @click="openUploadFiles">
                {{ $t("general.change") }}
              </button>
              <button v-if="logoPreview || partner?.logo" type="button" class="btn-sm btn-danger mt-1" @click="removePreview">
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
import useVuelidate from '@vuelidate/core'
import { Required } from '@/Utils/validationMessages'
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

defineOptions({ layout: Layout })

const props = defineProps({
  title: String,
  data: Object
})

const logo = ref(null)
const logoPreview = ref(null)
const defaultlogoPreview = ref(null)

const partner = useForm({
  _method: route().current('admin.partners.create') ? 'POST' : 'PUT',
  id: props?.data?.id,
  url: props?.data?.url,
  logo: props?.data?.logo,
  delete_logo: false,
  name: {
    'ru': props.data?.name?.ru,
  },
})

const rules = {
  name: {
    ru: { Required },
  },
  url: {  },
  logo: { Required }
}

const v$ = useVuelidate(rules, partner)

const submitForm = () => {
  v$.value.$touch()
  if (v$._value.$invalid) return false

  route().current('admin.partners.create')
    ? partner.post(route('admin.partners.store'), partner)
    : partner.post(route('admin.partners.update', partner.id), partner)
}

const updatePhotoPreview = (event) => {
  const file = logo.value.files[0]

  const reader = new FileReader();
  reader.onload = (e) => {
    logoPreview.value = e.target.result;
    partner.logo = file;
    event.target.value = "";
  }

  reader.readAsDataURL(file);
}

const removePreview = (event) => {
  logoPreview.value = defaultlogoPreview.value = partner.logo = logo.value = null
  partner.delete_logo = true
}

const openUploadFiles = () => {
  document.getElementById('logo').click()
}
</script>
