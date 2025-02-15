import { Component, Inject, OnInit } from '@angular/core';
import { MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';
import { StateService } from 'src/app/core/services/state.service';
import { WordPressService } from 'src/app/core/services/request.service';
import { SwapService } from 'src/app/core/services/swap.service';

import { Task } from './Task';

import {
  faExchangeAlt,
  
} from '@fortawesome/free-solid-svg-icons';

@Component({
  selector: 'teamportal-ruil-lijst',
  templateUrl: './ruil-lijst.component.html',
  styleUrls: ['./ruil-lijst.component.scss'],
  providers: []
})

export class RuilLijstComponent implements OnInit {



  otherTasks: any[];
  myTasks: any[];
  isLoading: boolean = true;
  swapIcon = faExchangeAlt;

  selectedIndexStyle: number | null = null;
  mySelectedTask: Task = {} as Task;
  otherSelectedTask: {[key: number] : object} = {};
  selectedTasksStyle: { [key: number] : boolean} = {};
  currentPage = 'page1';

  tasks: Record<number, Task> = {};

  swapsProposedToMe: any[] = [];


  constructor(
    private wordPressService: WordPressService,
    private stateService: StateService,
    private swapService: SwapService,
    private dialogRef: MatDialogRef<RuilLijstComponent>,
    @Inject(MAT_DIALOG_DATA) public data: any // Receive data from parent
  ) {}

  ngOnInit(): void {
      this.wordPressService.GetBarDienstenForUser(this.data.userid).subscribe((response) => {
        console.log(response)
        this.myTasks = response.filter(obj => obj.persoon.id === this.data.userid)
        }, (error) => {
        console.log(error)
        this.isLoading = false;
      })

      this.wordPressService.GetAllBardiensten().subscribe((response) => {
        this.otherTasks = response.filter(obj => obj.persoon.id !== this.data.userid)
        console.log(response)
        this.isLoading = false;
      }, (error) => {
        this.isLoading = false;
        console.log(error)
      })

      this.swapService.GetProposedSwaps().subscribe((response) => {
        console.log(response)
        console.log(this.data.userid)
        this.swapsProposedToMe = response.filter(obj => obj.otherUserId === this.data.userid)
        console.log("getallswap")
        console.log(this.swapsProposedToMe)
      })
  }


  closeModal() {
    this.dialogRef.close(); // Close the modal
  }

  addProposals() {
    if (Object.keys(this.mySelectedTask).length === 0) {
      alert('Select one of your own tasks')
      return;
    }

    if (Object.keys(this.tasks).length === 0) {
      alert('Select someone elses task')
      return;
    }

    // i want to POST 
    // my own thingy in conjunction with the other thingys

    for (const [key, value] of Object.entries(this.tasks)) {
      console.log(value)
      const newSwap = {
        taskToSwapId: this.mySelectedTask.id,
        userWhoProposedId: this.mySelectedTask.persoon.id,
        swapForTaskId: value.id,
        otherUserId: value.persoon.id,
        date: value.bardag.date.date,
        shift: value.shift
      }
      this.swapService.ProposeSwap(newSwap).subscribe((response) => {
        this.selectedTasksStyle[key] = !this.selectedTasksStyle[key]
        this.selectedIndexStyle = null;

        this.mySelectedTask = {} as Task
        console.log("success")
      }, (error) => {
        console.log('Error occurred while sending swap proposal:', error)
        this.selectedTasksStyle[key] = !this.selectedTasksStyle[key]
        this.selectedIndexStyle = null;
        this.mySelectedTask = {} as Task
      })
    }
  }

  selectButton(index: number, task: object): void {
    // Set the selected button index
    this.selectedIndexStyle = this.selectedIndexStyle === index ? null : index;
    this.mySelectedTask = task as Task;
  }

  selectTaskButton(index: number, task: object): void {
    this.selectedTasksStyle[index] = !this.selectedTasksStyle[index]
    if (this.selectedTasksStyle[index]) {
      this.tasks[index] = task as Task;
    } else {
      delete this.tasks[index]; // Proper way to delete
    }
  }

  handleAcceptSwap(task: Task) {
    console.log('accept')
    console.log(task)

  }

  handleRejectSwap(task: Task) {
    console.log('reject')
    console.log(task)
  }
}
