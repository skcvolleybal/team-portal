import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ScheidscoComponent } from './scheidsco.component';

describe('ScheidscoComponent', () => {
  let component: ScheidscoComponent;
  let fixture: ComponentFixture<ScheidscoComponent>;

  beforeEach(async(() => {
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
