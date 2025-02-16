import { Injectable } from '@angular/core';
import { ToastrService } from 'ngx-toastr';

@Injectable({
  providedIn: 'root'
})
export class NotificationService {
  constructor(private toastr: ToastrService) {}

  showSuccess(message: string, title: string = 'Success') {
    this.toastr.success(message, title);
  }

  showError(message: string, title: string = 'Error') {
    this.toastr.error(message, title);
  }

  showWarning(message: string, title: string = 'Warning') {
    this.toastr.warning(message, title);
  }

  showInfo(message: string, title: string = 'Info') {
    this.toastr.info(message, title);
  }
}