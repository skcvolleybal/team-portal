import { ComponentFixture, TestBed, waitForAsync } from '@angular/core/testing';

import { BarcieIndelingComponent } from './barcie-indeling.component';

describe('BarcieIndelingComponent', () => {
  let component: BarcieIndelingComponent;
  let fixture: ComponentFixture<BarcieIndelingComponent>;

  beforeEach(waitForAsync(() => {
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
