<template>
  <Head :title="title" />

  <div class="card mb-4 card-table-list">
    <div class="card-header">
      <font-awesome-icon icon="table" />
      {{ title }}
      <Link class="edit-action-link" :href="route('admin.hobbies.index')">
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
            v-model="hobby.name[lang.code]"
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
import { helpers } from '@vuelidate/validators'
import { reactive } from 'vue'
import { useForm } from '@inertiajs/vue3'

defineOptions({ layout: Layout })

const props = defineProps({
  title: String,
  data: Object
})

const hobby = useForm({
  id: props?.data?.id,
  name: {
    'ru': props.data?.name?.ru,
  },
})

const rules = {
  name: {
    ru: { Required },
  },
}

const v$ = useVuelidate(rules, hobby)

const submitForm = () => {
  v$.value.$touch()
  if (v$._value.$invalid) return false

  route().current('admin.hobbies.create')
    ? hobby.post(route('admin.hobbies.store'), hobby)
    : hobby.put(route('admin.hobbies.update', hobby.id), hobby)

}
</script>
