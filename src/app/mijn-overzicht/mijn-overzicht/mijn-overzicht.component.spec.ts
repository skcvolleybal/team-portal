import { ComponentFixture, TestBed, waitForAsync } from '@angular/core/testing';

import { MijnOverzichtComponent } from './mijn-overzicht.component';

describe('MijnOverzichtComponent', () => {
  let component: MijnOverzichtComponent;
  let fixture: ComponentFixture<MijnOverzichtComponent>;

  beforeEach(waitForAsync(() => {
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
