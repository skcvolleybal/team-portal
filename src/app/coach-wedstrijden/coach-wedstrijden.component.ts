import { Component, OnInit } from '@angular/core';
import {
  faTimes,
  faQuestion,
  faCheck
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
  wedstrijden = [
    {
      datum: '21 okt',
      tijd: '19:30',
      team1: 'SKC HS 2',
      isTeam1: true,
      isCoachTeam1: false,
      team2: 'Kalinko HS 2',
      isTeam: false,
      isCoachTeam2: false,
      scheidsrechter: 'Kevin Fung',
      isScheidsrechter: false,
      tellers: 'Heren 1',
      isTellers: false,
      locatie: 'Universitair Sport Centrum, Sportweg 6 2333 AS Leiden',
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
          tellers: 'Heren 1',
          isTellers: false,
          locatie: 'Universitair Sport Centrum, Sportweg 6 2333 AS Leiden'
        },
        {
          datum: '21 okt',
          tijd: '19:30',
          team1: 'SKC HS 2',
          isTeam1: false,
          isCoachTeam1: false,
          team2: 'Kalinko HS 2',
          isTeam: false,
          isCoachTeam2: false,
          scheidsrechter: 'Jonathan',
          isScheidsrechter: true,
          tellers: 'Heren 1',
          isTellers: false,
          locatie: 'Universitair Sport Centrum, Sportweg 6 2333 AS Leiden'
        },
        {
          datum: '21 okt',
          tijd: '19:30',
          team1: 'SKC HS 2',
          isTeam1: true,
          isCoachTeam1: false,
          team2: 'Kalinko HS 2',
          isTeam: false,
          isCoachTeam2: false,
          scheidsrechter: 'Kevin Fung',
          isScheidsrechter: false,
          tellers: 'Heren 1',
          isTellers: false,
          locatie: 'Universitair Sport Centrum, Sportweg 6 2333 AS Leiden'
        }
      ]
    },
    {
      datum: '21 okt',
      tijd: '19:30',
      team1: 'SKC HS 3',
      isTeam1: true,
      isCoachTeam1: false,
      team2: 'Kalinko HS 2',
      isTeam: false,
      isCoachTeam2: false,
      scheidsrechter: 'Kevin Fung',
      isScheidsrechter: false,
      tellers: 'Heren 1',
      isTellers: false,
      locatie: 'Universitair Sport Centrum, Sportweg 6 2333 AS Leiden',
      isCollapsed: false,
      eigenWedstrijden: []
    },
    {
      datum: '21 okt',
      tijd: '19:30',
      team1: 'SKC HS 2',
      isTeam1: true,
      isCoachTeam1: false,
      team2: 'Kalinko HS 2',
      isTeam: false,
      isCoachTeam2: false,
      scheidsrechter: 'Kevin Fung',
      isScheidsrechter: false,
      tellers: 'Heren 1',
      isTellers: false,
      locatie: 'Universitair Sport Centrum, Sportweg 6 2333 AS Leiden',
      isCollapsed: false,
      eigenWedstrijden: []
    }
  ];
  constructor() {}

  ngOnInit() {}
}
