import { Team } from './Team';

export class Zaalwacht {
  team: Team;

  constructor(teamname: string) {
    this.team = new Team(teamname);
  }
}
