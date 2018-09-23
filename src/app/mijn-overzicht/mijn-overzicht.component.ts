import { Component, OnInit } from '@angular/core';
import {
  faCalendarCheck,
  faMinusSquare,
  faPlusSquare,
  faUser
} from '@fortawesome/free-solid-svg-icons';

@Component({
  selector: 'app-mijn-overzicht',
  templateUrl: './mijn-overzicht.component.html',
  styleUrls: ['./mijn-overzicht.component.scss']
})
export class MijnOverzichtComponent implements OnInit {
  scheidsrechterIcon = faUser;
  tellersIcon = faCalendarCheck;
  collapseIcon = faMinusSquare;
  openIcon = faPlusSquare;
  dagen = [
    {
      datum: '12 oktober 2018',
      items: [
        {
          type: 'zaalwacht',
          zaalwachtTeam: 'SKC HS 2'
        },
        {
          type: 'wedstrijd',
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
          isCollapsed: false
        },
        {
          type: 'wedstrijd',
          tijd: '19:30',
          team1: 'SKC HS 3',
          isTeam1: false,
          isCoachTeam1: false,
          team2: 'SKC DS 10',
          isTeam: false,
          isCoachTeam2: true,
          scheidsrechter: 'Kevin Fung',
          isScheidsrechter: false,
          tellers: 'SKC HS 1',
          isTellers: false,
          locatie: 'Universitair Sport Centrum, Sportweg 6 2333 AS Leiden',
          isCollapsed: false
        },
        {
          type: 'wedstrijd',
          datum: '19 oktober 2018',
          tijd: '19:30',
          team1: 'SKC HS 3',
          isTeam1: false,
          isCoachTeam1: false,
          team2: 'Kalinko HS 2',
          isTeam: false,
          isCoachTeam2: false,
          scheidsrechter: 'Kevin Fung',
          isScheidsrechter: false,
          tellers: 'SKC HS 2',
          isTellers: true,
          locatie: 'Universitair Sport Centrum, Sportweg 6 2333 AS Leiden',
          isCollapsed: false
        },
        {
          type: 'wedstrijd',
          datum: '19 oktober 2018',
          tijd: '19:30',
          team1: 'SKC HS 3',
          isTeam1: false,
          isCoachTeam1: false,
          team2: 'Kalinko HS 2',
          isTeam: false,
          isCoachTeam2: false,
          scheidsrechter: 'Jonathan Neuteboom',
          isScheidsrechter: true,
          tellers: 'SKC HS 1',
          isTellers: false,
          locatie: 'Universitair Sport Centrum, Sportweg 6 2333 AS Leiden',
          isCollapsed: false
        }
      ]
    }
  ];

  model = {
    left: true,
    middle: false,
    right: false
  };
  dingen = [
    {
      type: 'wedstrijd',
      datum: '12 oktober 2018',
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
      locatie: 'Universitair Sport Centrum, Sportweg 6 2333 AS Leiden'
    },
    {
      type: 'wedstrijd',
      datum: '19 oktober 2018',
      tijd: '19:30',
      team1: 'SKC HS 2',
      isTeam1: false,
      isCoachTeam1: false,
      team2: 'Kalinko HS 2',
      isTeam: false,
      isCoachTeam2: true,
      scheidsrechter: 'Kevin Fung',
      isScheidsrechter: false,
      tellers: 'SKC HS 1',
      isTellers: false,
      locatie: 'Universitair Sport Centrum, Sportweg 6 2333 AS Leiden'
    },
    {
      type: 'wedstrijd',
      datum: '27 oktober 2018',
      tijd: '19:30',
      team1: 'SKC HS 2',
      isTeam1: false,
      isCoachTeam1: false,
      team2: 'Kalinko HS 2',
      isTeam: false,
      isCoachTeam2: false,
      scheidsrechter: 'Kevin Fung',
      isScheidsrechter: false,
      tellers: 'SKC HS 1',
      isTellers: true,
      locatie: 'Universitair Sport Centrum, Sportweg 6 2333 AS Leiden'
    }
  ];

  speeldagen = [
    {
      datum: '29 september 2018',
      zaalwacht: 'SKC HS 1',
      isZaalwacht: false,
      wedstrijden: [
        {
          tijd: '19:30',
          team1: 'SKC HS 2',
          isTeam1: true,
          team2: 'Kalinko HS 2',
          isTeam: false,
          scheidsrechter: 'Kevin Fung',
          isScheidsrechter: false,
          isCoachTeam1: false,
          isCoachTeam2: false,
          tellers: 'SKC HS 1',
          isTellers: false
        },
        {
          tijd: '19:30',
          team1: 'SKC HS 3',
          isTeam1: false,
          team2: 'VCO HS 2',
          isTeam: false,
          scheidsrechter: 'Jurrian Meijerhof',
          isScheidsrechter: false,
          isCoachTeam1: true,
          isCoachTeam2: false,
          tellers: 'SKC HS 2',
          isTellers: true
        },
        {
          tijd: '19:30',
          team1: 'SKC HS 4',
          isTeam1: false,
          team2: 'Vollingo HS 2',
          isTeam: false,
          scheidsrechter: 'Jonathan Neuteboom',
          isScheidsrechter: true,
          isCoachTeam1: false,
          isCoachTeam2: true,
          tellers: 'SKC HS 1',
          isTellers: false
        }
      ]
    },
    {
      datum: '7 oktober 2018',
      zaalwacht: 'SKC HS 2',
      isZaalwacht: true,
      wedstrijden: [
        {
          tijd: '19:30',
          team1: 'SKC HS 2',
          isTeam1: true,
          team2: 'Kalinko HS 2',
          isTeam: false,
          scheidsrechter: 'Kevin Fung',
          isScheidsrechter: false,
          tellers: 'SKC HS 1',
          isTellers: false
        },
        {
          tijd: '19:30',
          team1: 'SKC HS 3',
          isTeam1: true,
          team2: 'VCO HS 2',
          isTeam: false,
          scheidsrechter: 'Jurrian Meijerhof',
          isScheidsrechter: false,
          tellers: 'SKC HS 2',
          isTellers: true
        },
        {
          tijd: '19:30',
          team1: 'SKC HS 4',
          isTeam1: true,
          team2: 'Vollingo HS 2',
          isTeam: false,
          scheidsrechter: 'Jonathan Neuteboom',
          isScheidsrechter: true,
          tellers: 'SKC HS 1',
          isTellers: false
        }
      ]
    }
  ];

  constructor() {}

  ngOnInit() {}
}
