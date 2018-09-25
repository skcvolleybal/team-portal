import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { MeespeelTeamsComponent } from './meespeel-teams.component';

describe('MeespeelTeamsComponent', () => {
  let component: MeespeelTeamsComponent;
  let fixture: ComponentFixture<MeespeelTeamsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ MeespeelTeamsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(MeespeelTeamsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
