<template>
  <div>
    <h2>Hello from Vue 3!</h2>
    <p>This is a Vue 3 component embedded in Silverstripe.</p>
  </div>
</template>

<script>
import { apiFetch } from './utils/api';

export default {
  name: 'App',
  data() {
    return {
      apiData: null,
      loading: false,
      error: null,
    };
  },
  async mounted() {
    this.loading = true;
    try {
      // Example: fetch from /example-endpoint
      this.apiData = await apiFetch('/api/freshservice/tickets?per_page=1');
    } catch (err) {

      console.log("ng-aw ~ App.vue:27 ~ err:", err);

      this.error = err;
    } finally {
      this.loading = false;
    }
  },
};
</script>

<style scoped>
h2 {
  color: #42b983;
}
</style>
