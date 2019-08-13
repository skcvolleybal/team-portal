import { async, ComponentFixture, TestBed } from '@angular/core/testing';
import { KeuzeComponent } from './keuze.component';

describe('keuzeComponent', () => {
  let component: KeuzeComponent;
  let fixture: ComponentFixture<KeuzeComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [KeuzeComponent]
    }).compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(KeuzeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
