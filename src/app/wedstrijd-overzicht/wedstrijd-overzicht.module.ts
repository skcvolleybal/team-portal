import { CommonModule } from "@angular/common";
import { NgModule } from "@angular/core";
import { SharedModule } from "../shared/shared.module";
import { InvalTeamsComponent } from "./inval-teams/inval-teams.component";
import { SpelersLijstComponent } from "./spelers-lijst/spelers-lijst.component";
import { WedstrijdOverzichtComponent } from "./wedstrijd-overzicht/wedstrijd-overzicht.component";

@NgModule({
  declarations: [
    InvalTeamsComponent,
    SpelersLijstComponent,
    WedstrijdOverzichtComponent
  ],
  imports: [CommonModule, SharedModule]
})
export class WedstrijdOverzichtModule {}
