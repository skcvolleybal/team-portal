import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { BarcieBeschikbaarheidComponent } from './barcie-beschikbaarheid.component';

describe('BarcieBeschikbaarheidComponent', () => {
  let component: BarcieBeschikbaarheidComponent;
  let fixture: ComponentFixture<BarcieBeschikbaarheidComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ BarcieBeschikbaarheidComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(BarcieBeschikbaarheidComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
