import { HttpClient } from '@angular/common/http';
import { Component, OnInit } from '@angular/core';
import {
  faCalendarCheck,
  faCheck,
  faQuestion,
  faTimes,
  faUser
} from '@fortawesome/free-solid-svg-icons';
// tslint:disable-next-line:no-implicit-dependencies
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-coach-wedstrijden',
  templateUrl: './coach-wedstrijden.component.html',
  styleUrls: ['./coach-wedstrijden.component.scss']
})
export class CoachWedstrijdenComponent implements OnInit {
  neeIcon = faTimes;
  misschienIcon = faQuestion;
  jaIcon = faCheck;
  scheidsrechterIcon = faUser;
  teamIcon = faCalendarCheck;

  //   wedstrijden = [
  //     {
  //       datum: '21 oktober 2018',
  //       tijd: '19:30',
  //       team1: 'SKC HS 2',
  //       isTeam1: true,
  //       isCoachTeam1: false,
  //       team2: 'Kalinko HS 2',
  //       isTeam: false,
  //       isCoachTeam2: false,
  //       scheidsrechter: 'Kevin Fung',
  //       isScheidsrechter: false,
  //       telteam: 'SKC HS 1',
  //       isTelteam: false,
  //       locatie: 'Universitair Sport Centrum, Sportweg 6 2333 AS Leiden',
  //       aanwezigheid: 'ja',
  //       eigenWedstrijden: [
  //         {
  //           datum: '20 okt',
  //           tijd: '19:30',
  //           team1: 'SKC HS 2',
  //           isTeam1: true,
  //           isCoachTeam1: false,
  //           team2: 'Kalinko HS 2',
  //           isTeam: false,
  //           isCoachTeam2: false,
  //           scheidsrechter: 'Kevin Fung',
  //           isScheidsrechter: false,
  //           telteam: 'SKC HS 1',
  //           isTelteam: false,
  //           locatie: 'Universitair Sport Centrum, Sportweg 6 2333 AS Leiden',
  //           isThuis: true,
  //           isMogelijk: 'Ja'
  //         },
  //         {
  //           datum: '21 okt',
  //           tijd: '19:30',
  //           team1: 'SKC HS 4',
  //           isTeam1: false,
  //           isCoachTeam1: false,
  //           team2: 'Kalinko HS 3',
  //           isTeam: false,
  //           isCoachTeam2: true,
  //           scheidsrechter: 'Jonathan',
  //           isScheidsrechter: false,
  //           telteam: 'SKC HS 1',
  //           isTelteam: false,
  //           locatie: 'Universitair Sport Centrum, Sportweg 6 2333 AS Leiden',
  //           isThuis: true,
  //           isMogelijk: 'Nee'
  //         },
  //         {
  //           datum: '21 okt',
  //           tijd: '19:30',
  //           team1: 'SKC HS 4',
  //           isTeam1: false,
  //           isCoachTeam1: false,
  //           team2: 'Kalinko HS 2',
  //           isTeam: false,
  //           isCoachTeam2: false,
  //           scheidsrechter: 'Jonathan Neuteboom',
  //           isScheidsrechter: true,
  //           telteam: 'SKC HS 2',
  //           isTelteam: true,
  //           locatie: 'Universitair Sport Centrum, Sportweg 6 2333 AS Leiden',
  //           isMogelijk: 'Misschien'
  //         }
  //       ]
  //     },
  //     {
  //       datum: '28 oktober 2018',
  //       tijd: '19:30',
  //       team1: 'SKC HS 3',
  //       isTeam1: true,
  //       isCoachTeam1: false,
  //       team2: 'Kalinko HS 2',
  //       isTeam: false,
  //       isCoachTeam2: false,
  //       scheidsrechter: 'Kevin Fung',
  //       isScheidsrechter: false,
  //       telteam: 'SKC HS 1',
  //       isTelteam: false,
  //       locatie: 'Universitair Sport Centrum, Sportweg 6 2333 AS Leiden',
  //       isCollapsed: false,
  //       aanwezigheid: 'ja',
  //       eigenWedstrijden: []
  //     },
  //     {
  //       datum: '10 november 2018',
  //       tijd: '19:30',
  //       team1: 'SKC HS 2',
  //       isTeam1: true,
  //       isCoachTeam1: false,
  //       team2: 'Kalinko HS 2',
  //       isTeam: false,
  //       isCoachTeam2: false,
  //       scheidsrechter: 'Kevin Fung',
  //       isScheidsrechter: false,
  //       telteam: 'SKC HS 1',
  //       isTelteam: false,
  //       locatie: 'Universitair Sport Centrum, Sportweg 6 2333 AS Leiden',
  //       isCollapsed: false,
  //       eigenWedstrijden: []
  //     }
  //   ];
  loading: boolean;
  errorMessage: any;
  wedstrijden: any[];

  constructor(private httpClient: HttpClient) {}

  ngOnInit() {
    this.getCoachAanwezigheid();
  }

  UpdateCoachAanwezigheid(beschikbaarheid, datum, tijd) {
    this.httpClient
      .post(
        environment.baseUrl,
        {
          datum,
          tijd,
          beschikbaarheid
        },
        {
          params: { action: 'UpdateCoachAanwezigheid' }
        }
      )
      .subscribe();
  }

  getCoachAanwezigheid() {
    this.loading = true;
    this.httpClient
      .get<any>(environment.baseUrl, {
        params: {
          action: 'GetCoachAanwezigheid'
        }
      })
      .subscribe(
        response => {
          this.wedstrijden = response.wedstrijden;
          this.loading = false;
        },
        error => {
          if (error.status === 500) {
            this.errorMessage = error.error;
            this.loading = false;
          }
        }
      );
  }
}
