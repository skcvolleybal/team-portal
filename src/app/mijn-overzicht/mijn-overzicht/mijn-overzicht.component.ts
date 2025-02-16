import { Component, OnInit } from '@angular/core';
import { calenderGenerator } from '../../core/services/calenderGenerator';
import { RuilLijstComponent } from '../ruil-lijst/ruil-lijst.component';
import { MatDialog } from '@angular/material/dialog';

import {
  faCalendarCheck,
  faPlusSquare,
  faUser,
  faInfoCircle,
  faPeopleCarry,
  faCalendar,
  
} from '@fortawesome/free-solid-svg-icons';

import { WordPressService } from '../../core/services/request.service';
import { StateService } from 'src/app/core/services/state.service';
import { SwapService } from 'src/app/core/services/swap.service';

@Component({
  selector: 'teamportal-mijn-overzicht',
  templateUrl: './mijn-overzicht.component.html',
  styleUrls: ['./mijn-overzicht.component.scss'],
  providers: [calenderGenerator]
})

export class MijnOverzichtComponent implements OnInit {
  loading: boolean;
  taskIcon = faUser;
  scheidsrechterIcon = faUser;
  tellersIcon = faCalendarCheck;
  calenderIcon = faCalendar;
  openIcon = faPlusSquare;
  dagen: any[];
  errorMessage: string;
  dagenEmpty: boolean = false;
  user: any;
  zaalwacht = faPeopleCarry;

  showRuilLijst = false;

  proposed: { [key: number]: string } = {};
  isPressed: { [key: number]: boolean } = {};

  infoIcon = faInfoCircle;

  constructor(
    private wordPressService: WordPressService,
    private stateService: StateService,
    // private CalendarService: calenderGenerator,
    private SwapService: SwapService,
    private dialog: MatDialog
  ) {}

  ngOnInit() {
    this.loading = true;
    this.wordPressService.GetMijnOverzicht().subscribe(
      (response) => {
        console.log(response)
        this.dagen = response;
        this.loading = false;
        if (this.dagen.length == 0) {
          this.dagenEmpty = true;
        }
      },
      (error) => {
        console.log(error);
        if (error.status === 500) {
          this.errorMessage = error.error.message;
          this.loading = false;
        }
      }
    );

    this.stateService.isAuthenticated.subscribe((isAuthenticated) => {
      if (isAuthenticated) {
        this.ngOnInit();
      }
    });

    this.wordPressService.GetCurrentUser().subscribe((data) => {
      this.user = data;
    });
  }

  openModal() {
    this.dialog.open(RuilLijstComponent, {
      width: '600px', // Adjust size
      panelClass: 'custom-modal',
      data: { userid: this.user.id },
    });
  }

  // setProposed() {
  //   // When loading in the barshifts that a user has already selected to swap set these elements to the right properties.
  //   const bardiensten = this.dagen.map(obj => obj.bardiensten).flat();
  //   for (let dienst of bardiensten) {
  //     this.proposed[dienst.id] = 'Propose';
  //     this.isPressed[dienst.id] = false
  //   }
  //   this.SwapService.GetSwapsById(this.user.id).subscribe((response) => {
  //     for (let swapdienst of response) {
  //       this.proposed[swapdienst.scheduleid] = 'Proposed';
  //       this.isPressed[swapdienst.scheduleid] = true;
  //     }
  //   }, (error) => {
  //     console.log(error);
  //   })

  // }

  // generateCalender() {
  //   this.CalendarService.generateICalendar(this.user);
  // }

  // handleSwapClick(bardienst?) {
  //   console.log(bardienst.date)
  //   console.log(this.user)
  //   this.SwapService.GetAllSwaps().subscribe(response => {
  //     console.log(response)
  //   }, (error => {
  //     console.log(error)
  //   }))
  //   // console.log(bardienst)
  // }

  // proposeSwap(bardienst) {
  //   const shiftId = bardienst.id;
  //   this.isPressed[shiftId] = !this.isPressed[shiftId]; // Toggle state

  //   const newSwap = {
  //     ...bardienst,
  //     scheduleid: bardienst.id,
  //     userid: this.user.id
  //   }
  //   this.SwapService.ProposeSwap(this.isPressed[shiftId], newSwap).subscribe((response) => {
  //     this.proposed[shiftId] = this.isPressed[shiftId] ? "Proposed" : "Propose"
  //   },
  //   ((error) => {
  //     console.log(error)
  //     this.isPressed[shiftId] = !this.isPressed[shiftId]
  //     alert('Er is iets fout gegaan en je teamtaak zit niet in de ruillijst')
  //   })
  // )
  // }
}
