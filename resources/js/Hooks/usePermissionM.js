import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const user = computed(() => usePage().props.auth.user || null);
const permissions = computed(() => usePage().props.permissionM || null);

// 1 - Финансист компании
// 2 - Финансист филиалов
// 3 - HR компании
// 4 - HR филиалов

export function usePermissionM(ids) {
  if(user && permissions) {
    if(user.value.role == 'COMPANY' || user.value.role == 'ADMIN') {
      return true
    } else if(user.value.role == 'MODERATOR') {
      for(let i in permissions.value) {
        for(let x in ids) {
          if(+ids[x] == +permissions.value[i]) {
            return true
          }
        }
      }
    }
  }
}
