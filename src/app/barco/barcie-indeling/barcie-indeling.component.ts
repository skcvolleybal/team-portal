import { Component, OnInit } from '@angular/core';
import { FormControl, FormGroup, Validators } from '@angular/forms';
import { faHeart as heartRegular } from '@fortawesome/free-regular-svg-icons';
import {
  faCalendarAlt,
  faHeart as heartSolid,
  faTrashAlt
} from '@fortawesome/free-solid-svg-icons';
import { NgbDate, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { AanwezigheidService } from '../../core/services/aanwezigheid.service';
import { BarcoService } from '../../core/services/barco.service';
import { SelecteerBarcielidComponent } from '../selecteer-barcie-lid/selecteer-barcie-lid.component';

@Component({
  selector: 'teamportal-barcie-indeling',
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

  constructor(
    private barcoService: BarcoService,
    private aanwezigheidService: AanwezigheidService,
    private modalService: NgbModal
  ) {}

  ngOnInit() {
    this.newDate = new FormGroup({
      date: new FormControl(null, [Validators.required])
    });
    this.GetBarcieRooster();
  }

  onDateSelection(date: NgbDate) {
    this.errorMessage = null;
    this.barcoService.AddBarcieDag(date).subscribe(
      () => {
        this.GetBarcieRooster();
      },
      response => {
        this.errorMessage = response.error;
      }
    );
  }

  DeleteBarcieDate(date) {
    this.barcoService.DeleteBarcieDag(date).subscribe(() => {
      this.GetBarcieRooster();
    });
  }

  ToggleBhv(selectedBarcieDag, selectedBarcielid, shift) {
    this.barcoService.ToggleBhv(
      selectedBarcieDag.date,
      shift,
      selectedBarcielid.id
    );

    this.barcieDagen.forEach(barcieDag => {
      if (barcieDag.date === selectedBarcieDag.date) {
        barcieDag.shifts[shift - 1].barcieLeden.forEach(barcieLid => {
          if (selectedBarcielid.id === barcieLid.id) {
            barcieLid.isBhv = !barcieLid.isBhv;
            return;
          }
        });
      }
    });
  }

  DeleteAanwezigheid(selectedBarcieDag, selectedBarcielid, shift) {
    this.aanwezigheidService
      .DeleteBarcieAanwezigheid(
        selectedBarcieDag.date,
        shift,
        selectedBarcielid.id
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
                selectedBarcielid.id ===
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
    this.barcoService.GetBarcieRooster().subscribe(
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
    const component = SelecteerBarcielidComponent;
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
