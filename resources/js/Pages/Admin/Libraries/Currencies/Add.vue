<template>
  <Head :title="title" />

  <div class="card mb-4 card-table-list">
    <div class="card-header">
      <font-awesome-icon icon="table" />
      {{ title }}
      <Link class="edit-action-link" :href="route('admin.currencies.index')">
        <button type="button" class="btn float-end btn-primary">
          <font-awesome-icon icon="arrow-left" />
        </button>
      </Link>
    </div>
    <div class="card-body card-table-list">
      <form @submit.prevent="submitForm" @keyup.13="submitForm">
        <div class="mb-3">
          <label :for="`name`" class="form-label">
            {{ $t("general.title") }}
          </label>
          <input
            type="text"
            class="form-control"
            v-model="currency.name"
            :id="`name`"
            :class="{ 'is-invalid': v$?.name?.$errors?.length }"
          >
          <div class="invalid-feedback">
            {{
              v$?.name?.$errors?.[0]?.$message || v$?.name?.$errors?.[0]?.$params?.message
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

const currency = useForm({
  id: props?.data?.id,
  name: props.data?.name,
})

const rules = {
  name: Required,
}

const v$ = useVuelidate(rules, currency)

const submitForm = () => {
  v$.value.$touch()
  if (v$._value.$invalid) return false

  route().current('admin.currencies.create')
    ? currency.post(route('admin.currencies.store'), currency)
    : currency.put(route('admin.currencies.update', currency.id), currency)

}
</script>
