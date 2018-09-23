import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-fluit-aanwezigheid',
  templateUrl: './fluit-aanwezigheid.component.html',
  styleUrls: ['./fluit-aanwezigheid.component.scss']
})
export class FluitAanwezigheidComponent implements OnInit {
  speeldagen = [
    {
      datum: '21 oktober 2018',
      speeltijden: [
        {
          tijd: '19:30',
          wedstrijden: [
            {
              team1: 'SKC HS 2',
              isTeam1: true,
              team2: 'Kalinko HS 2',
              isTeam: false
            },
            {
              team1: 'SKC HS 2',
              isTeam1: true,
              team2: 'Delta HS 2',
              isTeam: false
            },
            {
              team1: 'SKC HS 2',
              isTeam1: true,
              team2: 'VCO HS 2',
              isTeam: false
            }
          ],
          beschikbaarheid: 'ja'
        },
        {
          tijd: '21:30',
          wedstrijden: [
            {
              team1: 'SKC HS 2',
              isTeam1: true,
              team2: 'Kalinko HS 2',
              isTeam: false
            },
            {
              team1: 'SKC HS 2',
              isTeam1: true,
              team2: 'Delta HS 2',
              isTeam: false
            },
            {
              team1: 'SKC HS 2',
              isTeam1: true,
              team2: 'VCO HS 2',
              isTeam: false
            }
          ],
          beschikbaarheid: null
        }
      ],
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
          locatie: 'Universitair Sport Centrum, Sportweg 6 2333 AS Leiden',
          isThuis: true
        },
        {
          datum: '21 okt',
          tijd: '19:30',
          team1: 'SKC HS 2',
          isTeam1: false,
          isCoachTeam1: false,
          team2: 'Kalinko HS 3',
          isTeam: false,
          isCoachTeam2: true,
          scheidsrechter: 'Jonathan',
          isScheidsrechter: true,
          tellers: 'Heren 1',
          isTellers: false,
          locatie: 'De Does, Leiderdorp',
          isThuis: false
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
          scheidsrechter: 'Kevin Fung',
          isScheidsrechter: false,
          tellers: 'SKC HS 2',
          isTellers: true,
          locatie: 'Universitair Sport Centrum, Sportweg 6 2333 AS Leiden',
          isThuis: true
        }
      ]
    }
  ];

  constructor() {}

  ngOnInit() {}
}
