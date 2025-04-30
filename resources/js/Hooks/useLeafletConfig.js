const layers = [
  {
    url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
    name: 'OpenStreet',
    visible: false,
    format: 'image/png',
    attribution: ''
  },
  {
    url: 'https://mt1.google.com/vt/lyrs=m@115&hl=en&x={x}&y={y}&z={z}&s=Galil',
    name: 'Google',
    visible: true,
    format: 'image/png',
    attribution: ''
  },
  {
    url: 'https://mt.google.com/vt/lyrs=s,h@115&hl=en&x={x}&y={y}&z={z}&s=Galil',
    name: 'Google Satelite',
    visible: false,
    format: 'image/png',
    attribution: ''
  },
  {
    url: 'http://vec{01,02,03,04}.maps.yandex.net/tiles?l=map&lang=ru-RU&v=2.26.0&x={x}&y={y}&z={level}',
    name: 'Yandex Map',
    visible: false,
    format: 'image/png',
    attribution: ''
  }
];

const zoom = 7;
const center = [55.938817691492545, 37.79296875000001];

export function useLeafletConfig() {
  return {
    layers,
    zoom,
    center,
  };
}
