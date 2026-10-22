import { Component, Inject, OnInit } from "@angular/core";
import { CommonModule } from "@angular/common";
import { FontAwesomeModule } from "@fortawesome/angular-fontawesome";
import { MatDialogModule } from "@angular/material/dialog";
import { MatDialogRef, MAT_DIALOG_DATA } from "@angular/material/dialog";
import { NgbModule } from "@ng-bootstrap/ng-bootstrap";
import { StateService } from "../../core/services/state.service";
import { WedstrijdComponent } from "../../shared/wedstrijd/wedstrijd.component";
import { WordPressService } from "../../core/services/request.service";
import { SwapService } from "../../core/services/swap.service";
import { switchMap } from "rxjs/operators";
import { NotificationService } from "../../core/services/notifications.service";

import { Task } from "./Task";

import {
    faExchangeAlt,
    faTimes,
    faCheck,
    faTimes as faClose,
    faUser,
    faInfoCircle,
    faPlusSquare,
    faMinusSquare,
    faPlus,
} from "@fortawesome/free-solid-svg-icons";

@Component({
    selector: "teamportal-ruil-lijst",
    standalone: true,
    imports: [
        CommonModule,
        FontAwesomeModule,
        MatDialogModule,
        WedstrijdComponent,
        NgbModule,
    ],
    templateUrl: "./ruil-lijst.component.html",
    styleUrls: ["./ruil-lijst.component.scss"],
    providers: [],
})
export class RuilLijstComponent implements OnInit {
    otherTasks!: any[];
    myTasks!: any[];
    isLoading: boolean = true;
    swapIcon = faExchangeAlt;
    timesIcon = faTimes;
    checkIcon = faCheck;
    closeIcon = faClose;
    userIcon = faUser;
    infoIcon = faInfoCircle;
    isCollapsed = true;
    uitklappen = faPlusSquare;
    inklappen = faMinusSquare;
    info = faInfoCircle;
    spelerToevoegen = faPlus;
    teams: any[] = [];
    isTeamCollapsed: boolean[] = [];

    selectedIndexStyle: number | null = null;
    mySelectedTask: Task = {} as Task;
    otherSelectedTask: { [key: number]: object } = {};
    selectedTasksStyle: { [key: string]: boolean } = {};
    currentPage = "page1";

    tasks: Record<number, Task> = {};

    swapsProposedToMe: any[] = [];
    myProposedSwaps: any[] = [];
    isAccepting: { [key: string]: boolean } = {};
    isRejecting: { [key: string]: boolean } = {};
    isDeleting: { [key: string]: boolean } = {};
    proposedShifts: Set<string> = new Set();

    constructor(
        private wordPressService: WordPressService,
        private swapService: SwapService,
        private dialogRef: MatDialogRef<RuilLijstComponent>,
        private notificationService: NotificationService,
        @Inject(MAT_DIALOG_DATA) public data: any,
    ) {}

    ngOnInit(): void {
        this.wordPressService.GetBarDienstenForUser(this.data.userid).subscribe(
            (response: any) => {
                this.myTasks = response.filter(
                    (obj: any) => obj.persoon.id === this.data.userid,
                );
            },
            (error: any) => {
                console.log(error);
                this.isLoading = false;
            },
        );

        this.wordPressService.GetAllBardiensten().subscribe(
            (response) => {
                this.otherTasks = response.filter(
                    (obj) => obj.persoon.id !== this.data.userid,
                );
                this.isLoading = false;
            },
            (error) => {
                this.isLoading = false;
                console.log(error);
            },
        );

        this.swapService.GetProposedSwaps().subscribe((response) => {
            this.swapsProposedToMe = response.filter(
                (obj) => Number(obj.otherUserId) === Number(this.data.userid),
            );
            this.myProposedSwaps = response.filter(
                (obj) =>
                    Number(obj.userWhoProposedId) === Number(this.data.userid),
            );
            this.myProposedSwaps.forEach((swap) => {
                this.proposedShifts.add(String(swap.swapForTaskId));
            });
        });
    }

    closeModal() {
        this.dialogRef.close();
    }

