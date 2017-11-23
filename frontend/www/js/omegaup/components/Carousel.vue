<template>
  <div class="col-md-8">
    <div class="panel panel-default">
      <div class="carousel slide"
           data-interval="3000"
           data-ride="carousel"
           id="carousel-display">
        <!-- Indicators -->
        <ol class="carousel-indicators">
          <li data-target="#carousel-display"
              v-bind:class="{active: !index}"
              v-bind:data-slide-to="index"
              v-for="(slide, index) of slides"></li>
        </ol><!-- Wrapper for slides -->
        <div class="carousel-inner">
          <div class="item"
               v-bind:class="{active: !index}"
               v-for="(slide, index) of slides">
            <img v-bind:src="slide.image">
            <div class="carousel-caption">
              <h3>{{ localized(slide.title) }}</h3>
              <p>{{ localized(slide.description) }}</p>
            </div>
          </div>
        </div><!-- Controls -->
         <a class="left carousel-control"
             data-slide="prev"
             href="#carousel-display"><span class="icon-prev"></span></a> <a class=
             "right carousel-control"
             data-slide="next"
             href="#carousel-display"><span class="icon-next"></span></a>
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
  data: function() { return {};},
  methods: {
    localized: function(text) { return text[T.locale];},
  },
}
</script>
