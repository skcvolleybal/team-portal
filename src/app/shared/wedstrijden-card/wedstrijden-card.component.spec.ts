import { ComponentFixture, TestBed, waitForAsync } from '@angular/core/testing';

import { WedstrijdenCardComponent } from './wedstrijden-card.component';

describe('WedstrijdenCardComponent', () => {
  let component: WedstrijdenCardComponent;
  let fixture: ComponentFixture<WedstrijdenCardComponent>;

  beforeEach(waitForAsync(() => {
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
