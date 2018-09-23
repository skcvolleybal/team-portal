import { Component, OnInit } from '@angular/core';
import {
  faAngleDoubleDown,
  faAngleDoubleUp,
  faPlus,
  faTimesCircle
} from '@fortawesome/free-solid-svg-icons';

@Component({
  selector: 'app-wedstrijd-overzicht',
  templateUrl: './wedstrijd-overzicht.component.html',
  styleUrls: ['./wedstrijd-overzicht.component.scss']
})
export class WedstrijdOverzichtComponent implements OnInit {
  spelerToevoegen = faPlus;
  uitklappen = faAngleDoubleDown;
  inklappen = faAngleDoubleUp;
  verwijderen = faTimesCircle;
  wedstrijden = [
    {
      datum: '21 oktober 2018',
      tijd: '19:30',
      aanwezigen: [
        { naam: 'Jonathan Neuteboom', speeltMee: false },
        { naam: 'Friso van Bokhorst', speeltMee: false },
        { naam: 'Jurrian Hofmeijer', speeltMee: false },
        { naam: 'Martijn Klinkenberg', speeltMee: true }
      ],
      afwezigen: ['Remco Krijgsman', 'Coen van der Sluis'],
      onbekend: [
        'Sjoerd Verbeek',
        'Pjotr Hurkmans',
        'Lars Wennekes',
        'Sietse Luk'
      ],
      backupTeams: [
        {
          naam: 'SKC HS 4',
          wedstrijden: [
            {
              tijd: '21:30',
              team1: 'SKC HS 3',
              team2: 'VCO HS 4',
              locatie: 'de Wasbeek, Leiderdorp, 2312 AS',
              isThuis: false
            }
          ],
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
          wedstrijden: [
            {
              tijd: '21:30',
              team1: 'SKC HS 4',
              team2: 'Delta HS 2',
              locatie: 'de Wasbeek, Leiderdorp, 2312 AS',
              isThuis: true
            }
          ]
        },
        {
          naam: 'SKC HS 6',
          isCollapsed: true,
          wedstrijden: [],
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
  constructor() {}

  ngOnInit() {}
}
