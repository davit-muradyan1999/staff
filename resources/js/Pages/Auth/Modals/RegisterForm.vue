<template>
  <div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="registerModalLabel">{{ $t("general.registration") }}</h5>
          <button type="button" id="close-modal" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="text-center m-auto mb-4 p-2 rounded" role="button">
            <font-awesome-icon :icon="formType == 'COMPANY' ? 'users' : 'user'" class="display-3" />
            <h5 class="card-title mt-2">{{ formType == 'COMPANY' ? $t("general.company") : $t("general.applicant") }}</h5>
          </div>
          <ShowMessage
            v-if="$page?.props?.errorMessage || Object.keys($page.props.errors).length > 0"
          />
          <form>
            <div class="mb-3">
              <label for="email" class="form-label">{{ $t("general.firstName") }}, {{ $t("general.lastName") }}</label>
              <input
                type="name"
                v-model="form.name"
                placeholder=""
                class="form-control"
                id="name"
                aria-describedby="emailHelp"
                :class="{ 'is-invalid': v$.name.$errors.length }"
              >
              <div class="invalid-feedback">
                {{ v$?.name?.$errors[0]?.$message }}
              </div>
            </div>

            <div class="mb-3">
              <label for="email" class="form-label">{{ $t("general.email") }}</label>
              <input
                type="email"
                v-model="form.email"
                placeholder=""
                class="form-control"
                id="email"
                :class="{ 'is-invalid': v$.email.$errors.length }"
              >
              <div class="invalid-feedback">
                {{
                  v$?.email?.$errors[0]?.$message ||
                  v$?.email?.$errors[0]?.$params?.message
                }}
              </div>
            </div>

            <div class="mb-3" v-if="formType == 'COMPANY'">
              <label for="companyName" class="form-label">{{ $t("general.companyName") }}</label>
              <input
                type="text"
                v-model="form.company_name"
                placeholder=""
                class="form-control"
                id="companyName"
                :class="{ 'is-invalid': v$.company_name.$errors.length }"
              >
              <div class="invalid-feedback">
                {{ v$?.company_name?.$errors[0]?.$message }}
              </div>
            </div>

            <div class="mb-3">
              <label for="phone" class="form-label">{{ $t("general.phone") }}</label>
              <input
                type="phone"
                v-model="form.phone"
                placeholder=""
                class="form-control"
                id="phone"
                :class="{ 'is-invalid': v$.phone.$errors.length }"
              >
              <div class="invalid-feedback">
                {{ v$?.phone?.$errors[0]?.$message }}
              </div>
            </div>

            <div class="mb-3" v-if="formType == 'COMPANY'">
              <label for="phone" class="form-label">{{ $t("general.companyPhone") }}</label>
              <input
                type="phone"
                v-model="form.company_phone"
                placeholder=""
                class="form-control"
                id="company_phone"
                :class="{ 'is-invalid': v$.company_phone.$errors.length }"
              >
              <div class="invalid-feedback">
                {{ v$?.company_phone?.$errors[0]?.$message }}
              </div>
            </div>

            <div class="mb-3">
              <label for="password" class="form-label">{{ $t("general.password") }}</label>
              <input
                type="password"
                v-model="form.password"
                placeholder=""
                class="form-control"
                id="password"
                :class="{ 'is-invalid': v$.password.$errors.length }"
              >
              <div class="invalid-feedback">
                {{
                  v$?.password?.$errors[0]?.$message ||
                  v$?.password?.$errors[0]?.$params?.message
                }}
              </div>
            </div>

            <div class="mb-3">
              <label for="password_confirmation" class="form-label">{{ $t("general.confirmPassword") }}</label>
              <input
                type="password"
                v-model="form.password_confirmation"
                placeholder=""
                class="form-control"
                id="password_confirmation"
                :class="{ 'is-invalid': v$.password_confirmation.$errors.length }"
              >
              <div class="invalid-feedback">
                {{
                    v$?.password_confirmation?.$errors[0]?.$message ||
                    v$?.password_confirmation?.$errors[0]?.$params?.message
                  }}
              </div>
            </div>

            <div class="row social-registration">
              <div class="col mb-2">
                <a :href="route('yandex_redirect', {'type': type})">
                  <button type="button" class="btn btn-block btn-social btn-yandex">
                    <span class="me-2"><font-awesome-icon :icon="['fab', 'yandex']"  /></span>{{ $t("general.registerViaYandex") }}
                  </button>
                </a>
              </div>
              <div class="col">
                <a :href="route('mailru_redirect', {'type': type})">
                  <button type="button" class="btn btn-block btn-social btn-mail-ru">
                    <span class="me-2"><font-awesome-icon class="mail-ru-icon" icon="at" /></span>{{ $t("general.registerViaYMailRu") }}
                  </button>
                </a>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ $t("general.close") }}</button>
          <button type="button" class="btn btn-primary" @click="submit">{{ $t("general.registration") }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import useVuelidate from '@vuelidate/core'
import { Required, Email, MinLength } from '../../../Utils/validationMessages'

const props = defineProps({
  type: String
})

const formType = ref(props.type)

const typeRequired = computed(() => {
  return formType.value === 'COMPANY' ? { Required } : { }
});

const form = useForm({
  name: '',
  company_name: '',
  email: '',
  phone: '',
  company_phone: '',
  password: '',
  password_confirmation: '',
  type: null
})

const rules = {
  name: { Required },
  company_name: typeRequired,
  email: { Required, Email },
  phone: { Required },
  company_phone: typeRequired,
  password: { Required, MinLength: MinLength(7) },
  password_confirmation: { Required, MinLength: MinLength(7) },
};

let v$ = useVuelidate(rules, form)

const submit = () => {
  v$.value.$touch()
  if (v$._value.$invalid) return false;

  form.type = formType.value
  form.post(route('register'), {
    onSuccess: () => {
      document.getElementById('close-modal').click();
      document.getElementById('mail-message-modal').click();
      form.reset()
      v$.value.$reset()
    },
  })
}

watch(() => props.type, (newValue, oldValue) => {
  form.reset()
  formType.value = newValue
  v$.value.$reset()
})
</script>
