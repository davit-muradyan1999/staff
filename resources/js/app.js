import '../../public/css/bootstrap.css';
import '../../public/css/common.css';
import '@vueform/multiselect/themes/default.css'
import 'leaflet/dist/leaflet.css'
import 'v-calendar/dist/style.css';
import { createApp, h } from 'vue';
import { InertiaProgress } from '@inertiajs/progress';
import { createInertiaApp, Link, Head } from '@inertiajs/vue3';
import { i18nVue } from 'laravel-vue-i18n'
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';
import Multiselect from '@vueform/multiselect'
import ShowMessage from '@/Components/ShowMessage.vue'
import ModalDelete from '@/Components/ModalDelete.vue'
import VueClickAway from 'vue3-click-away'
import Notifications from '@kyvg/vue3-notification'
import { LMap, LTileLayer, LMarker, LControlLayers, LPopup, LIcon } from '@vue-leaflet/vue-leaflet'
import Pagination from '@/Components/Pagination.vue'
import CKEditor from '@ckeditor/ckeditor5-vue'

// FontAwesome Icons
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faPencil, faPlus, faMinus, faTrash, faTable, faArrowLeft, faArrowDown, faChevronUp, faChevronRight, faChevronLeft, faChevronDown, faGraduationCap, faRotate, faTimes, faUser, faUsers, faBriefcase, faBookmark, faXmark, faLocationDot, faLink, faMagnifyingGlass, faCheck, faPenToSquare, faBars, faHeart, faUserCheck, faCalendarDays, faCircleInfo, faStar, faBan, faRotateLeft, faList, faEye, faUserPlus, faAddressCard, faIdCardClip, faFileLines, faCircleCheck, faPaperclip, faAt, faBuildingColumns } from '@fortawesome/free-solid-svg-icons'
import { faFacebook, faGoogle, faYandex } from '@fortawesome/free-brands-svg-icons';
library.add(faPencil, faPlus, faMinus, faTrash, faTable, faArrowLeft, faArrowDown, faChevronUp, faChevronRight, faChevronLeft, faChevronDown, faGraduationCap, faRotate, faTimes, faUser, faUsers, faFacebook, faGoogle, faYandex, faBriefcase, faBookmark, faXmark, faLocationDot, faLink, faMagnifyingGlass, faCheck, faPenToSquare, faBars, faHeart, faUserCheck, faCalendarDays, faCircleInfo, faStar, faBan, faRotateLeft, faList, faEye, faUserPlus, faAddressCard, faIdCardClip, faFileLines, faCircleCheck, faPaperclip, faAt, faBuildingColumns );
InertiaProgress.init()

const cleanApp = () => {
  document.getElementById('app').removeAttribute('data-page')
}

let Layout = '';
createInertiaApp({
  resolve: (name) => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
    let page = pages[`./Pages/${name}.vue`]
    page.default.layout = page.default.layout || Layout
    return page
  },
  setup({ el, App, props, plugin }) {
    return createApp({ render: () => h(App, props) })
      .mixin({ methods: { route } })
      .use(plugin)
      .use(ZiggyVue, Ziggy)
      .use(VueClickAway)
      .use(Notifications)
      .use(CKEditor)
      .use(i18nVue, {
        lang: props?.initialPage?.props?.locale || 'ru',
        resolve: async lang => {
          const langs = import.meta.glob('../../lang/*.json');
          return await langs[`../../lang/${lang}.json`]();
        }
      })
      .component('Link', Link)
      .component('Head', Head)
      .component('Multiselect', Multiselect)
      .component('ShowMessage', ShowMessage)
      .component('ModalDelete', ModalDelete)
      .component('LMap', LMap)
      .component('LTileLayer', LTileLayer)
      .component('LMarker', LMarker)
      .component('LControlLayers', LControlLayers)
      .component('LPopup', LPopup)
      .component('LIcon', LIcon)
      .component('font-awesome-icon', FontAwesomeIcon)
      .component('Pagination', Pagination)
      .mount(el)
  },
}).then(cleanApp);
