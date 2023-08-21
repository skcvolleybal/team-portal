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
            console.log(response)
            this.dagen = response;
            // console.log(response)

            if (!this.dagen) {
              alert("there are no events to add to your calender.");
            }

            console.log(this.dagen)
            this.dagen.forEach(dag => {
              const event = new ICAL.Component('vevent');
              const eventStart = ICAL.Time.fromJSDate(new Date (this.toJSDate(dag)));
              const eventEnd = eventStart.clone();
              eventEnd.addDuration(ICAL.Duration.fromSeconds(2 * 60 * 60)); // Add 2 hours
              event.addPropertyWithValue('dtstart', eventStart);
              event.addPropertyWithValue('dtend', eventEnd);
              event.addPropertyWithValue('summary', this.GetEventTitle(dag));
              event.addPropertyWithValue('description', this.GetEventDescription(dag));
              calendar.addSubcomponent(event);
            });

            const serializedCalendar = calendar.toString();
            // You can use serializedCalendar as needed
            console.log(serializedCalendar);

            const blob = new Blob([serializedCalendar], { type: 'text/calender' });

            const url = URL.createObjectURL(blob);

            const downloadLink = document.createElement('a');

            downloadLink.href = url;
            downloadLink.download = 'SKCVolleyballCalender.ics';

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

    toJSDate(dag) {
      if (dag.speeltijden) {
        return dag.date + "T" + dag.speeltijden[0].tijd + ":00"
      }
      
    }

    GetEventTitle(dag) {
      if (dag.speeltijden) {
        return "Volleyball match " + dag.speeltijden[0].wedstrijden[0].teams
      }
    }

    GetEventDescription(dag) {
      if (dag.speeltijden) {
        return "Scheidsrechter: " + dag.speeltijden[0].wedstrijden[0].scheidsrechter + '\n' +
                    "Tellers: " + dag.speeltijden[0].wedstrijden[0].tellers[0] + ", " + dag.speeltijden[0].wedstrijden[0].tellers[1];
      }
    }
}