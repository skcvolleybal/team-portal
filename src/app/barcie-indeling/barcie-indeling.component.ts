import { HttpClient } from '@angular/common/http';
import { Component, OnInit } from '@angular/core';
import { FormControl, FormGroup, Validators } from '@angular/forms';
import { faHeart as heartRegular } from '@fortawesome/free-regular-svg-icons';
import {
  faCalendarAlt,
  faHeart as heartSolid,
  faTrashAlt
} from '@fortawesome/free-solid-svg-icons';
import { NgbDate, NgbModal } from '@ng-bootstrap/ng-bootstrap';
// tslint:disable-next-line:no-implicit-dependencies
import { environment } from 'src/environments/environment';
import { SelecteerBarcieLidComponent } from '../selecteer-barcie-lid/selecteer-barcie-lid.component';

@Component({
  selector: 'app-barcie-indeling',
  templateUrl: './barcie-indeling.component.html',
  styleUrls: ['./barcie-indeling.component.scss']
})
export class BarcieIndelingComponent implements OnInit {
  calendar = faCalendarAlt;
  geenBhv = heartRegular;
  bhv = heartSolid;
  delete = faTrashAlt;
  newDate: FormGroup;

  barcieDagen = [];
  roosterLoading: boolean;
  errorMessage: string;

  constructor(private httpClient: HttpClient, private modalService: NgbModal) {}

  ngOnInit() {
    this.newDate = new FormGroup({
      date: new FormControl(null, [Validators.required])
    });
    this.GetBarcieRooster();
  }

  onDateSelection(date: NgbDate) {
    this.errorMessage = null;
    this.httpClient
      .post<any>(
        environment.baseUrl,
        {
          date: `${date.year}-${date.month}-${date.day}`
        },
        {
          params: {
            action: 'AddBarcieDag'
          }
        }
      )
      .subscribe(
        () => {
          this.GetBarcieRooster();
        },
        response => {
          this.errorMessage = response.error;
        }
      );
  }

  DeleteBarcieDate(date) {
    this.httpClient
      .post<any>(
        environment.baseUrl,
        {
          date
        },
        {
          params: {
            action: 'DeleteBarcieDag'
          }
        }
      )
      .subscribe(() => {
        this.GetBarcieRooster();
      });
  }

  ToggleBhv(selectedBarcieDag, selectedBarcieLid, shift) {
    this.httpClient
      .post<any>(
        environment.baseUrl,
        {
          date: selectedBarcieDag.date,
          barcieLidId: selectedBarcieLid.id,
          shift
        },
        {
          params: {
            action: 'ToggleBhv'
          }
        }
      )
      .subscribe(() => {
        this.barcieDagen.forEach(barcieDag => {
          if (barcieDag.date === selectedBarcieDag.date) {
            barcieDag.shifts[shift - 1].barcieLeden.forEach(barcieLid => {
              if (selectedBarcieLid.id === barcieLid.id) {
                barcieLid.isBhv = !barcieLid.isBhv;
                return;
              }
            });
          }
        });
      });
  }

  DeleteAanwezigheid(selectedBarcieDag, selectedBarcieLid, shift) {
    this.httpClient
      .post<any>(
        environment.baseUrl,
        {
          date: selectedBarcieDag.date,
          barcieLidId: selectedBarcieLid.id,
          shift
        },
        {
          params: {
            action: 'DeleteBarcieAanwezigheid'
          }
        }
      )
      .subscribe(() => {
        this.barcieDagen.forEach(barcieDag => {
          if (barcieDag.date === selectedBarcieDag.date) {
            for (
              let i = 0;
              i < barcieDag.shifts[shift - 1].barcieLeden.length;
              i++
            ) {
              if (
                selectedBarcieLid.id ===
                barcieDag.shifts[shift - 1].barcieLeden[i].id
              ) {
                barcieDag.shifts[shift - 1].barcieLeden.splice(i, 1);
                break;
              }
            }
          }
        });
      });
  }

  GetBarcieRooster() {
    this.roosterLoading = true;
    this.httpClient
      .get<any>(environment.baseUrl, {
        params: {
          action: 'GetBarcieRooster'
        }
      })
      .subscribe(
        response => {
          this.roosterLoading = false;
          this.barcieDagen = response.barcieDagen;
        },
        response => {
          this.roosterLoading = true;
          this.errorMessage = response.error;
        }
      );
  }

  AddShift(date) {
    this.barcieDagen.forEach(barcieDag => {
      if (barcieDag.date === date) {
        barcieDag.shifts.push({
          barcieLeden: []
        });
        return;
      }
    });
  }

  SelecteerBarcieLid(geselecteerdeBarcieDag, shift: number) {
    const component = SelecteerBarcieLidComponent;
    component.date = geselecteerdeBarcieDag.date;
    component.datum = geselecteerdeBarcieDag.datum;
    component.shift = shift;
    this.modalService
      .open(component)
      .result.then((barcieLid: { id: number; naam: string }) => {
        if (!barcieLid) {
          return;
        }
        this.GetBarcieRooster();
      })
      .catch(() => {});
  }
}
