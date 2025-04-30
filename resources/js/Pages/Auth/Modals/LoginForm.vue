<template>
  <Teleport to="body">
    <div
      class="modal fade"
      id="exampleModal"
      tabindex="-1"
      aria-labelledby="exampleModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog">
        <div class="modal-content">

          <form @submit.prevent="submit">
            <div class="modal-header">
              <h4 class="modal-title">{{ $t("general.login") }}</h4>
              <button
                type="button"
                class="btn-close"
                data-bs-dismiss="modal"
                aria-label="Close"
              ></button>
            </div>
            <div class="modal-body">
              <template v-if="$page?.props?.successMessage || $page?.props?.errorMessage">
                <div v-if="$page?.props?.successMessage" class="alert alert-success alert-dismissible fade show m-0 mb-2 alert-message" role="alert">
                  {{ $page?.props?.successMessage }}
                </div>
                <div v-if="$page?.props?.errorMessage" class="alert alert-danger alert-dismissible m-0 mb-2 alert-message" role="alert">
                  {{ $page?.props?.errorMessage }}
                </div>
              </template>
              <div class="mb-3">
                <label for="email" class="form-label">{{ $t("general.email") }}</label>
                <input
                  type="email"
                  v-model="form.email"
                  placeholder="name@example.com"
                  class="form-control"
                  id="email"
                  aria-describedby="emailHelp"
                  :class="{ 'is-invalid': v$.email.$errors.length }"
                >
                <div class="invalid-feedback">
                  {{ v$?.email?.$errors[0]?.$message }}
                </div>
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">{{ $t("general.password") }}</label>
                <input
                  type="password"
                  v-model="form.password"
                  placeholder="*******"
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
            </div>
            <div class="modal-footer justify-content-between">
              <label class="form-check-label">
                <input type="checkbox" class="me-1" v-model="form.remember" />{{ $t("general.remember") }}
              </label>
              <input type="submit" class="btn btn-primary" :value="$t('general.login')" />
            </div>
            <div class="row social-registration p-2">
              <div class="col">
                <a :href="route('yandex_redirect')">
                  <button type="button" class="btn btn-block btn-social btn-yandex">
                    <span class="me-2"><font-awesome-icon :icon="['fab', 'yandex']"  /></span> {{ $t("general.loginViaYandex") }}
                  </button>
                </a>
              </div>
              <div class="col">
                <a :href="route('mailru_redirect')">
                  <button type="button" class="btn btn-block btn-social btn-mail-ru">
                    <span class="me-2"><font-awesome-icon icon="at" class="mail-ru-icon" /></span> {{ $t("general.loginViaMailRu") }}
                  </button>
                </a>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { useForm } from "@inertiajs/vue3";
import useVuelidate from "@vuelidate/core";
import { Required, Email, MinLength } from "../../../Utils/validationMessages";

defineProps({
  canResetPassword: Boolean,
  status: String,
});

const form = useForm({
  email: "",
  password: "",
  remember: false,
});

const rules = {
  email: { Required, Email },
  password: { Required, MinLength: MinLength(7) },
};

const v$ = useVuelidate(rules, form);

const submit = () => {
  v$.value.$touch();
  if (v$._value.$invalid) return false;

  form
    .transform((data) => ({
      ...data,
      remember: form.remember ? "on" : "",
    }))
    .post(route("login"), {
      onSuccess: () => {
        document.querySelectorAll('.modal-backdrop').forEach(element => {
          element.remove();
        });
        form.reset("password")
      },
    });
};
</script>
