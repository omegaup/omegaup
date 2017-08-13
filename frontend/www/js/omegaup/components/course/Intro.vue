<template>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h2 class="panel-title" >{{ T.courseDetails }} </h2>
		</div>
		<div class="row">
			<div class="col-md-6 col-md-offset-1" >
				<div id="course-details">
					<h2 id="name">{{ name }}</h2>
					<p id="description">{{ description }}</p>
				</div>

				<form id="start-course-form" v-on:submit.prevent="">					
					<button type="button" id="start-course-submit" class="btn btn-primary btn-lg" v-on:click="onSubmit">{{ T.startCourse }}</button>
				</form>				

			</div>
		</div>
	</div>
</template>

<script>
import {T} from '../../omegaup.js';

export default {
	props: {
		name: String,
		description: String
	},
	data: function() {
	    return {
	      T: T,
  		}
	},
	methods: {
		onSubmit() {
      		this.$emit('submit', this);
  		}
	}
}
</script>