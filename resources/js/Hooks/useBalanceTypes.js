const types = [
  {
    name: 'Смена',
    value: 'SHIFT'
  },
  {
    name: 'Бонус',
    value: 'BONUS'
  },
  {
    name: 'Перевод',
    value: 'TRANSFER'
  }
]

export function useBalanceTypes() {
  return {
    types,
  }
}

