import { Component, EventEmitter, Input, Output } from '@angular/core';
import {
  faCheck,
  faQuestion,
  faTimes,
} from '@fortawesome/free-solid-svg-icons';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { ToastrService } from 'ngx-toastr';

@Component({
  selector: 'teamportal-keuze',
  templateUrl: './keuze.component.html',
  styleUrls: ['./keuze.component.scss'],
})
export class KeuzeComponent {
  constructor(private toastr: ToastrService) {}

  @Input()
  keuze;
  @Output()
  updateKeuze = new EventEmitter();

  jaIcon = faCheck;
  onbekendIcon = faQuestion;
  neeIcon = faTimes;

  onClick(keuze) {
    this.keuze = keuze;
    this.updateKeuze.emit(keuze);
    this.toastr.success(' ', 'Updated', {
      timeOut: 0,
    });
  }
}
