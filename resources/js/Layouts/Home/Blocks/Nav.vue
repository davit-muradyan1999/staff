<template>
  <LoginForm v-if="!$page.props.auth.user" />
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container px-5">
      <Link class="navbar-brand" :href="route('home')">
        <img src="images/StaffStyleLogo.svg" class="home-logo">
      </Link>
      <button
        class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#navbarSupportedContent"
        aria-controls="navbarSupportedContent"
        aria-expanded="false"
        aria-label="Toggle navigation"
      >
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <Link class="nav-link" :href="route('register')">{{ $t("general.registration") }}</Link>
          </li>
          <li class="nav-item">
            <Link class="nav-link" href="#" id="login-button" data-bs-toggle="modal" data-bs-target="#exampleModal">{{ $t("general.login") }}</Link>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</template>

<script setup>
import LoginForm from '../../../Pages/Auth/Modals/LoginForm.vue'
import { router } from '@inertiajs/vue3'

const closeMessage = () => {
  router.get(window.location)
}
</script>
