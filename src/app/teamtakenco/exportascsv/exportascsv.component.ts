import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'tp-exportascsv',
  templateUrl: './exportascsv.component.html',
  styleUrls: ['./exportascsv.component.scss']
})
export class ExportascsvComponent implements OnInit {

  constructor() { }

  ngOnInit(): void {
  }

  exportAsCSVButton() {
    console.log("dada")
  }

}
