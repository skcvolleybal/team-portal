import { async, ComponentFixture, TestBed } from '@angular/core/testing';
import { BeschikbaarheidComponent } from './beschikbaarheid.component';

describe('BeschikbaarheidComponent', () => {
  let component: BeschikbaarheidComponent;
  let fixture: ComponentFixture<BeschikbaarheidComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [BeschikbaarheidComponent]
    }).compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(BeschikbaarheidComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
