import { HttpClient } from '@angular/common/http';
import { Component, OnInit } from '@angular/core';
import { faCalendarCheck, faUser } from '@fortawesome/free-solid-svg-icons';
import * as Enumerable from 'linq';
import { Observable } from 'rxjs/internal/Observable';
import { environment } from '../../environments/environment';

@Component({
  selector: 'app-scheidsco',
  templateUrl: './scheidsco.component.html',
  styleUrls: ['./scheidsco.component.scss']
})
export class ScheidscoComponent implements OnInit {
  scheidsrechterIcon = faUser;
  teamIcon = faCalendarCheck;
  scheidsrechterType = 'niveau';

  scheidsrechters: any[];
  scheidsrechtersGroepen: any[];

  speeldagen: any[];
  teams: any[];

  scheidsrechterLoading: boolean;
  overzichtLoading: boolean;
  teamsLoading: boolean;

  errorMessage: any;

  constructor(private httpClient: HttpClient) {}

  onChange() {
    this.setScheidsrechters();
  }

  setScheidsrechters() {
    const result = [];
    this.scheidsrechters.forEach(scheidsrechter => {
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
        .orderBy(scheidsrechter => {
          return scheidsrechter['gefloten'];
        })
        .toArray();
    });

    this.scheidsrechtersGroepen = Enumerable.from(result)
      .orderBy(bin => bin['name'])
      .toArray();
  }

  getScheidscoOverzicht() {
    this.overzichtLoading = true;

    this.httpClient
      .get<any[]>(
        environment.baseUrl + 'php/interface.php?action=GetScheidscoOverzicht'
      )
      .subscribe(
        speeldagen => {
          this.speeldagen = speeldagen;
          this.overzichtLoading = false;
        },
        error => {
          if (error.status === 500) {
            this.errorMessage = error.error;
            this.overzichtLoading = false;
          }
        }
      );
  }

  getScheidsrechters() {
    this.scheidsrechterLoading = true;

    this.httpClient
      .get<any[]>(
        environment.baseUrl + 'php/interface.php?action=GetScheidsrechters'
      )
      .subscribe(
        scheidsrechters => {
          this.scheidsrechters = scheidsrechters;
          this.setScheidsrechters();
          this.scheidsrechterLoading = false;
        },
        error => {
          if (error.status === 500) {
            this.errorMessage = error.error;
            this.scheidsrechterLoading = false;
          }
        }
      );
  }

  getZaalwachtTeams() {
    this.teamsLoading = true;

    this.httpClient
      .get<any[]>(
        environment.baseUrl + 'php/interface.php?action=GetZaalwachtTeams'
      )
      .subscribe(
        teams => {
          this.teams = teams;
          this.teamsLoading = false;
        },
        error => {
          if (error.status === 500) {
            this.errorMessage = error.error;
            this.teamsLoading = false;
          }
        }
      );
  }

  UpdateWedstrijd(matchId, scheidsrechter, telteam) {
    const speeldagen = this.speeldagen;
    this.httpClient
      .post<any>(
        environment.baseUrl +
          'php/interface.php?action=UpdateScheidscoWedstrijd',
        {
          matchId,
          scheidsrechter,
          telteam
        }
      )
      .subscribe(() => {
        speeldagen.forEach(speeldag => {
          speeldag.speeltijden.forEach(speeltijd => {
            speeltijd.wedstrijden.forEach(wedstrijd => {
              if (wedstrijd.id === matchId) {
                wedstrijd.scheidsrechter = scheidsrechter;
                wedstrijd.telteam = telteam;
              }
            });
          });
        });
        this.getScheidsrechters();
      });
  }

  UpdateZaalwacht(date, team) {
    const speeldagen = this.speeldagen;
    this.httpClient
      .post<any>(
        environment.baseUrl +
          'php/interface.php?action=UpdateScheidscoZaalwacht',
        {
          date,
          team
        }
      )
      .subscribe(() => {
        speeldagen.forEach(speeldag => {
          if (speeldag.date === date) {
            speeldag.zaalwacht = team;
          }
        });
        this.getZaalwachtTeams();
      });
  }

  ngOnInit() {
    this.getScheidsrechters();
    this.getZaalwachtTeams();
    this.getScheidscoOverzicht();
  }
}
