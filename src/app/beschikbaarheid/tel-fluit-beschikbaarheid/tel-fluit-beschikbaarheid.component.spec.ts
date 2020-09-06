import { async, ComponentFixture, TestBed } from '@angular/core/testing';
import { TelFluitBeschikbaarheidComponent } from './tel-fluit-beschikbaarheid.component';

describe('TelFluitBeschikbaarheidComponent', () => {
  let component: TelFluitBeschikbaarheidComponent;
  let fixture: ComponentFixture<TelFluitBeschikbaarheidComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [TelFluitBeschikbaarheidComponent]
    }).compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(TelFluitBeschikbaarheidComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
