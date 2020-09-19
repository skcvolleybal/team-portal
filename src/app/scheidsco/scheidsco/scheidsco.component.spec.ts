import { ComponentFixture, TestBed, waitForAsync } from '@angular/core/testing';

import { ScheidscoComponent } from './scheidsco.component';

describe('ScheidscoComponent', () => {
  let component: ScheidscoComponent;
  let fixture: ComponentFixture<ScheidscoComponent>;

  beforeEach(waitForAsync(() => {
    TestBed.configureTestingModule({
      declarations: [ ScheidscoComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ScheidscoComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
