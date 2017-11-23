<template>
  <div class="col-md-8">
    <div class="panel panel-default">
      <div id="carousel-display" class="carousel slide" data-ride="carousel" data-interval="3000">
      <!-- Indicators -->
      <ol class="carousel-indicators">
        <li v-for="(slide, index) of slides"
                    data-target="#carousel-display"
                    v-bind:class="{active: !index}"
                    v-bind:data-slide-to="index"></li>
      </ol>

      <!-- Wrapper for slides -->
      <div class="carousel-inner">
        <div class="item" v-for="(slide, index) of slides" v-bind:class="{active: !index}">
          <img v-bind:src="slide.image">
          <div class="carousel-caption">
            <h3>{{ localized(slide.title) }}</h3>
            <p>{{ localized(slide.description) }}</p>
          </div>
        </div>
      </div>
      <!-- Controls -->
      <a class="left carousel-control" href="#carousel-display" data-slide="prev">
        <span class="icon-prev"></span>
      </a>
      <a class="right carousel-control" href="#carousel-display" data-slide="next">
        <span class="icon-next"></span>
      </a>
      </div>
    </div>
  </div>
</template>

<script>
import {T} from '../omegaup.js';

export default {
  props: {
    json: Object,
  },
  computed: {
    slides: function() {
      let filteredSlides = [];
      this.json.forEach(slide => {
        if (slide._meta != 'comments') {
          filteredSlides.push(slide);
        }
      });
      return filteredSlides;
    },
  },
  data: function() {
    return {
    };
  },
  methods: {
    localized: function(text) {
      return text[T.locale];
    },
  },
}
</script>