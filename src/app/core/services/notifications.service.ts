import { Injectable } from "@angular/core";
import { ToastService } from "ngx-yet-another-toast-library";

@Injectable({
    providedIn: "root",
})
export class NotificationService {
    constructor(private toast: ToastService) {}

    showSuccess(message: string, title: string = "Success") {
        this.toast.success(message, title);
    }

    showError(message: string, title: string = "Error") {
        this.toast.error(message, title);
    }

    showWarning(message: string, title: string = "Warning") {
        this.toast.warning(message, title);
    }

    showInfo(message: string, title: string = "Info") {
        this.toast.info(message, title);
    }
}
