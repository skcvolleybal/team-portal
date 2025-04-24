import { Component, Inject, OnInit } from '@angular/core';
import { MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';
import { StateService } from 'src/app/core/services/state.service';
import { WordPressService } from 'src/app/core/services/request.service';
import { SwapService } from 'src/app/core/services/swap.service';
import { switchMap } from 'rxjs/operators';
import { NotificationService } from 'src/app/core/services/notifications.service';

import { Task } from './Task';

import {
  faExchangeAlt,
  faTimes,
  faCheck,
  faTimes as faClose,
  faUser,
  faInfoCircle
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
  timesIcon = faTimes;
  checkIcon = faCheck;
  closeIcon = faClose;
  userIcon = faUser;
  infoIcon = faInfoCircle;

  selectedIndexStyle: number | null = null;
  mySelectedTask: Task = {} as Task;
  otherSelectedTask: {[key: number] : object} = {};
  selectedTasksStyle: { [key: number] : boolean} = {};
  currentPage = 'page1';

  tasks: Record<number, Task> = {};

  swapsProposedToMe: any[] = []; // This element is used for rendering the second page
  myProposedSwaps: any[] = []; // Track swaps proposed by the current user
  isAccepting: { [key: string]: boolean } = {};
  isRejecting: { [key: string]: boolean } = {};
  isDeleting: { [key: string]: boolean } = {};
  proposedShifts: Set<string> = new Set();

  constructor(
    private wordPressService: WordPressService,
    private swapService: SwapService,
    private dialogRef: MatDialogRef<RuilLijstComponent>,
    private notificationService: NotificationService,
    @Inject(MAT_DIALOG_DATA) public data: any // Receive data from parent
  ) {}

  ngOnInit(): void {
      this.wordPressService.GetBarDienstenForUser(this.data.userid).subscribe((response) => {
        this.myTasks = response.filter(obj => obj.persoon.id === this.data.userid)
        }, (error) => {
        console.log(error)
        this.isLoading = false;
      })

      this.wordPressService.GetAllBardiensten().subscribe((response) => {
        this.otherTasks = response.filter(obj => obj.persoon.id !== this.data.userid)
        this.isLoading = false;
      }, (error) => {
        this.isLoading = false;
        console.log(error)
      })

      this.swapService.GetProposedSwaps().subscribe((response) => {
        this.swapsProposedToMe = response.filter(obj => obj.otherUserId === this.data.userid);
        this.myProposedSwaps = response.filter(obj => obj.userWhoProposedId === this.data.userid);
        // Initialize proposed shifts from existing proposals
        this.myProposedSwaps.forEach(swap => {
          this.proposedShifts.add(swap.swapForTaskId);
        });
      })
  }


  closeModal() {
    this.dialogRef.close(); // Close the modal
  }

  addProposals() {
    if (Object.keys(this.mySelectedTask).length === 0) {
      this.notificationService.showWarning("Select 1 of your own tasks")
      return;
    }

    if (Object.keys(this.tasks).length === 0) {
      this.notificationService.showWarning("Select 1 or more of someone elses task")
      return;
    }

    for (const [key, value] of Object.entries(this.tasks)) {
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
        this.notificationService.showSuccess("Proposal made")
        this.mySelectedTask = {} as Task
        // Add to proposed shifts
        this.proposedShifts.add(value.id);
      }, (error) => {
        this.notificationService.showError("1 proposal failed, they might be proposed already.")
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

  handleAcceptSwap(taskToAccept: any) {
    this.isAccepting[taskToAccept.id] = true;
    const acceptSwap = {
      swapForTaskId: taskToAccept.swapForTaskId,
      otherUserId: taskToAccept.otherUserId, // Current user's task
      userWhoProposedId: taskToAccept.userWhoProposedId,
      taskToSwapId: taskToAccept.taskToSwapId // User that wants to swap their task
    }

    this.swapService.AcceptSwap(acceptSwap).pipe(
      switchMap(() => this.swapService.DeleteSwap(taskToAccept.id))
    ).subscribe({
      next: () => {
        this.swapsProposedToMe = this.swapsProposedToMe.filter(task => task.id !== taskToAccept.id);
        this.notificationService.showSuccess("Swap accepted");
        this.isAccepting[taskToAccept.id] = false;
      },
      error: (error) => {
        this.notificationService.showError("Swap not accepted, please reload the page");
        console.log("Error in accept or delete swap", error);
        this.isAccepting[taskToAccept.id] = false;
      }
    });
  }

  handleRejectSwap(taskToDelete: any) {
    this.isRejecting[taskToDelete.id] = true;
    this.swapService.DeleteSwap(taskToDelete.id).subscribe({
      next: () => {
        this.swapsProposedToMe = this.swapsProposedToMe.filter(task => task.id !== taskToDelete.id);
        this.isRejecting[taskToDelete.id] = false;
      },
      error: (error) => {
        this.notificationService.showError("Error in rejecting the swap, please reload the page");
        console.log("Error in DeleteSwap", error);
        this.isRejecting[taskToDelete.id] = false;
      }
    });
  }

  handleDeleteProposal(proposal: any) {
    this.isDeleting[proposal.id] = true;
    this.swapService.DeleteSwap(proposal.id).subscribe({
      next: () => {
        this.myProposedSwaps = this.myProposedSwaps.filter(swap => swap.id !== proposal.id);
        this.proposedShifts.delete(proposal.swapForTaskId);
        this.notificationService.showSuccess("Proposal deleted");
        this.isDeleting[proposal.id] = false;
      },
      error: (error) => {
        this.notificationService.showError("Error deleting proposal, please try again");
        console.log("Error in DeleteSwap", error);
        this.isDeleting[proposal.id] = false;
      }
    });
  }

  canProposeSwap(): boolean {
    return this.selectedIndexStyle !== null && Object.values(this.selectedTasksStyle).some(value => value);
  }

  hasSelectedOtherTasks(): boolean {
    return Object.values(this.selectedTasksStyle).some(value => value);
  }

  hasSelectedMyTask(): boolean {
    return this.selectedIndexStyle !== null;
  }

  isShiftProposed(shiftId: string): boolean {
    return this.proposedShifts.has(shiftId);
  }
}
