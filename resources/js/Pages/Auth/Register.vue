<script setup>
import { ref } from 'vue';
import { useForm } from "@inertiajs/vue3";
import RegisterForm from './Modals/RegisterForm.vue';
import MailMessage from './Modals/MailMessage.vue';

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  terms: false,
});

const selectedType = ref('');

const submit = () => {
  form.post(route('register'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};
</script>

<template>
  <Head title="Register" />

  <RegisterForm :type="selectedType" />
  <MailMessage />

  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container px-5 text-center">
      <p class="navbar-brand m-auto">{{ $t("general.registration") }}</p>
    </div>
  </nav>

  <section>
    <div class="w-100 pt-3 px-3">
      <span class="mt-2">
        <Link :href="route('home')">
          <font-awesome-icon icon="chevron-left" class="fs-3" />
        </Link>
      </span>
    </div>
    <div class="container px-5 my-5">
      <div class="row row-cols-1 row-cols-md-2 g-4">
        <div class="col">
          <div class="card border-0">
            <div class="card-body">
              <div class="text-center w-100 m-auto mb-4 p-2 cursor-pointer rounded" role="button">
                <a href="#" data-bs-toggle="modal" data-bs-target="#registerModal" class="d-block" @click="selectedType = 'WORKER'">
                  <font-awesome-icon icon="user" class="display-3" />
                  <h5 class="card-title mt-2">{{ $t("general.applicant") }}</h5>
                </a>
              </div>
              <div class="pt-0 p-4">
                <p class="card-text">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>
              </div>
            </div>
          </div>
        </div>
        <div class="col">
          <div class="card border-0">
            <div class="card-body">
              <div class="text-center w-100 m-auto mb-4 p-2 cursor-pointer rounded" role="button">
                <a href="#" data-bs-toggle="modal" data-bs-target="#registerModal" class="d-block" @click="selectedType = 'COMPANY'">
                  <font-awesome-icon icon="users" class="display-3" />
                  <h5 class="card-title mt-2">{{ $t("general.company") }}</h5>
                </a>
              </div>
              <div class="pt-0 p-4">
                <p class="card-text">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>
              </div>
            </div>
          </div>
        </div>
        <a href="#" data-bs-toggle="modal" data-bs-target="#mailMessageModal" id="mail-message-modal" class="d-none">
          #
        </a>
      </div>
    </div>
  </section>
</template>
