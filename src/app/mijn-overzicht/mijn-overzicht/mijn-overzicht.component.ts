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
  faGavel,
  faAmbulance,
  faCalculator
  
} from '@fortawesome/free-solid-svg-icons';

import { WordPressService } from '../../core/services/request.service';
import { StateService } from 'src/app/core/services/state.service';
import { teamTask } from './teamTask';
import { wedstrijd } from './wedstrijd';

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
  bhvIcon = faAmbulance;
  tellersIcon = faCalculator;
  calenderIcon = faCalendar;
  openIcon = faPlusSquare;
  refIcon = faGavel;
  wedstrijden: wedstrijd[];
  diensten: any[];
  errorMessage: string;
  dagenEmpty: boolean = false;
  user: any;
  zaalwacht = faPeopleCarry;
  teamTasks: teamTask[] = [];

  showRuilLijst = false;

  proposed: { [key: number]: string } = {};
  isPressed: { [key: number]: boolean } = {};

  infoIcon = faInfoCircle;

  constructor(
    private wordPressService: WordPressService,
    private stateService: StateService,
    // private CalendarService: calenderGenerator,
    private dialog: MatDialog
  ) {}

  ngOnInit() {
    this.loading = true;
    this.wordPressService.GetMijnOverzicht().subscribe(
      (response) => {
        console.log("Wedstrijden", response)
        this.mapToWedstrijden(response)
        this.loading = false;
        if (this.wedstrijden.length == 0) {
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

    this.wordPressService.GetZaalwachtenForUser().subscribe((response) => {
      console.log("Zaalwachten: ", response)
    })

    this.stateService.isAuthenticated.subscribe((isAuthenticated) => {
      if (isAuthenticated) {
        this.ngOnInit();
      }

    });

    this.wordPressService.GetCoachWedstrijden().subscribe((response) => {
      console.log("GetCoachWedstrijden", response)
    })

    this.loadUserAndDiensten();
  }

  async loadUserAndDiensten() {
    try {
      this.user = await this.wordPressService.GetCurrentUser().toPromise();
      this.diensten = await this.wordPressService.GetDienstenForUser(this.user.id).toPromise()
      this.mapToTeamTasks(this.diensten)
      console.log('Diensten: ', this.diensten);
    } catch (err) {
      console.log('Error loading data', err)
    }
  }

  mapToTeamTasks(diensten) {
    const barShifts = diensten[0];
    const telShifts = diensten[1];
    const scheidsShifts = diensten[2];

    console.log(barShifts);
    console.log(scheidsShifts);
    console.log(telShifts);


    if (barShifts) {
      this.teamTasks = this.teamTasks.concat(barShifts.map(shift => ({
        id: shift.id,
        user_id: shift.persoon.id,
        type: shift.isBhv ? "BHV" : "Bardienst",
        timestamp: new Date(shift.bardag.date.date.replace(" ", "T")),
        shift: shift.shift,
        isBhv: shift.isBhv ? true : false
      })))
    }

    if (scheidsShifts) {
      this.teamTasks = this.teamTasks.concat(scheidsShifts.map(shift => ({
        id: shift.id,
        user_id: shift.scheidsrechterId,
        type: "Scheidsdienst",
        timestamp: new Date(shift.timestamp.replace(" ", "T")),
        // shift: shift.shift,
        // isBhv: shift.isBhv ? true : false
      })))
    }

    if (telShifts) {
      this.teamTasks = this.teamTasks.concat(telShifts.map(shift => ({
        id: shift.id,
        user_id: this.user.id,
        type: "Teldienst",
        timestamp: new Date(shift.timestamp.replace(" ", "T")),
        // shift: shift.shift,
        // isBhv: shift.isBhv ? true : false
      })))
    }

    this.teamTasks = this.teamTasks.sort((a, b) => a.timestamp.getTime() - b.timestamp.getTime())
    console.log(this.teamTasks);


  }

  mapToWedstrijden(wedstrijden) {
    this.wedstrijden = wedstrijden.map(wedstrijd => ({
      id: wedstrijd.id,
      locatie: wedstrijd.locatie,
      matchId: wedstrijd.matchId,
      timestamp: new Date(wedstrijd.timestamp.date.replace(" ", "T")),
      thuisTeam: wedstrijd.team1.naam,
      uitTeam: wedstrijd.team2.naam,
      thuisWedstrijd: wedstrijd.team1.naam.startsWith("SKC") ? true : false
    }))
    this.wedstrijden = this.wedstrijden.sort((a, b) => a.timestamp.getTime() - b.timestamp.getTime())

    console.log(this.wedstrijden)
  }

  openModal() {
    this.dialog.open(RuilLijstComponent, {
      width: '600px', // Adjust size
      panelClass: 'custom-modal',
      data: { userid: this.user.id },
    });
  }

  getIconClass(type: string) {
    switch (type) {
      case 'BHV': return this.bhvIcon; // Calendar icon
      case 'Bardienst': return this.taskIcon; // Task icon
      case 'Scheidsdienst': return this.refIcon; // Phone icon
      case 'Teldienst': return this.tellersIcon;
      default: return 'fas fa-info-circle'; // Default info icon
    }
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
