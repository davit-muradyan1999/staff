const statuses = [
  {
    name: 'Подтверждено',
    value: 'CONFIRMED'
  },
  {
    name: 'Отменено',
    value: 'CANCELED'
  }
]

export function useBonusStatuses() {
  return {
    statuses,
  }
}

