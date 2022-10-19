import Homepage from '../components/homepage/Homepage.vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';
import * as time from '../time';
import * as ui from '../ui';
import T from '../lang';


OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.IndexPayload();
  const bannerPayload = types.payloadParsers.UserDetailsPayload();
  const currentDate = new Date();  //  \OmegaUp\Time::get()
  var differenceDate = Date_diff(
    currentDate,
    bannerPayload.parent_email_verification_initial)

    if(differenceDate < 5)
    {
      ui.warning(T.AccountVerifyWarning + differenceDate + 'days left')
    }
    else if(differenceDate <=7){
      ui.warning(T.AccountVerifyWarning + differenceDate + 'days left')
    }

  const ranking = payload.userRank.map((user, index) => ({
    rank: index + 1,
    country: user.country_id,
    username: user.username,
    classname: user.classname,
    score: user.score,
    problems_solved: user.problems_solved,
  }));


  new Vue({
    el: '#main-container',
    components: {
      'omegaup-homepage': Homepage,
    },
    render: function (createElement) {
      return createElement('omegaup-homepage', {
        props: {
          coderOfTheMonth: payload.coderOfTheMonthData
            ? payload.coderOfTheMonthData.all
            : null,
          coderOfTheMonthFemale: payload.coderOfTheMonthData
            ? payload.coderOfTheMonthData.female
            : null,
          currentUserInfo: payload.currentUserInfo,
          rankTable: {
            page: 1,
            length: 5,
            isIndex: true,
            isLogged: false,
            availableFilters: [],
            filter: '',
            ranking: ranking,
            resultTotal: ranking.length,
          },
          schoolsRank: {
            page: 1,
            length: 5,
            showHeader: true,
            rank: payload.schoolRank,
            totalRows: payload.schoolRank.length,
          },
          schoolOfTheMonth: payload.schoolOfTheMonthData,
        },
      });
    },
  });
});
 
function userAge(): number | null {
  if (this.birthDate === null) {
    return null;
  }
  return time.getDifferenceInCalendarYears(this.birthDate);
}

function Date_diff(currentDate: Date, parent_email_verification_initial: Date) {
   //calculate time difference  
  var time_difference = currentDate.getTime() - parent_email_verification_initial.getTime();  

  //calculate days difference by dividing total milliseconds in a day  
  var days_difference = time_difference / (1000 * 60 * 60 * 24); 
  return days_difference; 
}
