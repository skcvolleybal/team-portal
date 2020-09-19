import { ComponentFixture, TestBed, waitForAsync } from '@angular/core/testing';

import { SelecteerScheidsrechterComponent } from './selecteer-scheidsrechter.component';

describe('SelecteerScheidsrechterComponent', () => {
  let component: SelecteerScheidsrechterComponent;
  let fixture: ComponentFixture<SelecteerScheidsrechterComponent>;

  beforeEach(waitForAsync(() => {
    TestBed.configureTestingModule({
      declarations: [SelecteerScheidsrechterComponent]
    }).compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(SelecteerScheidsrechterComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
