<template>
    <div class="wait_for_ajax panel panel-default" id="coders_list" >
	<div class="panel-heading">
        <ul class="nav nav-tabs">
            <li class="active" v-on:click="show = true"><a href="#top-coder-of-the-month" data-toggle="tab">{{UI.formatString(T.codersOfTheMonth)}}</a></li>
            <li v-on:click="show = false"><a href="#coder-of-the-month-list" data-toggle="tab">{{UI.formatString(T.codersOfTheMonthList)}}</a></li>
        </ul>
	</div>
	<div class="panel-body">
	</div>
		<table class="table table-striped table-hover" id="coder-of-the-month-table">
            <thead>
                <tr>
                    <th></th>
                    <th>{{UI.formatString(T.codersOfTheMonthCountry)}}</th>
                    <th>{{UI.formatString(T.codersOfTheMonthUser)}}</th>
                    <th>{{UI.formatString(T.codersOfTheMonthDate)}}</th>
                </tr>
            </thead>
            <tbody v-if="show">
                <tr v-for="coder in coders">
                    <td><img v-bind:src="coder.gravatar_32"/></td>
                    <td><img v-bind:src="'/media/flags/' + coder.country_id.toLowerCase() + '.png'" v-if="coder.country_id != null"/></td>
                    <td>{{coder.username}}</td>
                    <td>{{coder.date}}</td>
                </tr>
            </tbody>
            <tbody v-if="!show">
                <tr v-for="coder in coders_monthly">
                    <td><img v-bind:src="coder.gravatar_32"/></td>
                    <td><img v-bind:src="'/media/flags/' + coder.country_id.toLowerCase() + '.png'" v-if="coder.country_id != null"/></td>
                    <td>{{coder.username}}</td>
                    <td>{{coder.date}}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
import {API, T} from '../../omegaup.js';
import UI from '../../ui.js';

export default {
    props: {},
    computed: {
    },
    data: function() {
        return {
            T: T,
            UI: UI,
            coders: [],
            coders_monthly: [],
            show: true
        };
    },
    created: function() {
        //top coder of the month
        var self = this;
        API.User.coderOfTheMonthList().then(function(response) {
            self.coders = response.coders;
        });

        //coder of the month list
        var today = new Date();
        var mm = today.getMonth() + 1;
        var dd = today.getDate();

        var todayString = [
            today.getFullYear(),
            (mm>9 ? '' : '0') + mm,
            (dd>9 ? '' : '0') + dd
        ].join('-');
        API.User.coderOfTheMonthList({date: todayString}).then(function(response) {
            self.coders_monthly = response.coders;
        });
    }
};
</script>
