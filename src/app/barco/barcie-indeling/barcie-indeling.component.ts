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

  barciedagen = [];
  isLoading: boolean;
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
        this.errorMessage = response.error.message;
      }
    );
  }

  DeleteBarcieDate(date) {
    this.barcoService.DeleteBarcieDag(date).subscribe(
      () => {
        this.GetBarcieRooster();
      },
      response => {
        this.errorMessage = response.error.message;
      }
    );
  }

  ToggleBhv(selectedBarcieDag, selectedBarcielid, shift) {
    this.barcoService.ToggleBhv(
      selectedBarcieDag.date,
      shift,
      selectedBarcielid.id
    );

    this.barciedagen.forEach(barciedag => {
      if (barciedag.date === selectedBarcieDag.date) {
        barciedag.shifts[shift - 1].barleden.forEach(barcielid => {
          if (selectedBarcielid.id === barcielid.id) {
            barcielid.isBhv = !barcielid.isBhv;
            return;
          }
        });
      }
    });
  }

  DeleteAanwezigheid(selectedBarcieDag, selectedBarcielid, shiftNumber) {
    this.aanwezigheidService
      .DeleteBarcieAanwezigheid(
        selectedBarcieDag.date,
        shiftNumber,
        selectedBarcielid.id
      )
      .subscribe(() => {
        this.barciedagen.forEach(barciedag => {
          if (barciedag.date === selectedBarcieDag.date) {
            barciedag.shifts.forEach(shift => {
              shift.barleden.forEach((barlid, i) => {
                if (
                  selectedBarcielid.id === barlid.id &&
                  shift.shift === shiftNumber
                ) {
                  shift.barleden.splice(i, 1);
                  return;
                }
              });
            });
          }
        });
      });
  }

  GetBarcieRooster() {
    this.isLoading = true;
    this.barcoService.GetBarcieRooster().subscribe(
      response => {
        this.isLoading = false;
        this.barciedagen = response;
      },
      response => {
        this.isLoading = true;
        this.errorMessage = response.error.message;
      }
    );
  }

  AddShift(date) {
    this.barciedagen.forEach(barciedag => {
      if (barciedag.date === date) {
        const lastShift = barciedag.shifts[barciedag.shifts.length - 1].shift;
        barciedag.shifts.push({
          barleden: [],
          shift: lastShift + 1
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
      .result.then((barcielid: { id: number; naam: string }) => {
        if (!barcielid) {
          return;
        }
        this.GetBarcieRooster();
      })
      .catch(() => {});
  }
}
