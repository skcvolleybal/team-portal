import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ExportascsvComponent } from './exportascsv.component';

describe('ExportascsvComponent', () => {
  let component: ExportascsvComponent;
  let fixture: ComponentFixture<ExportascsvComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ExportascsvComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(ExportascsvComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
