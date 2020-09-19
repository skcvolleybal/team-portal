import { ComponentFixture, TestBed, waitForAsync } from '@angular/core/testing';

import { SelecteerBarcieLidComponent } from './selecteer-barcie-lid.component';

describe('SelecteerBarcieLidComponent', () => {
  let component: SelecteerBarcieLidComponent;
  let fixture: ComponentFixture<SelecteerBarcieLidComponent>;

  beforeEach(waitForAsync(() => {
    TestBed.configureTestingModule({
      declarations: [ SelecteerBarcieLidComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(SelecteerBarcieLidComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
