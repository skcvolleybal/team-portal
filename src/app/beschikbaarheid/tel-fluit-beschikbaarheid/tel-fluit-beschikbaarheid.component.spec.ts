import { ComponentFixture, TestBed, waitForAsync } from '@angular/core/testing';

import { TelFluitBeschikbaarheidComponent } from './tel-fluit-beschikbaarheid.component';

describe('TelFluitBeschikbaarheidComponent', () => {
  let component: TelFluitBeschikbaarheidComponent;
  let fixture: ComponentFixture<TelFluitBeschikbaarheidComponent>;

  beforeEach(waitForAsync(() => {
    TestBed.configureTestingModule({
      declarations: [TelFluitBeschikbaarheidComponent]
    }).compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(TelFluitBeschikbaarheidComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
