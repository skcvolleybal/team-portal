import { ComponentFixture, TestBed, waitForAsync } from '@angular/core/testing';

import { StatistiekenComponent } from './statistieken.component';

describe('StatistiekenComponent', () => {
  let component: StatistiekenComponent;
  let fixture: ComponentFixture<StatistiekenComponent>;

  beforeEach(waitForAsync(() => {
    TestBed.configureTestingModule({
      declarations: [ StatistiekenComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(StatistiekenComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
