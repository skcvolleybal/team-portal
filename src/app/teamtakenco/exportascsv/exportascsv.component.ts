import { Component, OnInit } from '@angular/core';
import { WordPressService } from '../../core/services/request.service';
import { environment } from '../../../environments/environment';
// import { HttpClient } from '@angular/common/http;
// import { saveAs } from 'file-saver';


@Component({
  selector: 'tp-exportascsv',
  templateUrl: './exportascsv.component.html',
  styleUrls: ['./exportascsv.component.scss']
})
export class ExportascsvComponent implements OnInit {
  private errorMessage;
  private data;
  selectedDate: string; // This will store the selected date

  constructor(
    private WordPressService: WordPressService,
  ) { }

  ngOnInit(): void {
  }

  onSubmit() {
    
    console.log('Selected Date:', this.selectedDate); // Handle form submission here, e.g., send the selectedDate to a service or perform an action
    this.WordPressService.GetWeekOverzicht(this.selectedDate).subscribe(
      (response) => {
        this.data = response;
        const url = environment.baseUrl + 'week-overzicht?datum=' + this.selectedDate;

        // const blob = new Blob([this.data], { type: 'text/csv' });
        // const url = window.URL.createObjectURL(blob);
  
        // Create a temporary link and trigger the download
        const a = document.createElement('a');
        a.href = url;
        a.download = 'example.csv';
        document.body.appendChild(a);
        a.click();
  
        // Clean up
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);

      },
      (error) => {
        console.log("error")
        console.log(error);
        if (error.status === 500) {
          this.errorMessage = error.error.message;
        }
      }
    );
  }

  // downloadExcel() {
  //   const url = 'http://localhost/team-portal/api/week-overzicht?datum=2023-10-20';

  //   this.http.get(url, { responseType: 'blob' }).subscribe((data) => {
  //     // Check if the response is a Blob
  //     if (data instanceof Blob) {
  //       // Use the 'file-saver' library to trigger the download
  //       saveAs(data, 'example.xlsx');
  //     } else {
  //       // Handle unexpected response type or errors
  //       console.error('Unexpected response type');
  //     }
  //   });
  // }

}
