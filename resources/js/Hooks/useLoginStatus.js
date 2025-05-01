const status = [
  {
    name: 'Удачный',
    value: '1'
  },
  {
    name: 'Неудачный',
    value: '0'
  }
]

export function useLoginStatus() {
  return {
    status,
  }
}

