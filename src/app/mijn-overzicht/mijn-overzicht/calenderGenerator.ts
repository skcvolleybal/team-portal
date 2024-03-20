import { Injectable } from '@angular/core';

import * as ICAL from 'ical.js'; // Import the ical.js library

import { WordPressService } from '../../core/services/request.service';
import { StateService } from 'src/app/core/services/state.service';


// @Component({
//     selector: 'app-calendar',
//     templateUrl: './calendar.component.html',
//     styleUrls: ['./calendar.component.css']
// })

@Injectable({
  providedIn: 'root' // This configures the service to be provided at the root level
})

export class calenderGenerator {

    dagen: any[];
    errorMessage: string;


    constructor(
        private joomalService: WordPressService,
        private stateService: StateService
      ) {}

    generateICalendar() {
      const calendar = new ICAL.Component(['vcalendar', [], []]);

      const dates = this.joomalService.GetMijnOverzicht().subscribe(
        (response) => {
            this.dagen = response;

            if (!this.dagen) {
              alert("there are no events to add to your calender.");
            }

            const serializedCalendar = this.CreateCalenderString(this.dagen, calendar);

            const url = this.CreateDownloadLink(serializedCalendar)

            const downloadLink = document.createElement('a');

            downloadLink.href = url;
            downloadLink.download = 'SKCVolleyballCalender.ics';
      

            // You can use serializedCalendar as needed


            downloadLink.click();

            // Clean up the URL created for the Blob
            URL.revokeObjectURL(url);
        


          },
          (error) => {
            console.log(error);
            if (error.status === 500) {
              this.errorMessage = error.error.message;
            }
          }
        );
    }

    CreateGameString(dagen, calender) {
      dagen.forEach(dag => {
        if (dag.speeltijden.length > 0) {
          var bardienst = false
          const event = new ICAL.Component('vevent');
          const eventStart = ICAL.Time.fromJSDate(new Date (this.toJSDate(dag, bardienst)));
          const eventEnd = eventStart.clone();
          eventEnd.addDuration(ICAL.Duration.fromSeconds(2 * 60 * 60)); // Add 2 hours
          event.addPropertyWithValue('dtstart', eventStart);
          event.addPropertyWithValue('dtend', eventEnd);
          event.addPropertyWithValue('summary', this.GetGameTitle(dag));
          event.addPropertyWithValue('description', this.GetGameDescription(dag));
          calender.addSubcomponent(event);
        }
        if (dag.bardiensten.length > 0) {
          var bardienst = true
          const event = new ICAL.Component('vevent');
          const eventStart = ICAL.Time.fromJSDate(new Date (this.toJSDate(dag, bardienst)));
          const eventEnd = eventStart.clone();
          eventEnd.addDuration(ICAL.Duration.fromSeconds(2 * 60 * 60)); // Add 2 hours
          event.addPropertyWithValue('dtstart', eventStart);
          event.addPropertyWithValue('dtend', eventEnd);
          event.addPropertyWithValue('summary', this.GetBarTitle(dag));
          calender.addSubcomponent(event);
        }
      });
      return calender
    }


    toJSDate(dag, bardienst) {
      if (!bardienst) {
        return dag.date + "T" + dag.speeltijden[0].tijd + ":00"
      } else {
        return dag.bardiensten[0].date + "T" + this.GetTimeFromShiftNumber(dag.bardiensten[0].shift)
      }
      
    }

    GetGameTitle(dag) {
      if (dag.speeltijden.length > 0) {
        return dag.speeltijden[0].wedstrijden[0].teams
      }
    }

    GetGameDescription(dag) {
      if (dag.speeltijden.length > 0) {
        return "Scheidsrechter: " + dag.speeltijden[0].wedstrijden[0].scheidsrechter + '\n' +
                    "Tellers: " + dag.speeltijden[0].wedstrijden[0].tellers[0] + ", " + dag.speeltijden[0].wedstrijden[0].tellers[1] + '\n' +
                    "1e Zaalwacht " + dag.eersteZaalwachtShortNotation
                    + ", 2e Zaalwacht " + dag.tweedeZaalwachtShortNotation;
      } else {
        return "";
      }
    }

    CreateCalenderString(dagen, calender) {
      console.log (dagen)

      calender = this.CreateGameString(dagen, calender)

      // CreateTelString(dagen, calender)

      // CreateScheidsString(dagen, calender)

      
      return calender.toString();
    }


    GetBarTitle(dag) {
      if (dag.bardiensten[0].isBhv) {
        return "BHV dienst SKC"
      } else {
        return "Bardienst SKC"
      }
    }


    CreateDownloadLink(serializedCalendar) {
      const blob = new Blob([serializedCalendar], { type: 'text/calender' });
      const url = URL.createObjectURL(blob);
      return url;
    }

    GetTimeFromShiftNumber(shift) {
      switch(shift) {
        case 1:
          return "20:00";
          break;
        case 2:
          return "22:00";
          break;
        case 3:
          return "00:00";
          break;
        default:
          return "";
          break;
      }
    }
}