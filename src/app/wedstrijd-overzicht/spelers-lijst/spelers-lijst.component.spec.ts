import { ComponentFixture, TestBed, waitForAsync } from '@angular/core/testing';

import { SpelersLijstComponent } from './spelers-lijst.component';

describe('SpelersLijstComponent', () => {
  let component: SpelersLijstComponent;
  let fixture: ComponentFixture<SpelersLijstComponent>;

  beforeEach(waitForAsync(() => {
    TestBed.configureTestingModule({
      declarations: [ SpelersLijstComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(SpelersLijstComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
