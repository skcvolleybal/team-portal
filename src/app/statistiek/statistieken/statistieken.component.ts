import { Component, OnInit, ViewChild } from '@angular/core';
import { FormBuilder, FormGroup } from '@angular/forms';
import { Chart } from 'chart.js';
import { StatistiekService } from '../../core/services/statistieken.service';
import { AantalGespeeldePunten } from './grafieken/aantal-gespeelde-punten';
import { SetPlusminusGraph } from './grafieken/plusminus-per-persoon';
import { GetGrafiekPuntenPerRotatie } from './grafieken/punten-per-rotatie';
import { SetServicesGraph } from './grafieken/services-per-persoon';
import { IPunten } from './models/IPunten';

@Component({
  selector: 'teamportal-statistieken',
  templateUrl: './statistieken.component.html',
  styleUrls: ['./statistieken.component.scss']
})
export class StatistiekenComponent implements OnInit {
  statistiekForm: FormGroup;
  statistieken: any;

  rotatieGraph: Chart;
  servicesGraph: Chart;
  plusminusGraph: Chart;
  gespeeldePuntenGraph: Chart;

  wedstrijden: any[];

  constructor(
    private fb: FormBuilder,
    private statistiekService: StatistiekService
  ) {}

  ngOnInit() {
    this.statistiekForm = this.fb.group({
      geselecteerdeWedstrijd: '',
      spelsysteem: null,
      rotatiekeuze: null,
      service: null,
      plusminusType: 'totaal'
    });

    this.statistiekService
      .GetEigenWedstrijden()
      .subscribe(wedstrijden => (this.wedstrijden = wedstrijden));

    this.statistiekService.GetStatistieken().subscribe(statistieken => {
      this.statistiekForm
        .get('spelsysteem')
        .setValue(statistieken.spelsystemen[0].type);
      this.statistiekForm.get('rotatiekeuze').setValue('puntenPerRotatie');

      this.statistiekForm
        .get('spelsysteem')
        .valueChanges.subscribe(() => this.DisplayRotatieStats());

      this.statistiekForm
        .get('rotatiekeuze')
        .valueChanges.subscribe(() => this.DisplayRotatieStats());

      this.statistiekForm
        .get('plusminusType')
        .valueChanges.subscribe(() => this.DisplayPlusminusStats());

      this.statistiekForm
        .get('geselecteerdeWedstrijd')
        .valueChanges.subscribe(matchId => {
          this.statistiekService.GetStatistieken(matchId).subscribe(stats => {
            this.DisplayStatistieken(stats);
          });
        });

      this.DisplayStatistieken(statistieken);
    });
  }

  DisplayStatistieken(statistieken: any) {
    this.statistieken = statistieken;

    this.DisplayGespeeldePunten();
    this.DisplayRotatieStats();
    this.DisplayServicesStats();
    this.DisplayPlusminusStats();
  }

  DisplayGespeeldePunten() {
    if (this.gespeeldePuntenGraph) {
      this.gespeeldePuntenGraph.destroy();
    }
    this.gespeeldePuntenGraph = AantalGespeeldePunten(
      this.statistieken.gespeeldePunten
    );
  }

  DisplayPlusminusStats() {
    const type = this.statistiekForm.get('plusminusType').value;
    let label: string;
    let plusminus: IPunten[];
    switch (type) {
      case 'totaal':
        plusminus = this.statistieken.plusminus;
        label = `Totaal`;
        break;
      case 'voor':
        plusminus = this.statistieken.plusminusAlleenVoor;
        label = `Voor`;
        break;
    }

    if (this.plusminusGraph) {
      this.plusminusGraph.destroy();
    }
    this.plusminusGraph = SetPlusminusGraph(plusminus, label);
  }

  DisplayServicesStats() {
    if (this.servicesGraph) {
      this.servicesGraph.destroy();
    }
    this.servicesGraph = SetServicesGraph(this.statistieken.services);
  }

  DisplayRotatieStats() {
    const rotatiekeuze = this.statistiekForm.get('rotatiekeuze').value;
    const type = this.statistiekForm.get('spelsysteem').value;
    const i = this.statistieken.spelsystemen.findIndex(
      spelsysteem => spelsysteem.type === type
    );

    const totaalAantalPunten = this.statistieken.spelsystemen[i]
      .totaalAantalPunten;

    let label: string;
    let puntenPerRotatie: IPunten[];
    switch (rotatiekeuze) {
      case 'puntenPerRotatie':
        puntenPerRotatie = this.statistieken.spelsystemen[i].puntenPerRotatie;
        label = `Totaal (${totaalAantalPunten} punten)`;
        break;
      case 'puntenPerRotatieEigenService':
        puntenPerRotatie = this.statistieken.spelsystemen[i]
          .puntenPerRotatieEigenService;
        label = `Eigen service (${totaalAantalPunten} punten)`;
        break;
      case 'puntenPerRotatieServiceontvangst':
        puntenPerRotatie = this.statistieken.spelsystemen[i]
          .puntenPerRotatieServiceontvangst;
        label = `Serviceontvangst (${totaalAantalPunten} punten)`;
        break;
    }

    if (this.rotatieGraph) {
      this.rotatieGraph.destroy();
    }

    this.rotatieGraph = GetGrafiekPuntenPerRotatie(puntenPerRotatie, label);
  }
}
