<template>
  <Head :title="title" />
  <AddCreditCard
    v-if="openAddCreditCardPopup"
    @close="openAddCreditCardPopup = false"
  />
  <div class="card mb-3" v-if="false">
    <div class="card-body">
      <div class="row">
        <div class="col-md-3">
          <div class="debit-card">
            <div class="d-flex flex-column h-100">
              <label class="d-block">
                <div class="d-flex position-relative justify-content-between">
                  <div>
                    <p class="text-white fw-bold">Название банка</p>
                  </div>
                  <div>
                    <img src="https://www.freepnglogos.com/uploads/mastercard-png/mastercard-logo-png-transparent-svg-vector-bie-supply-0.png" class="master" alt="">
                  </div>
                </div>
              </label>
              <div class="fw-bold d-flex align-items-center justify-content-between">
                <p>**** **** **** 1234</p>
                <p>01/24</p>
              </div>
              <div class="d-flex align-items-center justify-content-between mt-4">
                <p class="m-0">Полное имя</p>
                <p class="m-0">Дествует до 11/22</p>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="debit-card add-debit-card position-relative">
            <div class="d-flex flex-column h-100">
              <label class="d-block">
                <div class="d-flex position-relative justify-content-between">
                  <div style="height:   50px;">
                    <p class="text-white fw-bold">Название банка</p>
                  </div>
                </div>
              </label>
              <div class="fw-bold d-flex align-items-center justify-content-between">
                <p>**** **** **** 1234</p>
                <p >../..</p>
              </div>
              <div class="d-flex align-items-center justify-content-between mt-4">
                <p >Полное имя</p>
                <p >Дествует до ../..</p>
              </div>
            </div>
            <button type="button" class="btn btn-primary text-white add-card" @click="openAddCreditCardPopup = true">
              <span>{{ $t("general.addCard") }}</span>
            </button>
          </div>
        </div>
        <div class="col-md-4">

        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="row bg-white">
        <div class="col-md-3">
          Отработанные часы <span class="h4">{{ useMinuteToHours(totalTime) }}</span> ч
        </div>
        <div class="col-md-3">
          Заработана <span class="h4 text-success">+{{ useFixedNumber(totalSum) }}</span><span class="ms-1 text-success">руб</span>
        </div>
        <!-- <div class="col-md-3">
          Баланс <span class="h4">500.000</span> руб
        </div> -->
      </div>
    </div>
  </div>

  <div class="card mt-3" v-for="(transfer, key) in transfers.data" :key="transfer.id">
    <div class="card-body p-4 position-relative">
      <div class="col-md-12">
        <div class="row align-items-center">
          <div class="col-md-3 border-end">
            {{ moment(transfer?.created_at).format("YYYY-MM-DD HH:mm:ss") }}
          </div>
          <div class="col-md-7 border-end">
            {{ transfer?.note }}
          </div>
          <div class="col-md-2">
            <span class="text-success">{{ transfer?.amount }} {{ $t('general.rub') }}</span>
          </div>
        </div>
      </div>
      <div class="collapse multi-collapse" :id="`multiCollapseExample${key}`">
        <hr class="mt-4 mb-4">
        <div class="row bg-white" v-for="detail in transfer.details" :key="detail.id">
          <div class="col-md-2 col-sm-4">
            {{ moment(detail?.created_at).format("YYYY-MM-DD HH:mm:ss") }}
          </div>
          <div class="col-md-2 col-sm-4">
            <a class="text-decoration-none" target="_blank" :href="route('company_profile', { id: detail?.employee_balance_list?.job_check_list?.company?.id })">
              {{ detail?.employee_balance_list?.job_check_list?.company?.company_name }}
            </a>
          </div>
          <div class="col-md-2 col-sm-4">
            {{ detail?.employee_balance_list?.job_check_list?.branche?.title_b }}
          </div>
          <div class="col-md-2 col-sm-4">
            <template v-if="detail?.employee_balance_list?.job_check_list?.job.id">
              <a class="text-decoration-none" target="_blank" :href="route('admin.show_job_list', detail?.employee_balance_list?.job_check_list?.job?.id)">
                {{ detail?.employee_balance_list?.job_check_list?.job?.establishment?.name_e }} <span class="job-id">#{{ detail?.employee_balance_list?.job_check_list?.job?.id }}</span>
              </a>
            </template>
          </div>
          <div class="col-md-2 col-sm-4">
            <template v-if="detail?.employee_balance_list?.type == 'SHIFT'">
              Смена {{ moment(detail?.employee_balance_list?.job_check_list?.started_at).format("YYYY-MM-DD") }}
              <span :class="detail?.employee_balance_list?.job_check_list?.job_time_graphic?.started_at != detail?.employee_balance_list?.job_check_list?.started_at ? 'text-danger' : ''">{{ moment(detail?.employee_balance_list?.job_check_list?.started_at).format("HH:mm") }}</span> -
              <span :class="detail?.employee_balance_list?.job_check_list?.job_time_graphic?.finished_at != detail?.employee_balance_list?.job_check_list?.finished_at ? 'text-danger' : ''">{{ moment(detail?.employee_balance_list?.job_check_list?.finished_at).format("HH:mm") }}</span>
            </template>
            <template v-if="detail?.employee_balance_list?.type == 'BONUS'">
              Бонус
            </template>
          </div>
          <div class="col-md-2 col-sm-4">
            <span class="text-success">{{ detail?.employee_balance_list?.amount }} {{ $t('general.rub') }}</span>
          </div>
          <hr>
        </div>
      </div>
      <div class="text-muted text-sm-center cursor-pointer" style="position: absolute; bottom: 5px; font-size: 11px; right: 7px;">
        <a data-bs-toggle="collapse" @click.prevent="openBlock(key)" :href="`#multiCollapseExample${key}`" class="text-decoration-none text-muted p-2">
          {{ openBlockIds.includes(key) ? $t('general.hide') : $t('general.more') }}
          <font-awesome-icon :icon="openBlockIds.includes(key) ? 'chevron-up' : 'chevron-down'" class="ms-4" />
        </a>
      </div>
    </div>
  </div>

  <!-- <p class="mt-3 fw-bold">Декабрь 2022</p> -->
  <!-- <div class="card mt-3" v-for="i in 5" :key="i">
    <div class="card-body p-4 position-relative ">
      <div class="row bg-white">
        <div class="col border-end">
          1 Ноября 2022
        </div>
        <div class="col border-end">
          22:00
        </div>
        <div class="col border-end">
          Магнит ООО
        </div>
        <div class="col border-end">
          Магнит 1 филиал
        </div>
        <div class="col border-end">
          Кассир
        </div>
        <div class="col border-end">
          **** **** **** 1234
        </div>
        <div class="col text-end">
          <span class="fw-bold">+ 50,000 руб</span>
        </div>
      </div>
      <div class="collapse multi-collapse" :id="`multiCollapseExample${i}`">
        <hr class="mt-4 mb-4">
        <div class="row bg-white">
          <div class="col border-end">
            Дата сдельки 1 Ноября
          </div>
          <div class="col border-end">
            Номер документа N 2525
          </div>
          <div class="col border-end">
            Втрой палавина зарплаты 11.2022
          </div>
          <div class="col">
            **** **** **** 1234
          </div>
        </div>
      </div>
      <div class="text-muted text-sm-center cursor-pointer" style="position: absolute; bottom: 5px; font-size: 11px; right: 7px;">
        <a data-bs-toggle="collapse" @click.prevent="openBlock(i)" :href="`#multiCollapseExample${i}`" class="text-decoration-none text-muted">
          {{ openBlockIds.includes(i) ? $t('general.hide') : $t('general.more') }}
          <font-awesome-icon :icon="openBlockIds.includes(i) ? 'chevron-up' : 'chevron-down'" class="ms-4" />
        </a>
      </div>
    </div>
  </div> -->
</template>

<script setup>
import Layout from '@/Layouts/Personal/AppLayout.vue'
import { ref } from 'vue'
import AddCreditCard from './Modals/AddCreditCard.vue'
import moment from 'moment'
import { useFixedNumber } from '@/Hooks/useFixedNumber'
import { useMinuteToHours } from '@/Hooks/useMinuteToHours'

defineOptions({ layout: Layout })

defineProps({
  title: String,
  transfers: Object,
  totalSum: Number,
  totalTime: Number
});

const openBlockIds = ref([])

const openAddCreditCardPopup = ref(false)

const openBlock = (id) => {
  if(openBlockIds.value.includes(id)){
      openBlockIds.value.splice(openBlockIds.value.indexOf(id), 1);
      return;
   }
   openBlockIds.value.push(id);
}
</script>
