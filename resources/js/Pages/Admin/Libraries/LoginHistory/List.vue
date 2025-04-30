<template>
  <Head :title="title" />

  <div class="card mb-4">
    <div class="card-body">
      <div class="row bg-white align-items-center">
        <div class="col-6">
          <span class="fw-bold text-success">{{ title }}</span>
        </div>
        <div class="col-6">
          <Link class="edit-action-link" :href="route('admin.positions.create')">
            <button type="button" class="btn float-end btn-success">
              <font-awesome-icon icon="plus" />
              {{ $t("general.add") }}
            </button>
          </Link>
        </div>
      </div>
    </div>
  </div>

  <div class="mt-3">
    <div class="card mt-3" v-for="item in loginHistory" :key="item.id">
      <div class="card-body p-3 position-relative">
        <div class="row bg-white align-items-center">
          <div class="col-md-1 col-sm-12 border-end">{{ item.id }}</div>
          <div class="col-md-9 col-sm-12 border-end">{{ item.username }}</div>
          <div class="col-md-2 col-sm-12 actions">
<!--            <Link-->
<!--              class="edit-action-link"-->
<!--              :href="route('admin.positions.edit', item.id)"-->
<!--            >-->
<!--              <font-awesome-icon icon="pen-to-square" class="edit" />-->
<!--            </Link>-->
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import Layout from '@/Layouts/Admin/AppLayout.vue'

defineOptions({ layout: Layout })

defineProps({
  title: String,
  loginHistory: Object
})

</script>

