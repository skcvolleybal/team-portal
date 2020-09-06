import { Persoon } from './Persoon';

export class Wedstrijd {
  coaches: Persoon[];
  date: string;
  datum: string;
  isCoachTeam1: boolean;
  isCoachTeam2: boolean;
  isScheidsrechter: boolean;
  isTeam1: boolean;
  isTeam2: boolean;
  isTellers: boolean;
  locatie: string;
  matchId: string;
  scheidsrechter: Persoon;
  setstanden: string;
  team1: string;
  team2: string;
  teams: string;
  tellers: Persoon[];
  tijd: string;
}
