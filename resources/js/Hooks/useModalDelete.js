import { ref, reactive } from 'vue'

const isOpen = ref(false);
const url = ref('');
const data = reactive({});

export function useModalDelete() {
  return {
    isOpen,
    url,
    data,
  }
}
