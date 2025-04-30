<template>
  <div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
      <div class="sb-sidenav-menu">
        <div class="user-profile">
          <div class="profile-dtl">
            <a href="#">
              <img id="user-profile-img" :src="$page.props.auth?.user?.profile_photo_path ? '/storage/' + $page.props.auth?.user?.profile_photo_path : 'https://avatars.mds.yandex.net/i?id=0393e6b5b8874243329b425b83cc44b0e64b1b89-8986614-images-thumbs&n=13'" alt="">
            </a>
            <p class="mt-2 profile-sidebar-name" id="user-name">{{ $page.props.auth?.user?.name }}</p>
          </div>
        </div>
        <div class="nav">
          <template v-if="$page.props.auth?.user?.role == 'WORKER'">
            <Link class="nav-link" :class="urlPath.match(/\bprofile\b/)?.length > 0 ? 'active' : ''" :href="route('profile')">{{ $t("general.profile") }}</Link>
            <Link class="nav-link" :class="urlPath.match(/\binformations\b/)?.length > 0 ? 'active' : ''" :href="route('informations')">{{ $t("general.personalInformation") }}</Link>
            <Link class="nav-link" :class="urlPath.match(/\bactive_works\b/)?.length > 0 ? 'active' : ''" :href="route('active_works')">{{ $t("general.myPositions") }}</Link>
            <Link class="nav-link" :class="urlPath.match(/\bschedule\b/)?.length > 0 ? 'active' : ''" :href="route('schedule')">{{ $t("general.schedule") }}</Link>
            <Link class="nav-link" :class="urlPath.match(/\bjobs\b/)?.length > 0 ? 'active' : ''" :href="route('jobs')">{{ $t("general.jobs") }}</Link>
            <Link class="nav-link" :class="urlPath.match(/\bmy_check_lists\b/)?.length > 0 ? 'active' : ''" :href="route('my_check_lists')">{{ $t("general.checkList") }}</Link>
            <Link class="nav-link" :class="urlPath.match(/\bbalance\b/)?.length > 0 ? 'active' : ''" :href="route('balance')">{{ $t("general.balance") }}</Link>
            <Link class="nav-link" :class="urlPath.match(/\bpayment_info\b/)?.length > 0 ? 'active' : ''" :href="route('payment_info')">{{ $t("general.translations") }}</Link>
            <a class="nav-link" href="#">{{ $t("general.conditions") }}</a>
          </template>
          <template v-else-if="$page.props.auth?.user?.role == 'COMPANY' || $page.props.auth?.user?.role == 'MODERATOR'">
            <Link class="nav-link" :class="urlPath.match(/\bcompany_profile\b/)?.length > 0 ? 'active' : ''" :href="route('company_profile')">{{ $t("general.profile") }}</Link>
            <Link class="nav-link" :class="urlPath.match(/\bemployees\b/)?.length > 0 ? 'active' : ''" :href="route('employees')">{{ $t("general.collaborator") }}</Link>
            <Link class="nav-link" :class="urlPath.match(/\bestablishment\b/)?.length > 0 ? 'active' : ''" :href="route('establishment')">{{ $t("general.establishment") }}</Link>
            <Link class="nav-link" :class="urlPath.match(/\bcompany_jobs\b/)?.length > 0 ? 'active' : ''" :href="route('company_jobs')">{{ $t("general.jobs") }}</Link>
            <Link v-if="usePermissionM([1,3])" class="nav-link" :class="urlPath.match(/\bmy_branches\b/)?.length > 0 ? 'active' : ''" :href="route('my_branches')">{{ $t("general.myBranches") }}</Link>
            <Link v-if="usePermissionM([1,3])" class="nav-link" :class="urlPath.match(/\bmoderators\b/)?.length > 0 ? 'active' : ''" :href="route('moderators')">{{ $t("general.moderators") }}</Link>
            <Link class="nav-link" :class="urlPath.match(/\bcheck_list_bonuses\b/)?.length > 0 ? 'active' : ''" :href="route('check_list_bonuses')">{{ $t("general.checkListBonuses") }}</Link>
            <Link class="nav-link" :class="urlPath.match(/\bcheck_lists\b/)?.length > 0 ? 'active' : ''" :href="route('check_lists')">{{ $t("general.checkListCollaborator") }}</Link>
            <Link class="nav-link" :class="urlPath.match(/\bbalance_info\b/)?.length > 0 ? 'active' : ''" :href="route('balance_info')">{{ $t("general.balance") }}</Link>
            <a class="nav-link" href="#">{{ $t("general.reports") }}</a>
          </template>
        </div>
      </div>
      <div class="sb-sidenav-footer">
        <template v-if="$page.props.auth?.user?.role == 'COMPANY' && !$page.props.auth?.user?.company_name">
          <div id="invalid-message" class="invalid-message">
            <Link :href="route('company_profile')">{{ $t("general.youMustFillInTheCompanyName") }}</Link>
          </div>
        </template>
      </div>
    </nav>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Inertia } from '@inertiajs/inertia'
import { usePermissionM } from '@/Hooks/usePermissionM'

const urlPath = ref(window.location.href)

Inertia.on('finish', (event) => {
  urlPath.value = event.detail.visit.url.href
})
</script>
