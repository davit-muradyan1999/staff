<template>
  <Head :title="title" />

  <div class="card mb-4 card-table-list">
    <div class="card-header">
      <font-awesome-icon icon="table" />
      {{ title }}
      <Link class="edit-action-link" :href="route('admin.districts.index')">
        <button type="button" class="btn float-end btn-primary">
          <font-awesome-icon icon="arrow-left" />
        </button>
      </Link>
    </div>
    <div class="card-body card-table-list">
      <form @submit.prevent="submitForm" @keyup.13="submitForm">
        <div class="mb-3">
          <label for="countryId" class="form-label">
            {{ $t("general.country") }}
          </label>
          <Multiselect
            v-model="district.country_id"
            valueProp="id"
            id="countryId"
            :options="countries"
            :searchable="true"
            label="name_c"
            track-by="name_c"
            :class="{ 'is-invalid': v$.country_id.$errors.length }"
          />
          <div class="invalid-feedback">
            {{ v$?.country_id?.$errors[0]?.$message }}
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
            v-model="district.name[lang.code]"
            :id="`name-${lang.code}`"
            :class="{ 'is-invalid': v$?.name?.[lang.code]?.$errors.length }"
          >
          <div class="invalid-feedback">
            {{
              v$?.name?.[lang.code]?.$errors?.[0]?.$message || v$?.name?.[lang.code]?.$errors?.[0]?.$params?.message
            }}
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
import { useForm } from '@inertiajs/vue3'

defineOptions({ layout: Layout })

const props = defineProps({
  title: String,
  data: Object,
  countries: Object
})

const district = useForm({
  id: props?.data?.id,
  country_id: props?.data?.country_id,
  name: {
    'ru': props.data?.name?.ru,
  },
})

const rules = {
  country_id: { Required },
  name: {
    ru: { Required },
  },
}

const v$ = useVuelidate(rules, district)

const submitForm = () => {
  v$.value.$touch()
  if (v$._value.$invalid) return false

  route().current('admin.districts.create')
    ? district.post(route('admin.districts.store'), district)
    : district.put(route('admin.districts.update', district.id), district)
}

</script>
