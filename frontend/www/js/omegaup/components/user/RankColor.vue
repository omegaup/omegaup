<template>
  <span v-bind:class="classname">{{ username }}</span>
</template>

<script>
export default {
  props: {
    username: String,
    classname: String,
  }
}
</script>

<style>
.user-rank-beginner,
.user-rank-specialist,
.user-rank-expert,
.user-rank-master,
.user-rank-international-master {
  font-weight: bold;
}

.user-rank-beginner {
   color: #919191;
}

.user-rank-specialist {
  color: #598C4C;
}

.user-rank-expert {
  color: #1C52C7;
}

.user-rank-master {
  color: #F0C245;
}

.user-rank-international-master {
  color: #CB000A;
}
</style>
