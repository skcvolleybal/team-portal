import { Component, OnInit } from "@angular/core";
import { CommonModule } from "@angular/common";

@Component({
    selector: "tp-exportascsv",
    standalone: true,
    imports: [CommonModule],
    templateUrl: "./exportascsv.component.html",
    styleUrls: ["./exportascsv.component.scss"],
})
export class ExportascsvComponent implements OnInit {
    private errorMessage;
    private data;
    selectedDate: string;

    constructor(private WordPressService: WordPressService) {}

    ngOnInit(): void {}

    onSubmit() {
        console.log("Selected Date:", this.selectedDate);
        this.WordPressService.GetWeekOverzicht(this.selectedDate).subscribe(
            (response) => {
                this.data = response;
                const url =
                    environment.baseUrl +
                    "week-overzicht?datum=" +
                    this.selectedDate;

                const a = document.createElement("a");
                a.href = url;
                a.download = "example.csv";
                document.body.appendChild(a);
                a.click();

                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
            },
            (error) => {
                console.log("error");
                console.log(error);
                if (error.status === 500) {
                    this.errorMessage = error.error.message;
                }
            },
        );
    }
}
