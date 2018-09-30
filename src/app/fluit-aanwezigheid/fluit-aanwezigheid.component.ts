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
              team1: 'SKC HS 1',
              isTeam1: false,
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
              team1: 'SKC HS 3',
              isTeam1: false,
              team2: 'VCO HS 2',
              isTeam: false
            }
          ],
          aanwezigheid: 'ja'
        },
        {
          tijd: '21:30',
          wedstrijden: [
            {
              team1: 'SKC HS 4',
              isTeam1: false,
              team2: 'Kalinko HS 2',
              isTeam: false
            },
            {
              team1: 'SKC HS 5',
              isTeam1: false,
              team2: 'Delta HS 2',
              isTeam: false
            },
            {
              team1: 'SKC HS 6',
              isTeam1: false,
              team2: 'VCO HS 2',
              isTeam: false
            }
          ],
          aanwezigheid: null
        }
      ],
      eigenWedstrijden: [
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
          tellers: 'SKC HS 1',
          isTellers: false,
          locatie: 'De Does, Leiderdorp',
          isThuis: false,
          isMogelijk: 'Ja'
        }
      ]
    },
    {
      datum: '28 oktober 2018',
      speeltijden: [
        {
          tijd: '19:30',
          wedstrijden: [
            {
              team1: 'SKC HS 1',
              isTeam1: false,
              team2: 'Kalinko HS 2',
              isTeam: false
            },
            {
              team1: 'SKC HS 2',
              isTeam1: false,
              team2: 'Delta HS 2',
              isTeam: false
            },
            {
              team1: 'SKC HS 3',
              isTeam1: false,
              team2: 'VCO HS 2',
              isTeam: false
            }
          ],
          aanwezigheid: 'ja'
        },
        {
          tijd: '21:30',
          wedstrijden: [
            {
              team1: 'SKC HS 4',
              isTeam1: false,
              team2: 'Kalinko HS 2',
              isTeam: false
            },
            {
              team1: 'SKC HS 5',
              isTeam1: false,
              team2: 'Delta HS 2',
              isTeam: false
            },
            {
              team1: 'SKC HS 6',
              isTeam1: false,
              team2: 'VCO HS 2',
              isTeam: false
            }
          ],
          aanwezigheid: null
        }
      ],
      eigenWedstrijden: [
        {
          datum: '20 okt',
          tijd: '19:30',
          team1: 'Kalinko HS 2',
          isTeam1: false,
          isCoachTeam1: false,
          team2: 'SKC HS 2',
          isTeam2: true,
          isCoachTeam2: false,
          scheidsrechter: 'Kevin Fung',
          isScheidsrechter: false,
          tellers: 'SKC HS 1',
          isTellers: false,
          locatie: 'Steenwijklaan, Steenwijklaan 16, 2541RL S-GRAVENHAGE',
          isThuis: false,
          isMogelijk: 'Nee'
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
          tellers: 'SKC HS 1',
          isTellers: false,
          locatie: 'De Does, Amaliaplein 40, 2351PV LEIDERDORP',
          isThuis: false,
          isMogelijk: 'Misschien'
        }
      ]
    }
  ];

  constructor() {}

  ngOnInit() {}
}
