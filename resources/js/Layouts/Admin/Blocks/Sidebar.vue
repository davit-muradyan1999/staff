<template>
  <div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
      <div class="sb-sidenav-menu">
        <div class="nav">
          <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseUsers" aria-expanded="true" aria-controls="collapseUsers">
            {{ $t("general.users") }}
            <div class="sb-sidenav-collapse-arrow">
              <font-awesome-icon icon="chevron-down" />
            </div>
          </a>
          <div class="collapse " id="collapseUsers" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion" style="">
            <nav class="sb-sidenav-menu-nested nav">
              <Link v-if="usePermissionA([7])" class="nav-link" :href="route('admin.users.index') + '?stage=&role=WORKER'" :class="urlPath.match(/\badmin\/users\?stage=&role=WORKER\b/)?.length > 0 ? 'active' : ''">{{ $t("general.collaborator") }}</Link>
              <Link v-if="usePermissionA([8])" class="nav-link" :href="route('admin.users.index') + '?stage=&role=MODERATOR'" :class="urlPath.match(/\badmin\/users\?stage=&role=MODERATOR\b/)?.length > 0 ? 'active' : ''">{{ $t("general.moderators") }}</Link>
              <Link v-if="usePermissionA([8])" class="nav-link" :href="route('admin.users.index') + '?stage=&role=COMPANY'" :class="urlPath.match(/\badmin\/users\?stage=&role=COMPANY\b/)?.length > 0 ? 'active' : ''">{{ $t("general.companies") }}</Link>

              <template v-if="$page.props.auth?.user?.role == 'ADMIN'">
                <Link class="nav-link" :href="route('admin.users.index') + '?stage=&role=ADMINISTRATOR'" :class="urlPath.match(/\badmin\/users\?stage=&role=ADMINISTRATOR\b/)?.length > 0 ? 'active' : ''">{{ $t("general.administrators") }}</Link>
                <Link class="nav-link" :href="route('admin.users.index') + '?stage=registration&role=WORKER'" :class="urlPath.match(/\badmin\/users\?stage=registration&role=WORKER\b/)?.length > 0 ? 'active' : ''">{{ $t("general.atTheRegistrationStage") }} ({{ $t("general.collaborator") }})</Link>
                <Link class="nav-link" :href="route('admin.users.index') + '?stage=registration&role=COMPANY'" :class="urlPath.match(/\badmin\/users\?stage=registration&role=COMPANY\b/)?.length > 0 ? 'active' : ''">{{ $t("general.atTheRegistrationStage") }} ({{ $t("general.companies") }})</Link>
              </template>
            </nav>
          </div>
          <Link v-if="usePermissionA([7])" class="nav-link" :href="route('admin.salary_project_results')" :class="urlPath.match(/\badmin\/salary_project_results\b/)?.length > 0 ? 'active' : ''">{{ $t("general.salaryProject") }}</Link>
          <!-- <Link class="nav-link" :href="route('admin.jobs.index')" :class="urlPath.match(/\badmin\/jobs\b/)?.length > 0 ? 'active' : ''">{{ $t("general.jobs") }}</Link> -->
          <Link v-if="usePermissionA([8])" class="nav-link" :href="route('admin.establishments.index')" :class="urlPath.match(/\badmin\/establishments\b/)?.length > 0 ? 'active' : ''">{{ $t("general.establishment") }}</Link>
          <Link class="nav-link" :class="urlPath.match(/\bcompany_jobs\b/)?.length > 0 ? 'active' : ''" :href="route('company_jobs')">{{ $t("general.jobs") }}</Link>
          <Link class="nav-link" :class="urlPath.match(/\bactive_works\b/)?.length > 0 ? 'active' : ''" :href="route('active_works')">{{ $t("general.employeePositions") }}</Link>
          <Link class="nav-link" :href="route('admin.black_lists.index')" :class="urlPath.match(/\badmin\/black_lists\b/)?.length > 0 ? 'active' : ''">{{ $t("general.blackList") }}</Link>
          <Link class="nav-link" :href="route('admin.check_list_bonuses')" :class="urlPath.match(/\badmin\/check_list_bonuses\b/)?.length > 0 ? 'active' : ''">{{ $t("general.checkListBonuses") }}</Link>
          <Link class="nav-link" :href="route('admin.check_lists.index')" :class="urlPath.match(/\badmin\/check_lists\b/)?.length > 0 ? 'active' : ''">{{ $t("general.checkList") }}</Link>
          <Link v-if="usePermissionA([])" class="nav-link" :href="route('admin.income')" :class="urlPath.match(/\badmin\/income\b/)?.length > 0 ? 'active' : ''">{{ $t("general.income") }}</Link>
          <Link v-if="usePermissionA([])" class="nav-link" :href="route('admin.spending')" :class="urlPath.match(/\badmin\/spending\b/)?.length > 0 ? 'active' : ''">{{ $t("general.spending") }}</Link>
          <Link v-if="usePermissionA([7])" class="nav-link" :href="route('admin.employee_balance')" :class="urlPath.match(/\badmin\/employee_balance\b/)?.length > 0 ? 'active' : ''">{{ $t("general.employeeBalance") }}</Link>
          <Link v-if="usePermissionA([])" class="nav-link" :href="route('admin.companies_balance')" :class="urlPath.match(/\badmin\/companies_balance\b/)?.length > 0 ? 'active' : ''">{{ $t("general.companyBalances") }}</Link>
          <Link v-if="usePermissionA([])" class="nav-link" :href="route('admin.login_history')" :class="urlPath.match(/\badmin\/login_history\b/)?.length > 0 ? 'active' : ''">{{ $t("general.loginHistory") }}</Link>

          <template v-if="$page.props.auth?.user?.role == 'ADMIN'">
            <hr>
            <Link class="nav-link" :href="route('admin.positions.index')" :class="urlPath.match(/\badmin\/positions\b/)?.length > 0 ? 'active' : ''">{{ $t("general.positions") }}</Link>
            <Link class="nav-link" :href="route('admin.educations.index')" :class="urlPath.match(/\badmin\/educations\b/)?.length > 0 ? 'active' : ''">{{ $t("general.educations") }}</Link>
            <Link class="nav-link" :href="route('admin.professional_skills.index')" :class="urlPath.match(/\badmin\/professional_skills\b/)?.length > 0 ? 'active' : ''">{{ $t("general.professionalSkills") }}</Link>
            <Link class="nav-link" :href="route('admin.personal_skills.index')" :class="urlPath.match(/\badmin\/personal_skills\b/)?.length > 0 ? 'active' : ''">{{ $t("general.personalSkills") }}</Link>
            <Link class="nav-link" :href="route('admin.language_skills.index')" :class="urlPath.match(/\badmin\/language_skills\b/)?.length > 0 ? 'active' : ''">{{ $t("general.languageSkills") }}</Link>
            <Link class="nav-link" :href="route('admin.field_of_activities.index')" :class="urlPath.match(/\badmin\/field_of_activities\b/)?.length > 0 ? 'active' : ''">{{ $t("general.fieldOfActivity") }}</Link>
            <Link class="nav-link" :href="route('admin.driver_licenses.index')" :class="urlPath.match(/\badmin\/driver_licenses\b/)?.length > 0 ? 'active' : ''">{{ $t("general.driverLicense") }}</Link>
            <Link class="nav-link" :href="route('admin.hobbies.index')" :class="urlPath.match(/\badmin\/hobbies\b/)?.length > 0 ? 'active' : ''">{{ $t("general.hobbyAndInterest") }}</Link>
            <Link class="nav-link" :href="route('admin.genders.index')" :class="urlPath.match(/\badmin\/genders\b/)?.length > 0 ? 'active' : ''">{{ $t("general.gender") }}</Link>
            <Link class="nav-link" :href="route('admin.citizenships.index')" :class="urlPath.match(/\badmin\/citizenships\b/)?.length > 0 ? 'active' : ''">{{ $t("general.citizenship") }}</Link>
            <Link class="nav-link" :href="route('admin.industries.index')" :class="urlPath.match(/\badmin\/industries\b/)?.length > 0 ? 'active' : ''">{{ $t("general.industry") }}</Link>
            <Link class="nav-link" :href="route('admin.company_types.index')" :class="urlPath.match(/\badmin\/company_types\b/)?.length > 0 ? 'active' : ''">{{ $t("general.companyType") }}</Link>
            <Link class="nav-link" :href="route('admin.quantity_employees.index')" :class="urlPath.match(/\badmin\/quantity_employees\b/)?.length > 0 ? 'active' : ''">{{ $t("general.quantityEmployees") }}</Link>
            <Link class="nav-link" :href="route('admin.advantage_employees.index')" :class="urlPath.match(/\badmin\/advantage_employees\b/)?.length > 0 ? 'active' : ''">{{ $t("general.advantageEmployees") }}</Link>
            <Link class="nav-link" :href="route('admin.permissions.index')" :class="urlPath.match(/\badmin\/permissions\b/)?.length > 0 ? 'active' : ''">{{ $t("general.roles") }}</Link>
            <Link class="nav-link" :href="route('admin.informations.index')" :class="urlPath.match(/\badmin\/informations\b/)?.length > 0 ? 'active' : ''">{{ $t("general.information") }}</Link>
            <Link class="nav-link" :href="route('admin.partners.index')" :class="urlPath.match(/\badmin\/partners\b/)?.length > 0 ? 'active' : ''">{{ $t("general.partners") }}</Link>
            <Link class="nav-link" :href="route('admin.mean_of_transports.index')" :class="urlPath.match(/\badmin\/mean_of_transports\b/)?.length > 0 ? 'active' : ''">{{ $t("general.meanOfTransports") }}</Link>
            <Link class="nav-link" :href="route('admin.currencies.index')" :class="urlPath.match(/\badmin\/currencies\b/)?.length > 0 ? 'active' : ''">{{ $t("general.currency") }}</Link>
            <Link class="nav-link" :href="route('admin.shift_reasons.index')" :class="urlPath.match(/\badmin\/shift_reasons\b/)?.length > 0 ? 'active' : ''">{{ $t("general.reasonsForShiftChange") }}</Link>
            <Link class="nav-link" :href="route('admin.disability_groups.index')" :class="urlPath.match(/\badmin\/disability_groups\b/)?.length > 0 ? 'active' : ''">{{ $t("general.disabilityGroups") }}</Link>

            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLayouts" aria-expanded="true" aria-controls="collapseLayouts">
              {{ $t("general.settlements") }}
              <div class="sb-sidenav-collapse-arrow">
                <font-awesome-icon icon="chevron-down" />
              </div>
            </a>
          </template>

          <div class="collapse " id="collapseLayouts" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion" style="">
            <nav class="sb-sidenav-menu-nested nav">
              <Link class="nav-link" :href="route('admin.countries.index')" :class="urlPath.match(/\badmin\/countries\b/)?.length > 0 ? 'active' : ''">{{ $t("general.countries") }}</Link>
              <Link class="nav-link" :href="route('admin.districts.index')" :class="urlPath.match(/\badmin\/districts\b/)?.length > 0 ? 'active' : ''">{{ $t("general.federalDistrict") }}</Link>
              <Link class="nav-link" :href="route('admin.regions.index')" :class="urlPath.match(/\badmin\/regions\b/)?.length > 0 ? 'active' : ''">{{ $t("general.regions") }}</Link>
              <Link class="nav-link" :href="route('admin.territory_types.index')" :class="urlPath.match(/\badmin\/territory_types\b/)?.length > 0 ? 'active' : ''">{{ $t("general.territorialDivisionType") }}</Link>
              <Link class="nav-link" :href="route('admin.cities.index')" :class="urlPath.match(/\badmin\/cities\b/)?.length > 0 ? 'active' : ''">{{ $t("general.settlements") }}</Link>
            </nav>
          </div>
        </div>
      </div>
      <div class="sb-sidenav-footer"></div>
    </nav>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Inertia } from '@inertiajs/inertia'
import { usePermissionA } from '@/Hooks/usePermissionA'

const urlPath = ref(window.location.href)

Inertia.on('finish', (event) => {
  urlPath.value = event.detail.visit.url.href

  if(event.detail.visit.url.pathname != '/admin/employee_balance' && event.detail.visit.url.pathname != '/balance') {
    if(localStorage.getItem('notPaids'))
      localStorage.removeItem("notPaids");
  }
})
</script>
