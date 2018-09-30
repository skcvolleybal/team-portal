import { async, ComponentFixture, TestBed } from '@angular/core/testing';
import { AanwezigheidComponent } from './aanwezigheid.component';

describe('AanwezigheidComponent', () => {
  let component: AanwezigheidComponent;
  let fixture: ComponentFixture<AanwezigheidComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [AanwezigheidComponent]
    }).compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AanwezigheidComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
