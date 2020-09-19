import { ComponentFixture, TestBed, waitForAsync } from '@angular/core/testing';

import { SelecteerZaalwachtComponent } from './selecteer-zaalwacht.component';

describe('SelecteerZaalwachtComponent', () => {
  let component: SelecteerZaalwachtComponent;
  let fixture: ComponentFixture<SelecteerZaalwachtComponent>;

  beforeEach(waitForAsync(() => {
    TestBed.configureTestingModule({
      declarations: [ SelecteerZaalwachtComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(SelecteerZaalwachtComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
