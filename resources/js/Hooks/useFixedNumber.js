export function useFixedNumber(number, point = 2) {
  number = +number

  return typeof number === 'number'
    ? number.toFixed(point).replace(/\.?0+$/, '')
    : 0
}
