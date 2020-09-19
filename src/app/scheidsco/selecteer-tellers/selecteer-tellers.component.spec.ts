import { ComponentFixture, TestBed, waitForAsync } from '@angular/core/testing';

import { SelecteerTellersComponent } from './selecteer-tellers.component';

describe('SelecteerTellersComponent', () => {
  let component: SelecteerTellersComponent;
  let fixture: ComponentFixture<SelecteerTellersComponent>;

  beforeEach(waitForAsync(() => {
    TestBed.configureTestingModule({
      declarations: [ SelecteerTellersComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(SelecteerTellersComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
