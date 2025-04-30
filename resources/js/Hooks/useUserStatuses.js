const statuses = [
  {
    name: 'Прогресс',
    value: 'PROGRESS'
  },
  {
    name: 'Подтвержденный',
    value: 'CONFIRMED'
  },
  {
    name: 'Отклоненный',
    value: 'REJECTED'
  }
]

export function useUserStatuses() {
  return {
    statuses,
  }
}

