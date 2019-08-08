import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { CoachWedstrijdenComponent } from './coach-wedstrijden.component';

describe('CoachWedstrijdenComponent', () => {
  let component: CoachWedstrijdenComponent;
  let fixture: ComponentFixture<CoachWedstrijdenComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ CoachWedstrijdenComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(CoachWedstrijdenComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
