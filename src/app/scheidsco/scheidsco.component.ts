import { Component, OnInit } from '@angular/core';
import { faCalendarCheck, faUser } from '@fortawesome/free-solid-svg-icons';
import * as Enumerable from 'linq';
@Component({
  selector: 'app-scheidsco',
  templateUrl: './scheidsco.component.html',
  styleUrls: ['./scheidsco.component.scss']
})
export class ScheidscoComponent implements OnInit {
  scheidsrechterIcon = faUser;
  teamIcon = faCalendarCheck;
  scheidsrechterType = 'niveau';
  teams = [
    { naam: 'SKC HS 1', geteld: 1 },
    { naam: 'SKC HS 2', geteld: 2 },
    { naam: 'SKC HS 3', geteld: 1 },
    { naam: 'SKC HS 4', geteld: 3 },
    { naam: 'SKC HS 5', geteld: 1 },
    { naam: 'SKC HS 6', geteld: 5 },
    { naam: 'SKC HS 7', geteld: 1 },
    { naam: 'SKC HS 8', geteld: 2 }
  ];
  scheidsrechtersGroepen;
  scheidsrechterData = [
    { naam: 'Jonathan Neuteboom', niveau: 'V5', team: 'SKC HS 2', gefloten: 4 },
    { naam: 'Kevin Fung', niveau: 'V4', team: 'Geen Team', gefloten: 2 },
    { naam: 'Tanita de Graaf', niveau: 'V5', team: 'SKC DS 2', gefloten: 0 }
  ];
  speeldagen = [
    {
      datum: '21 oktober 2018',
      speeltijden: [
        {
          tijd: '19:30',
          wedstrijden: [
            {
              teams: 'SKC HS 2 - Kalinko HS 2',
              scheidsrechter: null,
              tellers: 'SKC HS 1'
            },
            {
              teams: 'SKC HS 2 - Kalinko HS 2',
              scheidsrechter: 'Kevin Fung',
              tellers: 'SKC HS 2'
            },
            {
              teams: 'SKC HS 2 - Kalinko HS 2',
              scheidsrechter: 'Kevin Fung',
              tellers: 'SKC HS 3'
            }
          ]
        },
        {
          tijd: '21:30',
          wedstrijden: [
            {
              teams: 'SKC HS 2 - Kalinko HS 2',
              scheidsrechter: 'Kevin Fung',
              tellers: 'SKC HS 3'
            },
            {
              teams: 'SKC HS 2 - Kalinko HS 2',
              scheidsrechter: 'Kevin Fung',
              tellers: 'SKC HS 3'
            },
            {
              teams: 'SKC HS 2 - Kalinko HS 2',
              scheidsrechter: 'Kevin Fung',
              tellers: 'SKC HS 3'
            }
          ]
        }
      ],
      zaalwacht: 'SKC HS 2'
    },
    {
      datum: '21 oktober 2018',
      speeltijden: [
        {
          tijd: '19:30',
          wedstrijden: [
            {
              teams: 'SKC HS 2 - Kalinko HS 2',
              scheidsrechter: 'Kevin Fung',
              tellers: 'SKC HS 3'
            },
            {
              teams: 'SKC HS 2 - Kalinko HS 2',
              scheidsrechter: 'Kevin Fung',
              tellers: 'SKC HS 3'
            },
            {
              teams: 'SKC HS 2 - Kalinko HS 2',
              scheidsrechter: 'Kevin Fung',
              tellers: 'SKC HS 3'
            }
          ]
        },
        {
          tijd: '21:30',
          wedstrijden: [
            {
              teams: 'SKC HS 2 - Kalinko HS 2',
              scheidsrechter: 'Kevin Fung',
              tellers: 'SKC HS 3'
            },
            {
              teams: 'SKC HS 2 - Kalinko HS 2',
              scheidsrechter: 'Kevin Fung',
              tellers: 'SKC HS 3'
            },
            {
              teams: 'SKC HS 2 - Kalinko HS 2',
              scheidsrechter: 'Kevin Fung',
              tellers: 'SKC HS 3'
            }
          ]
        }
      ],
      zaalwacht: 'SKC HS 3'
    }
  ];

  onChange() {
    this.setScheidsrechters();
  }

  setScheidsrechters() {
    const result = [];
    this.scheidsrechterData.forEach(scheidsrechter => {
      let binName;
      switch (this.scheidsrechterType) {
        case 'niveau':
          binName = scheidsrechter.niveau;
          break;
        case 'team':
          binName = scheidsrechter.team;
          break;
        default:
          binName = 'naam';
          break;
      }

      const bin = Enumerable.from(result).firstOrDefault(
        binItem => binItem.name.toUpperCase() === binName.toUpperCase()
      );

      if (!bin) {
        const newBin = {
          name: binName.charAt(0).toUpperCase() + binName.substr(1),
          scheidsrechters: [scheidsrechter]
        };
        result.push(newBin);
      } else {
        bin.scheidsrechters.push(scheidsrechter);
      }
    });

    Enumerable.from(result).forEach(bin => {
      bin.scheidsrechters = Enumerable.from(bin.scheidsrechters)
        .orderByDescending(scheidsrechter => {
          return scheidsrechter['gefloten'];
        })
        .toArray();
    });

    this.scheidsrechtersGroepen = Enumerable.from(result)
      .orderBy(bin => bin['name'])
      .toArray();
  }

  constructor() {}

  ngOnInit() {
    this.setScheidsrechters();
  }
}
