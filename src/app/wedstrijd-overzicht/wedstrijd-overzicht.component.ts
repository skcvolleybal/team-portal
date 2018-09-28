import { Component, OnInit } from '@angular/core';
import {
  faAngleDoubleDown,
  faAngleDoubleUp,
  faMinusSquare,
  faPlus,
  faPlusSquare,
  faTimesCircle
} from '@fortawesome/free-solid-svg-icons';

@Component({
  selector: 'app-wedstrijd-overzicht',
  templateUrl: './wedstrijd-overzicht.component.html',
  styleUrls: ['./wedstrijd-overzicht.component.scss']
})
export class WedstrijdOverzichtComponent implements OnInit {
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
      isThuis: true,

      aanwezigen: [
        { naam: 'Jonathan Neuteboom', speeltMee: false },
        { naam: 'Friso van Bokhorst', speeltMee: false },
        { naam: 'Jurrian Hofmeijer', speeltMee: false },
        { naam: 'Martijn Klinkenberg', speeltMee: true }
      ],
      afwezigen: [
        { naam: 'Remco Krijgsman', speeltMee: false },
        { naam: 'Coen van der Sluis', speeltMee: false }
      ],
      onbekend: [
        { naam: 'Sjoerd Verbeek', speeltMee: false },
        { naam: 'Pjotr Hurkmans', speeltMee: false },
        { naam: 'Lars Wennekes', speeltMee: false },
        { naam: 'Sietse Luk', speeltMee: false }
      ],
      meespeelTeams: [
        {
          naam: 'SKC HS 4',
          wedstrijd: {
            tijd: '21:30',
            isTeam2: true,
            team1: 'SKC HS 3',
            team2: 'VCO HS 4',
            locatie: 'de Wasbeek, Leiderdorp',
            isThuis: false
          },
          isMogelijk: false,
          isCollapsed: true,
          spelers: [
            {
              naam: 'Jeroen van Kleinwee',
              meegespeeldDezeMaand: 0,
              meegespeeldDitJaar: 11
            },
            {
              naam: 'Maarten Stam',
              meegespeeldDezeMaand: 2,
              meegespeeldDitJaar: 2
            },
            {
              naam: 'Tim Kroon',
              meegespeeldDezeMaand: 0,
              meegespeeldDitJaar: 0
            },
            {
              naam: 'Tristan Wubs',
              meegespeeldDezeMaand: 0,
              meegespeeldDitJaar: 4
            },
            {
              naam: 'Huub Adriaanse',
              meegespeeldDezeMaand: 0,
              meegespeeldDitJaar: 0
            },
            {
              naam: 'Martijn Klinkenberg',
              meegespeeldDezeMaand: 0,
              meegespeeldDitJaar: 0
            },
            {
              naam: 'Pieter van Wolferen',
              meegespeeldDezeMaand: 0,
              meegespeeldDitJaar: 0
            },
            {
              naam: 'Govert Verberg',
              meegespeeldDezeMaand: 0,
              meegespeeldDitJaar: 0
            },
            {
              naam: 'Vincent Flierman',
              meegespeeldDezeMaand: 0,
              meegespeeldDitJaar: 0
            },
            {
              naam: 'Giacomo Modanesi',
              meegespeeldDezeMaand: 0,
              meegespeeldDitJaar: 0
            }
          ]
        },
        {
          naam: 'SKC HS 5',
          isCollapsed: true,
          spelers: [
            {
              naam: 'Jeroen van Kleinwee',
              meegespeeldDezeMaand: 0,
              meegespeeldDitJaar: 11
            },
            {
              naam: 'Maarten Stam',
              meegespeeldDezeMaand: 2,
              meegespeeldDitJaar: 2
            },
            {
              naam: 'Tim Kroon',
              meegespeeldDezeMaand: 0,
              meegespeeldDitJaar: 0
            },
            {
              naam: 'Tristan Wubs',
              meegespeeldDezeMaand: 0,
              meegespeeldDitJaar: 4
            },
            {
              naam: 'Huub Adriaanse',
              meegespeeldDezeMaand: 0,
              meegespeeldDitJaar: 0
            },
            {
              naam: 'Martijn Klinkenberg',
              meegespeeldDezeMaand: 0,
              meegespeeldDitJaar: 0
            },
            {
              naam: 'Pieter van Wolferen',
              meegespeeldDezeMaand: 0,
              meegespeeldDitJaar: 0
            },
            {
              naam: 'Govert Verberg',
              meegespeeldDezeMaand: 0,
              meegespeeldDitJaar: 0
            },
            {
              naam: 'Vincent Flierman',
              meegespeeldDezeMaand: 0,
              meegespeeldDitJaar: 0
            },
            {
              naam: 'Giacomo Modanesi',
              meegespeeldDezeMaand: 0,
              meegespeeldDitJaar: 0
            }
          ],
          wedstrijd: {
            tijd: '21:30',
            team1: 'SKC HS 4',
            isTeam1: true,
            team2: 'Delta HS 2',
            locatie: 'de Wasbeek, Leiderdorp',
            isThuis: true
          }
        },
        {
          naam: 'SKC HS 6',
          isMogelijk: true,
          isCollapsed: true,
          spelers: [
            {
              naam: 'Jeroen van Kleinwee',
              meegespeeldDezeMaand: 0,
              meegespeeldDitJaar: 11
            },
            {
              naam: 'Maarten Stam',
              meegespeeldDezeMaand: 2,
              meegespeeldDitJaar: 2
            },
            {
              naam: 'Tim Kroon',
              meegespeeldDezeMaand: 0,
              meegespeeldDitJaar: 0
            },
            {
              naam: 'Tristan Wubs',
              meegespeeldDezeMaand: 0,
              meegespeeldDitJaar: 4
            },
            {
              naam: 'Huub Adriaanse',
              meegespeeldDezeMaand: 0,
              meegespeeldDitJaar: 0
            },
            {
              naam: 'Martijn Klinkenberg',
              meegespeeldDezeMaand: 0,
              meegespeeldDitJaar: 0
            },
            {
              naam: 'Pieter van Wolferen',
              meegespeeldDezeMaand: 0,
              meegespeeldDitJaar: 0
            },
            {
              naam: 'Govert Verberg',
              meegespeeldDezeMaand: 0,
              meegespeeldDitJaar: 0
            },
            {
              naam: 'Vincent Flierman',
              meegespeeldDezeMaand: 0,
              meegespeeldDitJaar: 0
            },
            {
              naam: 'Giacomo Modanesi',
              meegespeeldDezeMaand: 0,
              meegespeeldDitJaar: 0
            }
          ]
        }
      ]
    }
  ];

  toggle(collapsable) {
    collapsable = !collapsable;
  }

  constructor() {}

  ngOnInit() {}
}
