import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { BarcieIndelingComponent } from './barcie-indeling.component';

describe('BarcieIndelingComponent', () => {
  let component: BarcieIndelingComponent;
  let fixture: ComponentFixture<BarcieIndelingComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ BarcieIndelingComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(BarcieIndelingComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
