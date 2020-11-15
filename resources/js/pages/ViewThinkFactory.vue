<template>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <h3 v-if="!error && whatsAppMessage" class="mb-3">ThinkFactory code:  
          <a :href="'https://api.whatsapp.com/send?text=' + this.whatsAppMessage">{{ factoryId.toUpperCase() }}</a>
        </h3>
        <div v-if="error">
          <div class="alert alert-danger" role="alert">
            This ThinkFactory is no longer available or the room code is invalid
          </div>
          <router-link
            class="btn btn-primary btn-block"
            :to="{ name: 'create-factory' }"
          >
            Create a new ThinkFactory
          </router-link>
          <router-link
            class="btn btn-secondary btn-block"
            :to="{ name: 'home' }"
          >
            Join a different room
          </router-link>
        </div>
        <div class="card" v-if="!error">
          <div class="card-header">
            Question:
            <span class="font-weight-bold">{{ factory.question }}</span>
          </div>
          <div
            class="card-body"
            v-for="(count, answerKey) in votes"
            v-bind:key="answerKey"
          >
            <div class="container mb-2">
              <div class="row flex-row">
                <div class="col p-0">
                  {{ factory[answerKey] }} ({{ count }} votes)
                </div>
                <div>
                  <div v-if="voted === answerKey">
                    Your vote
                  </div>
                  <button
                    v-if="canVote"
                    @click="vote(answerKey)"
                    class="btn btn-success btn-sm"
                  >
                    Vote
                  </button>
                </div>
              </div>
            </div>

            <div class="progress" style="height: 20px">
              <div
                class="progress-bar"
                role="progressbar"
                v-bind:style="{ width: percentages[answerKey] }"
                aria-valuemin="0"
                aria-valuemax="100"
              ></div>
            </div>
          </div>
        </div>

        <div class="card" v-if="!error">
          <router-link
      class="btn btn-primary btn-sm button-bottom"
      :to="{ name: 'create-factory' }"
      >Create a ThinkFactory</router-link
    >
        </div>

      </div>
    </div>
  </div>
</template>

<style>
  .button-bottom {
    position: fixed !important;
    bottom: 10px;
    width: 90%;
  }
</style>

<script>
import shared from "../shared";
export default {
  data() {
    return {
      whatsAppMessage: "",
      error: false,
      voted: null,
      userId: "",
      factoryId: this.$route.params.id,
      factory: {},
      votes: {},
      totalVotes: 0,
      interval: null,
    };
  },
  mounted() {
    console.log("Component mounted.", this.$route.params.id);
    this.setUserId();
    this.getThinkFactory(this.factoryId);
    this.getVotes(this.factoryId);
    this.interval = setInterval( () => {
      this.getVotes(this.factoryId)
    }, 5000);
  },
  beforeDestroy() {
    if (this.interval) {
      clearInterval(this.interval);
      this.interval = null;
    }
  },
  computed: {
    canVote() {
      return this.userId !== this.factory.creatorId && !this.voted;
    },
    percentages() {
      const votePercentages = {};
      _.forEach(this.votes, (count, key) => {
        votePercentages[key] =
          (this.totalVotes ? (count / this.totalVotes) * 100 : 0) + "%";
      });
      return votePercentages;
    },
  },
  methods: {
    async setUserId() {
      this.userId = await shared.getUserId();
      this.getUserVote(this.userId)
    },
    async getThinkFactory(id) {
      try {
        const response = await axios.get(`/api/factory/${id}`);
        this.factory = response.data;
        this.whatsAppMessage = `Answer: ${this.factory.question} by clicking here: ${window.location.href}`;
      } catch (err) {
        this.error = true;
      }
    },
    async getUserVote(userId) {
      const voteResponse = await axios.get(`/api/factory/${this.factoryId}/votes/${userId}`)
      if (voteResponse.data) {
        this.voted = voteResponse.data
      }
    },
    async vote(answerKey) {
      try {
        const voteResponse = await axios.post(
          `/api/factory/${this.factoryId}/votes`,
          {
            userId: this.userId,
            answerKey,
          }
        );
        this.totalVotes += 1
        this.votes[answerKey] += 1
        this.voted = answerKey

      } catch (err) {
        console.log(err);
        alert("Could not vote, please try again");
      }
    },
    async getVotes(id) {
      try {
        const votes = await axios.get(`/api/factory/${id}/votes`);
        this.votes = votes.data;
        this.totalVotes = 0;
        _.forEach(votes.data, (count, key) => {
          this.totalVotes += count;
        });
      } catch (err) {
        if (this.interval) {
          clearInterval(this.interval);
          this.interval = null;
        }
      }
    },
  },
};
</script>
