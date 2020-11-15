<template>
  <div>
  <div class="container bottom-space">
    <h3>Create a ThinkFactory</h3>
    <div class="alert alert-danger" role="alert" v-if="error">
      Error: Could not create a Think Factory. Please try again.
    </div>
    <form v-on:submit.prevent="">
      <div class="form-group">
        <label class="mb-0" for="questionInput">Question:</label>
        <small class="form-text text-muted">e.g. Where should we eat?</small>
        <input
          v-model="question"
          type="text"
          name="questionInput"
          class="form-control"
          placeholder="Enter question"
          required
        />
      </div>
      <div class="form-group">
        <label class="mb-0">Possible Answers (2 minimum):</label>
        <small class="form-text text-muted">e.g. Burger King</small>
        <div v-for="(answer, index) in answers" :key="index">
          <div class="input-group mb-3">
            <input
              v-model="answer.value"
              type="text"
              class="form-control"
              :placeholder="`Enter answer ${index + 1}`"
            />
            <div class="input-group-append" v-if="answers.length > 2">
              <button
                @click.prevent="deleteAnswer(index)"
                class="btn btn-outline-secondary"
                type="button"
              >
                Remove
              </button>
            </div>
          </div>
        </div>
      </div>
      <button
        @click.prevent="addAnswerField"
        class="btn btn-primary btn-sm float-right mb-5"
      >
        + Add
      </button>
    </form>  
  </div>
  <div :class="{'container' : !isMobile}">
  <button
        :disabled="!formValid || submitting"
        @click="createThinkFactory"
        class="btn btn-success btn-lg"
        :class="{'button-bottom' : isMobile, 'float-right': !isMobile}"
      >
        {{ submitting ? 'Sumbitting...' : 'Create'}}
      </button>
  </div>
  </div>
</template>

<style scoped>
.button-bottom {
  position: fixed !important;
  bottom: 0;
  width: 100%;
  margin: 0;
  border-radius: 0;
}
.bottom-space {
  margin-bottom: 150px;
}
</style>

<script>
import shared from "../shared";
export default {
  data() {
    return {
      isMobile: true,
      submitting: false,
      error: false,
      question: "",
      answers: [{ value: "" }, { value: "" }],
    };
  },
  mounted() {
    this.isMobile = window.mobileAndTabletCheck()
  },
  computed: {
    formValid() {
      return (
        this.question.trim() &&
        this.answers.length >= 2 &&
        this.answers.every(({ value }) => value.trim())
      );
    },
  },
  methods: {
    deleteAnswer(index) {
      this.answers.splice(index, 1);
    },
    addAnswerField() {
      this.answers.push({ value: "" });
    },
    async createThinkFactory() {
      try {
        let userId = await shared.getUserId();

        const formData = {
          userId,
          question: this.question,
          answers: this.answers.map(({ value }) => value),
        };

        const {
          data: { factoryId },
        } = await axios.post("/api/factory/create", formData);

        if (factoryId) {
          this.$router.push({ path: `/view/${factoryId}` }); // -> /user/123
        } else {
          this.error = true;
        }
      } catch (err) {
        console.log("error:", err);
        this.error = true;
      }
    },
  },
};
</script>
