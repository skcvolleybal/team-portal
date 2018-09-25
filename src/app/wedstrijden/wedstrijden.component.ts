import { Component, OnInit } from '@angular/core';
import {
  faCheck,
  faQuestion,
  faTimes
} from '@fortawesome/free-solid-svg-icons';

@Component({
  templateUrl: './wedstrijden.component.html',
  styleUrls: ['./wedstrijden.component.scss']
})
export class WedstrijdenComponent implements OnInit {
  model1 = null;
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
      tellers: 'SKC HS 1',
      isTellers: false,
      locatie: 'Universitair Sport Centrum, Sportweg 6 2333 AS Leiden',
      isCollapsed: false,
      beschikbaarheid: 'ja'
    },
    {
      datum: '28 okt',
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
      beschikbaarheid: null
    },
    {
      datum: '5 nov',
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
      beschikbaarheid: 'nee'
    }
  ];

  constructor() {}

  ngOnInit() {}
}
