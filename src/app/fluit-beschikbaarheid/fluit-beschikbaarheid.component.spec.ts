import { async, ComponentFixture, TestBed } from '@angular/core/testing';
import { FluitBeschikbaarheidComponent } from './fluit-beschikbaarheid.component';

describe('FluitBeschikbaarheidComponent', () => {
  let component: FluitBeschikbaarheidComponent;
  let fixture: ComponentFixture<FluitBeschikbaarheidComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [FluitBeschikbaarheidComponent]
    }).compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(FluitBeschikbaarheidComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
