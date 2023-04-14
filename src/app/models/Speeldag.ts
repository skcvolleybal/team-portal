import { Bardienst } from './Bardienst';
import { Speeltijd } from './Speeltijd';
import { Wedstrijd } from './Wedstrijd';
import { Zaalwacht } from './Zaalwacht';

export class Speeldag {
  bardiensten: Bardienst;
  zaalwacht: Zaalwacht;
  date: string;
  datum: string;
  datum_long: string;
  speeltijden = new Array<Speeltijd>();
  eersteZaalwacht: string;
  eersteZaalwachtShortNotation: string;
  tweedeZaalwacht: string;
  tweedeZaalwachtShortNotation: string;
  eigenWedstrijden: Wedstrijd[];
}
