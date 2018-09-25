import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { WedstrijdenCardComponent } from './wedstrijden-card.component';

describe('WedstrijdenCardComponent', () => {
  let component: WedstrijdenCardComponent;
  let fixture: ComponentFixture<WedstrijdenCardComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ WedstrijdenCardComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(WedstrijdenCardComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
