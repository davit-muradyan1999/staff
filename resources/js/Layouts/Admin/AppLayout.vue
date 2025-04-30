<template>
  <Nav />
  <div id="layoutSidenav">
    <notifications style="z-index: 9999999;" />
    <Sidebar />
    <div id="layoutSidenav_content">
      <main>
        <div class="container-fluid px-4 py-2">
          <ShowMessage
            v-if="$page?.props?.successMessage || $page?.props?.errorMessage || Object.keys($page.props.errors).length > 0"
          />
          <slot></slot>
        </div>
      </main>
      <Footer v-if="false" />
    </div>
  </div>
</template>

<script setup>
import '@/Scripts/Personal/bootstrap.bundle.min.js'
import Nav from './Blocks/Nav.vue'
import Sidebar from './Blocks/Sidebar.vue'
import Footer from './Blocks/Footer.vue'

document.body.classList.add('sb-nav-fixed')
document.body.removeAttribute('style')

setTimeout(function() {
  setSidebarToggle()
}, 1000);

const setSidebarToggle = async (value) => {
  const sidebarToggle = document.body.querySelector('#sidebarToggle');
  if (sidebarToggle) {
    sidebarToggle.addEventListener('click', event => {
      event.preventDefault();
      document.body.classList.toggle('sb-sidenav-toggled');
      localStorage.setItem('sb|sidebar-toggle', document.body.classList.contains('sb-sidenav-toggled'));
    });
  }
}

document.body.classList.remove('modal-open')
// document.body.classList.remove('sb-nav-fixed')
</script>
