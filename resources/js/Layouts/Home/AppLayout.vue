<template>
  <main class="flex-shrink-0">
    <Nav />
    <slot></slot>
  </main>
  <Footer />
</template>

<script setup>
import Nav from './Blocks/Nav.vue'
import Footer from './Blocks/Footer.vue'

document.body.classList.remove('sb-nav-fixed')
</script>
