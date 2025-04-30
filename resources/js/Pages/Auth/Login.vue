<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";

document.location.href = '/'

defineProps({
  canResetPassword: Boolean,
  status: String,
});

const form = useForm({
  email: "",
  password: "",
  remember: false,
});

const submit = () => {
  form
    .transform((data) => ({
      ...data,
      remember: form.remember ? "on" : "",
    }))
    .post(route("login"), {
      onFinish: () => form.reset("password"),
    });
};
</script>

<template>
  <Head title="Log in" />
  <div class="vh-100 d-flex justify-content-center align-items-center" v-if="false">
    <div class="container">
      <div class="row d-flex justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
          <div class="border border-3 border-primary"></div>
          <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
          </div>
          <div class="card bg-white">
            <div class="card-body p-5">
              <form class="mb-3 mt-md-4" @submit.prevent="submit">
                <h2 class="fw-bold mb-2 text-uppercase">Brand</h2>
                <p class="mb-5">Please enter your login and password!</p>
                <div class="mb-3">
                  <label for="email" class="form-label">Email address</label>
                  <input
                    type="email"
                    v-model="form.email"
                    class="form-control"
                    id="email"
                    placeholder="name@example.com"
                    autofocus
                    autocomplete="username"
                  />
                  <span :message="form.errors.email"></span>
                </div>
                <div class="mb-3">
                  <label for="password" class="form-label">Password</label>
                  <input
                    type="password"
                    class="form-control"
                    id="password"
                    placeholder="*******"
                    v-model="form.password"
                    autocomplete="current-password"
                  />
                  <span :message="form.errors.password"></span>
                </div>
                <div class="block mt-4">
                  <label class="flex items-center">
                    <input type="checkbox" v-model="form.remember" name="remember" />
                    <span class="ml-2 text-sm text-gray-600">Remember me</span>
                  </label>
                </div>
                <p class="small">
                  <a class="text-primary" href="forget-password.html"
                    >Forgot password?</a
                  >
                </p>
                <div class="d-grid">
                  <button class="btn btn-outline-dark" type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Login
                  </button>
                </div>
              </form>
              <div>
                <p class="mb-0 text-center">
                  Don't have an account?
                  <a href="signup.html" class="text-primary fw-bold">Sign Up</a>
                </p>
              </div>

              <!-- <Link
                v-if="canResetPassword"
                :href="route('password.request')"
                class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                Forgot your password?
              </Link> -->
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
