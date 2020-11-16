<template>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div v-if="!error && whatsAppMessage" class="text-center mb-3">
          <h4 class="mb-1">
            ThinkFactory code:
            <a href="" @click.prevent="" id="code">{{
              factoryId.toUpperCase()
            }}</a>
          </h4>
          <a
            :href="'https://api.whatsapp.com/send?text=' + this.whatsAppMessage"
            class="btn btn-link"
            >Share on whatsapp</a
          >
        </div>

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
                  <div v-if="voted === answerKey">Your vote</div>
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
        <router-link
      class="btn btn-light btn-block mt-3"
      :to="{ name: 'create-factory' }"
      >Create another ThinkFactory</router-link>
      </div>
      
    </div>
  </div>
</template>

<script>
import shared from "../shared";
export default {
  data() {
    return {
      isMobile: true,
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
    this.isMobile = window.mobileAndTabletCheck();
    this.setUserId().then(() => {
      this.getThinkFactory(this.factoryId);
      this.getVotes(this.factoryId);
      this.interval = setInterval(() => {
        this.getVotes(this.factoryId);
      }, 5000);
    });
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
      this.getUserVote(this.userId);
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
      const voteResponse = await axios.get(
        `/api/factory/${this.factoryId}/votes/${userId}`
      );
      if (voteResponse.data) {
        this.voted = voteResponse.data;
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
        this.totalVotes += 1;
        this.votes[answerKey] += 1;
        this.voted = answerKey;
        alert("Vote submitted!");
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
