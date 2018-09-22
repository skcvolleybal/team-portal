import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-scheidsco',
  templateUrl: './scheidsco.component.html',
  styleUrls: ['./scheidsco.component.scss']
})
export class ScheidscoComponent implements OnInit {
  tellers = [
    { id: 1, name: 'SKC HS 1' },
    { id: 2, name: 'SKC HS 2' },
    { id: 3, name: 'SKC HS 3' },
    { id: 4, name: 'SKC HS 4' },
    { id: 5, name: 'SKC HS 5' },
    { id: 6, name: 'SKC HS 6' },
    { id: 7, name: 'SKC HS 7' },
    { id: 8, name: 'SKC HS 8' }
  ];
  scheidsrechters = [
    {
      id: 1,
      name: 'Jonathan Neuteboom'
    },
    {
      id: 2,
      name: 'Kevin Fung'
    },
    {
      id: 3,
      name: 'Tanita de Graaf'
    }
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

  constructor() {}

  ngOnInit() {}
}
