import { IPunten } from './IPunten';

export interface ISpelsysteem {
  puntenPerRotatie: IPunten[];
  puntenPerRotatieEigenService: IPunten[];
  puntenPerRotatieServiceontvangst: IPunten[];
}
