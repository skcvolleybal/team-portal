import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-aanwezigheid',
  templateUrl: './aanwezigheid.component.html',
  styleUrls: ['./aanwezigheid.component.scss']
})
export class AanwezigheidComponent implements OnInit {
  speeldagen = [
    {
      datum: '29 september 2018',
      isZaalwacht: true,
      eigenWedstrijden: [
        {
          tijd: '19:30',
          team1: 'SKC HS 2',
          isTeam1: true,
          team2: 'Kalinko HS 2',
          isTeam: false,
          isCoachWedstrijd: false
        },
        {
          tijd: '19:30',
          team1: 'SKC HS 2',
          isTeam1: true,
          team2: 'Kalinko HS 2',
          isTeam: false,
          isCoachWedstrijd: false
        }
      ],
      wedstrijden: [
        {
          tijd: '19:30',
          team1: 'SKC HS 2',
          isTeam1: true,
          team2: 'Kalinko HS 2',
          isTeam: false,
          scheidsrechter: 'Kevin Fung',
          isScheidsrechter: false,
          tellers: 'Heren 1',
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
          tellers: 'Heren 2',
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
          tellers: 'Heren 1',
          isTellers: false
        }
      ]
    }
  ];
  constructor() { }

  ngOnInit() {
  }

}
