import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { MijnOverzichtComponent } from './mijn-overzicht.component';

describe('MijnOverzichtComponent', () => {
  let component: MijnOverzichtComponent;
  let fixture: ComponentFixture<MijnOverzichtComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ MijnOverzichtComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(MijnOverzichtComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
