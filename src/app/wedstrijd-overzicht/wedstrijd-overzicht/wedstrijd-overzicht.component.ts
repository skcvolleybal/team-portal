import { Component, OnInit } from "@angular/core";
import { AanwezigheidService } from "../../core/services/aanwezigheid.service";
import { JoomlaService } from "../../core/services/request.service";

@Component({
  selector: "teamportal-wedstrijd-overzicht",
  templateUrl: "./wedstrijd-overzicht.component.html",
  styleUrls: ["./wedstrijd-overzicht.component.scss"]
})
export class WedstrijdOverzichtComponent implements OnInit {
  wedstrijden: any[];
  loading: boolean;
  errorMessage: string;
  user: any;

  constructor(
    private aanwezigheidService: AanwezigheidService,
    private joomalService: JoomlaService
  ) {}

  ngOnInit() {
    this.loading = true;
    this.joomalService.GetWedstrijdOverzicht().subscribe(
      wedstrijden => {
        this.wedstrijden = wedstrijden;
        this.loading = false;
      },
      error => {
        if (error.status === 500) {
          this.errorMessage = error.error.message;
          this.loading = false;
        }
      }
    );

    this.joomalService.GetCurrentUser().subscribe(data => {
      this.user = data;
    });
  }

  GetCoaches(aanwezigheden: any[]) {
    const coaches = aanwezigheden
      .map(aanwezigheid => aanwezigheid.naam)
      .join(", ");

    const firstWord = aanwezigheden.length === 1 ? "Coach" : "Coaches";
    return `${firstWord}: ${coaches}`;
  }

  UpdateAanwezigheid(currentWedstrijd: any, isAanwezig: boolean, speler: any) {
    const matchId = currentWedstrijd.matchId;
    const rol = currentWedstrijd.isEigenWedstrijd ? "speler" : "coach";
    this.aanwezigheidService.UpdateAanwezigheid(
      currentWedstrijd.matchId,
      isAanwezig,
      speler.id,
      rol
    );

    this.wedstrijden.forEach(wedstrijd => {
      if (wedstrijd.matchId === matchId) {
        wedstrijd.afwezigen = wedstrijd.afwezigen.filter(
          afwezige => afwezige.id !== speler.id
        );
        wedstrijd.aanwezigen = wedstrijd.aanwezigen.filter(
          aanwezige => aanwezige.id !== speler.id
        );
        wedstrijd.onbekend = wedstrijd.onbekend.filter(
          onbekende => onbekende.id !== speler.id
        );

        const isInvaller = speler.id !== this.user.id;
        if (isInvaller && isAanwezig === null) {
          return;
        }

        const newSpeler = {
          id: speler.id,
          naam: speler.naam,
          isInvaller
        };

        const SortTeam = (speler1, speler2) =>
          speler1.naam > speler2.naam ? 1 : -1;

        switch (isAanwezig) {
          case true:
            wedstrijd.aanwezigen.push(newSpeler);
            wedstrijd.aanwezigen.sort(SortTeam);
            break;
          case false:
            wedstrijd.afwezigen.push(newSpeler);
            wedstrijd.afwezigen.sort(SortTeam);
            break;
          case null:
            wedstrijd.onbekend.push(newSpeler);
            wedstrijd.onbekend.sort(SortTeam);
            break;
        }

        return;
      }
    });
  }
}