    addProposals() {
        if (Object.keys(this.mySelectedTask).length === 0) {
            this.notificationService.showWarning("Select 1 of your own tasks");
            return;
        }

        if (Object.keys(this.tasks).length === 0) {
            this.notificationService.showWarning(
                "Select 1 or more of someone elses task",
            );
            return;
        }

        for (const [key, value] of Object.entries(this.tasks)) {
            const newSwap = {
                taskToSwapId: this.mySelectedTask.id,
                userWhoProposedId: this.mySelectedTask.persoon.id,
                swapForTaskId: value.id,
                otherUserId: value.persoon.id,
                date: value.bardag.date.date,
                shift: value.shift,
            };
            this.swapService.ProposeSwap(newSwap).subscribe(
                (response) => {
                    this.selectedTasksStyle[key] =
                        !this.selectedTasksStyle[key];
                    this.selectedIndexStyle = null;
                    this.notificationService.showSuccess("Proposal made");
                    this.mySelectedTask = {} as Task;
                    this.proposedShifts.add(String(value.id));
                },
                (error) => {
                    this.notificationService.showError(
                        "1 proposal failed, they might be proposed already.",
                    );
                    console.log(
                        "Error occurred while sending swap proposal:",
                        error,
                    );
                    this.selectedTasksStyle[key] =
                        !this.selectedTasksStyle[key];
                    this.selectedIndexStyle = null;
                    this.mySelectedTask = {} as Task;
                },
            );
        }
    }

    selectButton(index: number, task: object): void {
        this.selectedIndexStyle =
            this.selectedIndexStyle === index ? null : index;
        this.mySelectedTask = task as Task;
    }

    selectTaskButton(index: number, task: object): void {
        this.selectedTasksStyle[index] = !this.selectedTasksStyle[index];
        if (this.selectedTasksStyle[index]) {
            this.tasks[index] = task as Task;
        } else {
            delete this.tasks[index];
        }
    }

    handleAcceptSwap(taskToAccept: any) {
        this.isAccepting[taskToAccept.id] = true;
        const acceptSwap = {
            swapForTaskId: taskToAccept.swapForTaskId,
            otherUserId: taskToAccept.otherUserId,
            userWhoProposedId: taskToAccept.userWhoProposedId,
            taskToSwapId: taskToAccept.taskToSwapId,
        };

        this.swapService
            .AcceptSwap(acceptSwap)
            .pipe(switchMap(() => this.swapService.DeleteSwap(taskToAccept.id)))
            .subscribe({
                next: () => {
                    this.swapsProposedToMe = this.swapsProposedToMe.filter(
                        (task) => task.id !== taskToAccept.id,
                    );
                    this.notificationService.showSuccess("Swap accepted");
                    this.isAccepting[taskToAccept.id] = false;
                },
                error: (error) => {
                    this.notificationService.showError(
                        "Swap not accepted, please reload the page",
                    );
                    console.log("Error in accept or delete swap", error);
                    this.isAccepting[taskToAccept.id] = false;
                },
            });
    }

    handleRejectSwap(taskToDelete: any) {
        this.isRejecting[taskToDelete.id] = true;
        this.swapService.DeleteSwap(taskToDelete.id).subscribe({
            next: () => {
                this.swapsProposedToMe = this.swapsProposedToMe.filter(
                    (task) => task.id !== taskToDelete.id,
                );
                this.isRejecting[taskToDelete.id] = false;
            },
            error: (error) => {
                this.notificationService.showError(
                    "Error in rejecting the swap, please reload the page",
                );
                console.log("Error in DeleteSwap", error);
                this.isRejecting[taskToDelete.id] = false;
            },
        });
    }

    handleDeleteProposal(proposal: any) {
        this.isDeleting[proposal.id] = true;
        this.swapService.DeleteSwap(proposal.id).subscribe({
            next: () => {
                this.myProposedSwaps = this.myProposedSwaps.filter(
                    (swap) => swap.id !== proposal.id,
                );
                this.proposedShifts.delete(String(proposal.swapForTaskId));
                this.notificationService.showSuccess("Proposal deleted");
                this.isDeleting[proposal.id] = false;
            },
            error: (error) => {
                this.notificationService.showError(
                    "Error deleting proposal, please try again",
                );
                console.log("Error in DeleteSwap", error);
                this.isDeleting[proposal.id] = false;
            },
        });
    }

    AddAanwezigheid(speler: any) {
        // Stub - implement as needed
    }

    canProposeSwap(): boolean {
        return (
            this.selectedIndexStyle !== null &&
            Object.values(this.selectedTasksStyle).some((value) => value)
        );
    }

    hasSelectedOtherTasks(): boolean {
        return Object.values(this.selectedTasksStyle).some((value) => value);
    }

    hasSelectedMyTask(): boolean {
        return this.selectedIndexStyle !== null;
    }

    isShiftProposed(shiftId: string | number): boolean {
        return this.proposedShifts.has(String(shiftId));
    }
}
