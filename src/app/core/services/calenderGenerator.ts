import { Injectable } from '@angular/core';

import * as ICAL from 'ical.js'; // Import the ical.js library

import { WordPressService } from './request.service';
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

    diensten: any[];
    errorMessage: string;
    user: any;
    bardiensten: any[];
    telfluitdiensten: any[];


    constructor(
        private WordPressService: WordPressService,
        private stateService: StateService
      ) {}

    generateICalendar(user: any) {
      const calendar = new ICAL.Component(['vcalendar', [], []]);
      this.user = user;

      const dates = this.WordPressService.GetDienstenForUser().subscribe(
        (response) => {
            this.bardiensten = response[0];
            this.telfluitdiensten = response[1];
            if (!this.diensten && !this.telfluitdiensten) {
              alert("there are no events to add to your calender.");
            }

            const serializedCalendar = this.CreateCalenderString(calendar);

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

    AddEvents(calender) {
      this.bardiensten.forEach(bardienst => {
          calender.addSubcomponent(this.createBarEntry(bardienst));
      });

      this.telfluitdiensten.forEach(telfluitdienst => {
        calender.addSubcomponent(this.createTelFluitEntry(telfluitdienst));
      });

      return calender
    }

    createBarEntry(dienst) {
      const event = new ICAL.Component('vevent');
      const eventStart = ICAL.Time.fromJSDate(new Date (this.toJSDate(dienst)));
      const eventEnd = eventStart.clone();
      eventEnd.addDuration(ICAL.Duration.fromSeconds(2 * 60 * 60)); // Add 2 hours
      event.addPropertyWithValue('dtstart', eventStart);
      event.addPropertyWithValue('dtend', eventEnd);
      event.addPropertyWithValue('summary', this.GetBarTitle(dienst));
      return event;
    }

    
    createTelFluitEntry(dienst) {
      const event = new ICAL.Component('vevent');
      const eventStart = ICAL.Time.fromJSDate(new Date (dienst.timestamp));
      const eventEnd = eventStart.clone();
      eventEnd.addDuration(ICAL.Duration.fromSeconds(2 * 60 * 60)); // Add 2 hours
      event.addPropertyWithValue('dtstart', eventStart);
      event.addPropertyWithValue('dtend', eventEnd);
      event.addPropertyWithValue('summary', this.GetTelFluitTitle(dienst));
      return event;

    }

    toJSDate(dienst) {
      return dienst.bardag.date.date.slice(0,10) + " " + this.GetTimeFromShiftNumber(dienst.shift)
      
    }

    CreateCalenderString(calender) {

      calender = this.AddEvents(calender)
      
      return calender.toString();
    }


    GetBarTitle(dienst) {
      if (dienst.isBhv) {
        return "BHV dienst SKC"
      } else {
        return "Bardienst SKC"
      }
    }

    GetTelFluitTitle(dienst) {
      if (dienst.scheidsrechter_id == this.user.id) {
        return "Scheidsen SKC";
      } else {
        return "Tellen SKC";
      }
    }


    CreateDownloadLink(serializedCalendar) {
      const blob = new Blob([serializedCalendar], { type: 'text/calender' });
      const url = URL.createObjectURL(blob);
      return url;
    }

    GetTimeFromShiftNumber(shift) {
      console.log(shift);
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