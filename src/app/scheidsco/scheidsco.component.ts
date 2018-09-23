import { Component, OnInit } from '@angular/core';
import * as Enumerable from 'linq';
import { faUser, faUsers } from '@fortawesome/free-solid-svg-icons';
@Component({
  selector: 'app-scheidsco',
  templateUrl: './scheidsco.component.html',
  styleUrls: ['./scheidsco.component.scss']
})
export class ScheidscoComponent implements OnInit {
  scheidsrechterIcon = faUser;
  teamIcon = faUsers;
  scheidsrechterType = 'niveau';
  teams = [
    'SKC HS 1',
    'SKC HS 2',
    'SKC HS 3',
    'SKC HS 4',
    'SKC HS 5',
    'SKC HS 6',
    'SKC HS 7',
    'SKC HS 8'
  ];
  scheidsrechtersGroepen;
  scheidsrechterData = [
    { naam: 'Jonathan Neuteboom', niveau: 'V4', team: 'SKC HS 2' },
    { naam: 'Kevin Fung', niveau: 'V5', team: 'Geen Team' },
    { naam: 'Tanita de Graaf', niveau: 'V4', team: 'SKC DS 2' }
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
              tellers: 'Heren 1'
            },
            {
              teams: 'SKC HS 2 - Kalinko HS 2',
              scheidsrechter: 'Kevin Fung',
              tellers: 'Heren 1'
            },
            {
              teams: 'SKC HS 2 - Kalinko HS 2',
              scheidsrechter: 'Kevin Fung',
              tellers: 'Heren 1'
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
              tellers: 'Heren 1'
            },
            {
              teams: 'SKC HS 2 - Kalinko HS 2',
              scheidsrechter: 'Kevin Fung',
              tellers: 'Heren 1'
            },
            {
              teams: 'SKC HS 2 - Kalinko HS 2',
              scheidsrechter: 'Kevin Fung',
              tellers: 'Heren 1'
            }
          ]
        },
        {
          tijd: '21:30',
          wedstrijden: [
            {
              teams: 'SKC HS 2 - Kalinko HS 2',
              scheidsrechter: 'Kevin Fung',
              tellers: 'Heren 1'
            },
            {
              teams: 'SKC HS 2 - Kalinko HS 2',
              scheidsrechter: 'Kevin Fung',
              tellers: 'Heren 1'
            },
            {
              teams: 'SKC HS 2 - Kalinko HS 2',
              scheidsrechter: 'Kevin Fung',
              tellers: 'Heren 1'
            }
          ]
        }
      ],
      zaalwacht: 'SKC HS 2'
    }
  ];

  onChange() {
    this.setScheidsrechters();
  }

  setScheidsrechters() {
    let result = [];
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
      const scheidsrechterType = this.scheidsrechterType;
      const bin = Enumerable.from(result).firstOrDefault(
        bin => bin.name.toLowerCase() === scheidsrechterType
      );

      if (!bin) {
        const newBin = {
          name: binName.charAt(0).toUpperCase() + binName.substr(1),
          scheidsrechters: [scheidsrechter.naam]
        };
        result.push(newBin);
      } else {
        bin.scheidsrechters.push(scheidsrechter.naam);
      }
    });

    this.scheidsrechtersGroepen = result;
    console.log(this.scheidsrechtersGroepen);
  }

  constructor() {}

  ngOnInit() {
    this.setScheidsrechters();
  }
}
