import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { FluitAanwezigheidComponent } from './fluit-aanwezigheid.component';

describe('FluitAanwezigheidComponent', () => {
  let component: FluitAanwezigheidComponent;
  let fixture: ComponentFixture<FluitAanwezigheidComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ FluitAanwezigheidComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(FluitAanwezigheidComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
