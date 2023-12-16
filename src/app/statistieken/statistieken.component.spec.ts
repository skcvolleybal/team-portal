import { ComponentFixture, TestBed } from '@angular/core/testing';

import { StatistiekenComponent } from './statistieken.component';

describe('StatistiekenComponent', () => {
  let component: StatistiekenComponent;
  let fixture: ComponentFixture<StatistiekenComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ StatistiekenComponent ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(StatistiekenComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
