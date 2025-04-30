<template>
  <div v-if="links?.length > 3">
    <div class="flex flex-wrap pagination">
      <template v-for="(link, key) in links" :key="key">
        <div
          v-if="link.url === null"
          :key="key"
          class="page-link"
          v-html="link.label"
        />
        <Link
          v-else
          class="page-link"
          :class="{ 'active': link.active }"
          :href="decodeURI(link.url).replace( /\[\d+\]/g, '[]')"
          v-html="link.label"
        />
      </template>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    links: Array,
  },
};
</script>
