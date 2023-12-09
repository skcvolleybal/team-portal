import { Component, OnInit } from '@angular/core';
import { UntypedFormControl, UntypedFormGroup, Validators } from '@angular/forms';
import { NgbDate, NgbModal } from '@ng-bootstrap/ng-bootstrap';
import {
  faUser,
  faTrash,
  faCalendarAlt,
  faTrashAlt,
  faHeart as heartSolid,
} from '@fortawesome/free-solid-svg-icons';

import { AanwezigheidService } from '../../core/services/aanwezigheid.service';
import { BarcieService } from '../../core/services/barcie.service';
import { SelecteerBarcielidComponent } from '../selecteer-barcie-lid/selecteer-barcie-lid.component';
import { faHeart as heartRegular } from '@fortawesome/free-regular-svg-icons';

@Component({
  selector: 'teamportal-barcie-indeling',
  templateUrl: './barcie-indeling.component.html',
  styleUrls: ['./barcie-indeling.component.scss'],
})
export class BarcieIndelingComponent implements OnInit {
  calendar = faCalendarAlt;
  user = faUser;
  geenBhv = heartRegular;
  bhv = heartSolid;
  delete = faTrashAlt;
  newDate: UntypedFormGroup;

  barciedagen = [];
  isLoading: boolean;
  errorMessage: string;
  bardiedagenEmpty: boolean = false;

  constructor(
    private barcieService: BarcieService,
    private aanwezigheidService: AanwezigheidService,
    private modalService: NgbModal
  ) {}

  ngOnInit() {
    this.newDate = new UntypedFormGroup({
      date: new UntypedFormControl(null, [Validators.required]),
    });
    this.GetBarcieRooster();
  }

  onDateSelection(date: NgbDate) {
    this.errorMessage = null;
    this.barcieService.AddBarcieDag(date).subscribe(
      () => {
        this.GetBarcieRooster();
      },
      (response) => {
        this.errorMessage = response.error.message;
      }
    );
  }

  DeleteBarcieDate(date) {
    this.barcieService.DeleteBarcieDag(date).subscribe(
      () => {
        this.GetBarcieRooster();
      },
      (response) => {
        this.errorMessage = response.error.message;
      }
    );
  }

  ToggleBhv(selectedBarcieDag, selectedBarcielid, shift) {
    this.barcieService.ToggleBhv(
      selectedBarcieDag.date,
      shift,
      selectedBarcielid.id
    );

    this.barciedagen.forEach((barciedag) => {
      if (barciedag.date === selectedBarcieDag.date) {
        barciedag.shifts[shift - 1].barleden.forEach((barcielid) => {
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
        this.barciedagen.forEach((barciedag) => {
          if (barciedag.date === selectedBarcieDag.date) {
            barciedag.shifts.forEach((shift) => {
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
    this.barcieService.GetBarcieRooster().subscribe(
      (response) => {
        this.isLoading = false;
        this.barciedagen = response;
        if (this.barciedagen.length == 0) {
          this.bardiedagenEmpty = true;
        }
      },
      (response) => {
        this.isLoading = true;
        this.errorMessage = response.error.message;
      }
    );
  }

  AddShift(date) {
    this.barciedagen.forEach((barciedag) => {
      if (barciedag.date === date) {
        const lastShift = barciedag.shifts[barciedag.shifts.length - 1].shift;
        barciedag.shifts.push({
          barleden: [],
          shift: lastShift + 1,
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
