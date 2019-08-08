import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ScheidsrechterComponent } from './scheidsrechter.component';

describe('ScheidsrechterComponent', () => {
  let component: ScheidsrechterComponent;
  let fixture: ComponentFixture<ScheidsrechterComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ScheidsrechterComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ScheidsrechterComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
