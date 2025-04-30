export function useTimeFormat(time) {
  let hours = +time.hours < 10 ?  `0${+time.hours}` : time.hours;
  let minutes = +time.minutes < 10 ?  `0${+time.minutes}` : time.minutes;

  return `${hours}:${minutes}`
}
