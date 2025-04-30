<template>
  <Head :title="title" />
  <RegisterForm :type="selectedType" />
  <MailMessage />
  <header class="bg-dark py-5">
    <div class="container px-5">
      <div class="row gx-5 align-items-center justify-content-center">
        <div class="col-lg-12 col-xl-12 col-xxl-12">
          <div class="my-5 text-center text-xl-start">
            <h1 class="display-5 fw-bolder text-white mb-2">{{ $t("general.weHelpFindJob") }}</h1>
            <p class="lead fw-normal text-white-50 mb-4">{{ $t("general.itsEasy") }}</p>
            <div class="d-grid gap-3 d-sm-flex justify-content-sm-center justify-content-xl-start">
              <a class="btn btn-primary btn-lg px-4 me-sm-3" href="#" data-bs-toggle="modal" data-bs-target="#registerModal" @click.prevent="selectedType = 'COMPANY'">{{ $t("general.findEmployee") }}</a>
              <a class="btn btn-outline-light btn-lg px-4" href="#" data-bs-toggle="modal" data-bs-target="#registerModal" @click.prevent="selectedType = 'WORKER'">{{ $t("general.findJob") }}</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>

  <template v-if="informations.length > 0">
    <template v-for="(info, index) in informations" :key="info.id">
      <section class="py-1">
        <div class="container px-5 my-3">
          <h2 class="fw-bolder mb-4 text-center">{{ info?.title_n }}</h2>
          <div class="row gx-4 gx-lg-5 my-5">
            <template v-if="index % 2 === 0">
              <div class="col-lg-5 order-sm-2 order-lg-1 order-md-2 order-2">
                <h1 class="font-weight-light">{{ info?.name_n }}</h1>
                <div v-html="info?.description_n"></div>
                <a class="btn btn-primary mt-2" v-if="!openInformationIds.includes(info?.id)" :style="{opacity: !openInformationIds.includes(info?.id) ? '1' : '0'}" href="#" @click.prevent="openInformation(info?.id)">
                  {{ $t("general.learnMore") }}
                </a>
              </div>
              <div class="col-lg-7 order-sm-1 order-lg-2 order-md-1 order-1">
                <img class="rounded mb-4 mb-lg-0 info-img" :src="`/storage/${info.img}`">
              </div>
            </template>
            <template v-else>
              <div class="col-lg-7">
                <img class="rounded mb-4 mb-lg-0 info-img" :src="`/storage/${info.img}`">
              </div>
              <div class="col-lg-5">
                <h1 class="font-weight-light">{{ info?.name_n }}</h1>
                <div v-html="info?.description_n"></div>
                <a class="btn btn-primary mt-2" v-if="!openInformationIds.includes(info?.id)" :style="{opacity: !openInformationIds.includes(info?.id) ? '1' : '0'}" href="#" @click.prevent="openInformation(info?.id)">
                  {{ $t("general.learnMore") }}
                </a>
              </div>
            </template>
          </div>
        </div>
      </section>
      <div class="py-3 bg-light" v-if="openInformationIds.includes(info?.id)">
        <div class="container px-5 my-3">
          <div class="row gx-5 justify-content-center">
            <div class="col-lg-12 col-xl-12">
              <div class="">
                <div class="fs-5 mb-4">
                  <div v-html="info?.info_n"></div>
                </div>
                <div class="d-flex align-items-center justify-content-center">
                  <div class="fw-bold">
                    <a v-if="index == 0" href="#" data-bs-toggle="modal" data-bs-target="#registerModal" class="btn btn-primary px-4 me-sm-3 register-more-button" @click="selectedType = 'WORKER'">
                      {{ $t("general.registration") }}
                    </a>
                    <a v-else-if="index == 1" href="#" data-bs-toggle="modal" data-bs-target="#registerModal" class="btn btn-primary px-4 me-sm-3 register-more-button" @click="selectedType = 'COMPANY'">
                      {{ $t("general.registration") }}
                    </a>
                    <a class="btn btn-secondary px-4 hide-more-button" href="#" @click.prevent="openInformation(info?.id)">{{ $t("general.hide") }}</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
    <a href="#" data-bs-toggle="modal" data-bs-target="#mailMessageModal" id="mail-message-modal" class="d-none">
      #
    </a>
  </template>

  <div class="py-2 bg-light" v-if="partners.length > 0">
    <div class="container px-5 my-5">
      <h2 class="fw-bolder mb-4 text-center">{{ $t("general.ourPartners") }}</h2>
      <div class="row gx-5 justify-content-center">
        <Flicking :options="{ circular: true, align: 'prev' }">
          <div class="partner-slide" v-for="partner in partners" :key="partner.id">
            <template v-if="partner.url">
              <a :href="partner.url" target="_blank">
                <img class="partner-logo " :src="`/storage/${partner.logo}`">
              </a>
            </template>
            <template v-else>
              <img class="partner-logo " :src="`/storage/${partner.logo}`">
            </template>
          </div>
        </Flicking>
      </div>
    </div>
  </div>

  <div class="py-2" v-if="false">
    <div class="container my-5">
      <h2 class="fw-bolder mb-4 text-center">{{ $t("general.ourResults") }}</h2>
      <div class="row gx-5 justify-content-center">
        <div class="col-lg-4 col-md-12 mb-4 p-0">
          <div class="card h-100">
            <div class="card-body">
              <div class="text-center p-3">
                <h3 class="card-title">{{ $t("general.employees") }}</h3>
              </div>
              <div class="text-center">
                <span class="card-text">
                  <font-awesome-icon icon="user" class="display-1" />
                </span>
              </div>
            </div>
            <div class="card-body text-center">
              <h1>1678</h1>
            </div>
          </div>
        </div>
        <div class="col-lg-4 col-md-12 mb-4 p-0">
          <div class="card h-100">
            <div class="card-body">
              <div class="text-center p-3">
                <h3 class="card-title">{{ $t("general.companies") }}</h3>
              </div>
              <div class="text-center">
                <span class="card-text">
                  <font-awesome-icon icon="users" class="display-1" />
                </span>
              </div>
            </div>
            <div class="card-body text-center">
              <h1>523</h1>
            </div>
          </div>
        </div>
        <div class="col-lg-4 col-md-12 mb-4 p-0">
          <div class="card h-100">
            <div class="card-body">
              <div class="text-center p-3">
                <h3 class="card-title">{{ $t("general.jobs") }}</h3>
              </div>
              <div class="text-center">
                <span class="card-text">
                  <font-awesome-icon icon="briefcase" class="display-1" />
                </span>
              </div>
            </div>
            <div class="card-body text-center">
              <h1>8676</h1>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import Layout from '../../Layouts/Home/AppLayout.vue'
import Flicking from '@egjs/vue3-flicking'
import '@egjs/vue3-flicking/dist/flicking.css'
import { usePage } from '@inertiajs/vue3';
import { ref } from 'vue'
import RegisterForm from '../Auth/Modals/RegisterForm.vue';
import MailMessage from '../Auth/Modals/MailMessage.vue';

const selectedType = ref('');
const urlParams = new URLSearchParams(window.location.search)

if(usePage().props?.successMessage) {
  setTimeout(function(){
    if(urlParams.get('verify') && document.getElementById('login-button')) {
      document.getElementById('login-button').click();
    }
   }, 500);
}

if(usePage().props?.errorMessage) {
  setTimeout(function(){
    if(urlParams.get('access') && document.getElementById('login-button')) {
      document.getElementById('login-button').click();
    }
   }, 500);
}

defineOptions({ layout: Layout })

defineProps({
  title: String,
  informations: Object,
  partners: Object,
});

const openInformationIds = ref([])
const openInformation = (id) => {
  if(openInformationIds.value.includes(id)){
    openInformationIds.value.splice(openInformationIds.value.indexOf(id), 1);
    return;
  }
  openInformationIds.value.push(id);
}

</script>
