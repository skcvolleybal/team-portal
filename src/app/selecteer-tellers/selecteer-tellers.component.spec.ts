import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { SelecteerTellersComponent } from './selecteer-tellers.component';

describe('SelecteerTellersComponent', () => {
  let component: SelecteerTellersComponent;
  let fixture: ComponentFixture<SelecteerTellersComponent>;

  beforeEach(async(() => {
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
