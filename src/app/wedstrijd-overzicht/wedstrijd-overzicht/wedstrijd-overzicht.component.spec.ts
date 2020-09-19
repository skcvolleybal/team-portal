import { ComponentFixture, TestBed, waitForAsync } from '@angular/core/testing';

import { WedstrijdOverzichtComponent } from './wedstrijd-overzicht.component';

describe('WedstrijdOverzichtComponent', () => {
  let component: WedstrijdOverzichtComponent;
  let fixture: ComponentFixture<WedstrijdOverzichtComponent>;

  beforeEach(waitForAsync(() => {
    TestBed.configureTestingModule({
      declarations: [ WedstrijdOverzichtComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(WedstrijdOverzichtComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
