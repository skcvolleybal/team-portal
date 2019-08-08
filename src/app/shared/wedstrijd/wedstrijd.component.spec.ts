import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { WedstrijdComponent } from './wedstrijd.component';

describe('WedstrijdComponent', () => {
  let component: WedstrijdComponent;
  let fixture: ComponentFixture<WedstrijdComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ WedstrijdComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(WedstrijdComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
