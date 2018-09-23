import { Component, OnInit } from '@angular/core';
import {
  faCalendarCheck,
  faCheck,
  faQuestion,
  faTimes,
  faUser
} from '@fortawesome/free-solid-svg-icons';

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

  wedstrijden = [
    {
      datum: '21 oktober 2018',
      tijd: '19:30',
      team1: 'SKC HS 2',
      isTeam1: true,
      isCoachTeam1: false,
      team2: 'Kalinko HS 2',
      isTeam: false,
      isCoachTeam2: false,
      scheidsrechter: 'Kevin Fung',
      isScheidsrechter: false,
      tellers: 'SKC HS 1',
      isTellers: false,
      locatie: 'Universitair Sport Centrum, Sportweg 6 2333 AS Leiden',
      beschikbaarheid: 'ja',
      eigenWedstrijden: [
        {
          datum: '20 okt',
          tijd: '19:30',
          team1: 'SKC HS 2',
          isTeam1: true,
          isCoachTeam1: false,
          team2: 'Kalinko HS 2',
          isTeam: false,
          isCoachTeam2: false,
          scheidsrechter: 'Kevin Fung',
          isScheidsrechter: false,
          tellers: 'SKC HS 1',
          isTellers: false,
          locatie: 'Universitair Sport Centrum, Sportweg 6 2333 AS Leiden',
          isThuis: true,
          isMogelijk: true
        },
        {
          datum: '21 okt',
          tijd: '19:30',
          team1: 'SKC HS 4',
          isTeam1: false,
          isCoachTeam1: false,
          team2: 'Kalinko HS 3',
          isTeam: false,
          isCoachTeam2: true,
          scheidsrechter: 'Jonathan',
          isScheidsrechter: false,
          tellers: 'SKC HS 1',
          isTellers: false,
          locatie: 'Universitair Sport Centrum, Sportweg 6 2333 AS Leiden',
          isThuis: true,
          isMogelijk: false
        },
        {
          datum: '21 okt',
          tijd: '19:30',
          team1: 'SKC HS 4',
          isTeam1: false,
          isCoachTeam1: false,
          team2: 'Kalinko HS 2',
          isTeam: false,
          isCoachTeam2: false,
          scheidsrechter: 'Jonathan Neuteboom',
          isScheidsrechter: true,
          tellers: 'SKC HS 2',
          isTellers: true,
          locatie: 'Universitair Sport Centrum, Sportweg 6 2333 AS Leiden'
        }
      ]
    },
    {
      datum: '28 oktober 2018',
      tijd: '19:30',
      team1: 'SKC HS 3',
      isTeam1: true,
      isCoachTeam1: false,
      team2: 'Kalinko HS 2',
      isTeam: false,
      isCoachTeam2: false,
      scheidsrechter: 'Kevin Fung',
      isScheidsrechter: false,
      tellers: 'SKC HS 1',
      isTellers: false,
      locatie: 'Universitair Sport Centrum, Sportweg 6 2333 AS Leiden',
      isCollapsed: false,
      beschikbaarheid: 'ja',
      eigenWedstrijden: []
    },
    {
      datum: '10 november 2018',
      tijd: '19:30',
      team1: 'SKC HS 2',
      isTeam1: true,
      isCoachTeam1: false,
      team2: 'Kalinko HS 2',
      isTeam: false,
      isCoachTeam2: false,
      scheidsrechter: 'Kevin Fung',
      isScheidsrechter: false,
      tellers: 'SKC HS 1',
      isTellers: false,
      locatie: 'Universitair Sport Centrum, Sportweg 6 2333 AS Leiden',
      isCollapsed: false,
      eigenWedstrijden: []
    }
  ];
  constructor() {}

  ngOnInit() {}
}
