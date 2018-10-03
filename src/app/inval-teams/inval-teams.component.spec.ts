import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { InvalTeamsComponent } from './inval-teams.component';

describe('InvalTeamsComponent', () => {
  let component: InvalTeamsComponent;
  let fixture: ComponentFixture<InvalTeamsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ InvalTeamsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(InvalTeamsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
