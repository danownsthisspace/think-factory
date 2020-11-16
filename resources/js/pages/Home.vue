<template>
  <div class="container">
    <div class="text-center">
      <img class="logo" src="/img/logo.png" alt="ThinkFactory logo">
      <p class="font-weight-light">Get group answers, easily.</p>
    </div>
    
    <input
      v-model="factoryId"
      class="form-control form-control-lg mb-2"
      type="text"
      placeholder="Enter Code"
    />
    <button
      @click="joinThinkFactory"
      type="button"
      class="btn btn-secondary btn-lg btn-block mb-3"
    >
      Join a ThinkFactory
    </button>
    <h4 class="text-center">or</h4>
    <router-link
      class="btn btn-primary btn-lg btn-block"
      :to="{ name: 'create-factory' }"
      >Create a ThinkFactory</router-link
    >
  </div>
</template>

<style scoped>
    .logo {
        width: 100%;
        max-width: 600px;
        margin: 0 auto;
    }
</style>

<script>
export default {
  data() {
    return {
      factoryId: "",
    };
  },
  methods: {
    joinThinkFactory() {
      if (this.factoryId.trim().length !== 4) {
        alert("invlalid room code");
        return;
      }

      this.$router.push({ path: `/view/${this.factoryId}` }); // -> /user/123
    },
  },
};
</script>
