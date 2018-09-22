import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { WedstrijdenComponent } from './wedstrijden.component';

describe('WedstrijdenComponent', () => {
  let component: WedstrijdenComponent;
  let fixture: ComponentFixture<WedstrijdenComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ WedstrijdenComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(WedstrijdenComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
