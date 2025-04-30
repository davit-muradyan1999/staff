<template>
  <Head :title="title" />

  <div class="card mb-4 card-table-list">
    <div class="card-header">
      <font-awesome-icon icon="table" />
      {{ title }}
      <Link class="edit-action-link" :href="route('admin.permissions.index')">
        <button type="button" class="btn float-end btn-primary">
          <font-awesome-icon icon="arrow-left" />
        </button>
      </Link>
    </div>
    <div class="card-body card-table-list">
      <form @submit.prevent="submitForm" @keyup.13="submitForm">
        <div class="mb-3 row">
          <label for="inputName" class="col-sm-2 col-form-label">{{ $t('general.type') }}</label>
          <div class="col-sm-12">
            <Multiselect
              v-model="permission.type"
              valueProp="value"
              :options="types"
              :searchable="true"
              label="name"
              track-by="value"
              :class="{ 'is-invalid': v$.type.$errors.length }"
            />
            <div class="invalid-feedback">
              {{ v$?.type?.$errors?.[0]?.$message || v$?.type?.$errors?.[0]?.$params?.message }}
            </div>
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
            v-model="permission.name[lang.code]"
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


const types = [
  {
    name: 'Сотрудник',
    value: 'WORKER'
  },
  {
    name: 'Модератор',
    value: 'MODERATOR'
  },
  {
    name: 'Компания',
    value: 'COMPANY'
  },
  {
    name: 'Администратор',
    value: 'ADMINISTRATOR'
  },
]

const permission = useForm({
  id: props?.data?.id,
  type: props?.data?.type,
  name: {
    'ru': props.data?.name?.ru,
  },
})

const rules = {
  name: {
    ru: { Required },
  },
  type: { Required }
}

const v$ = useVuelidate(rules, permission)

const submitForm = () => {
  v$.value.$touch()
  if (v$._value.$invalid) return false

  route().current('admin.permissions.create')
    ? permission.post(route('admin.permissions.store'), permission)
    : permission.put(route('admin.permissions.update', permission.id), permission)

}
</script>
