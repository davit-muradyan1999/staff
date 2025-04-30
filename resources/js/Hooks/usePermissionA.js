import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const user = computed(() => usePage().props.auth.user || null);
const permissions = computed(() => usePage().props.permissionA || null);

// 7 - Поддержка сотрудников
// 8 - Поддержка компаний

export function usePermissionA(ids) {
  if(user && permissions) {
    if(user.value.role == 'ADMIN') {
      return true
    } else if(user.value.role == 'ADMINISTRATOR') {
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
