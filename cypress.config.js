import { defineConfig } from "cypress";

export default defineConfig({
  e2e: {
    baseUrl: 'https://test-api.ddev.site',
    setupNodeEvents(on, config) {
      // implement node event listeners here
    },
  },
});
