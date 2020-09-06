export class Team {
  constructor(public name: string) {}

  getShortNotation(): string {
    const gender = this.name.charAt(0);
    const teamnumber = this.name.substring(6);
    return `${gender}${teamnumber}`;
  }
}
