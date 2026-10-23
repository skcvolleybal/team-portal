import { Component, OnInit } from "@angular/core";
import { CommonModule } from "@angular/common";
import { FontAwesomeModule } from "@fortawesome/angular-fontawesome";
import { MatDialogModule } from "@angular/material/dialog";
import { MatTabsModule } from "@angular/material/tabs";
import { calenderGenerator } from "../../core/services/calenderGenerator";
import { RuilLijstComponent } from "../ruil-lijst/ruil-lijst.component";
import { faExchangeAlt, faTimes } from "@fortawesome/free-solid-svg-icons";
import {
    faCalendarCheck,
    faPlusSquare,
    faUser,
    faInfoCircle,
    faPeopleCarry,
    faCalendar,
    faCheckCircle,
} from "@fortawesome/free-solid-svg-icons";
import { WordPressService } from "../../core/services/request.service";
import { StateService } from "../../core/services/state.service";
import { SwapService } from "../../core/services/swap.service";
import { MatDialog } from "@angular/material/dialog";

@Component({
    selector: "teamportal-mijn-overzicht",
    standalone: true,
    imports: [
        CommonModule,
        FontAwesomeModule,
        MatDialogModule,
        MatTabsModule,
        RuilLijstComponent,
    ],
    templateUrl: "./mijn-overzicht.component.html",
    styleUrls: ["./mijn-overzicht.component.scss"],
    providers: [calenderGenerator],
})
export class MijnOverzichtComponent implements OnInit {
    loading!: boolean;
    taskIcon = faUser;
    scheidsrechterIcon = faUser;
    tellersIcon = faCalendarCheck;
    calenderIcon = faCalendar;
    openIcon = faPlusSquare;
    dagen!: any[];
    errorMessage!: string;
    dagenEmpty: boolean = false;
    user: any;
    zaalwacht = faPeopleCarry;
    pendingProposals: number = 0;
    private isAuthenticatedSubscriptionSet = false;
    showRuilLijst = false;
    proposed: { [key: number]: string } = {};
    isPressed: { [key: number]: boolean } = {};
    infoIcon = faInfoCircle;
    swapIcon = faExchangeAlt;
    closeIcon = faTimes;
    myTasks!: any[];
    selectedIndexStyle: number | null = null;
    otherTasks!: any[];
    selectedTasksStyle: boolean[] = [];
    userIcon = faUser;
    checkIcon = faCheckCircle;
    timesIcon = faTimes;
    myProposedSwaps: any[] = [];
    swapsProposedToMe: any[] = [];
    isAccepting: { [key: number]: boolean } = {};
    isDeleting: { [key: number]: boolean } = {};
    isRejecting: { [key: number]: boolean } = {};

    constructor(
        private wordPressService: WordPressService,
        private stateService: StateService,
        private SwapService: SwapService,
        private dialog: MatDialog,
    ) {}

    ngOnInit() {
        this.loadOverview();
        if (!this.isAuthenticatedSubscriptionSet) {
            this.isAuthenticatedSubscriptionSet = true;
            this.stateService.isAuthenticated.subscribe((isAuthenticated) => {
                if (isAuthenticated) {
                    this.loadOverview();
                }
            });
        }
        this.wordPressService.GetCurrentUser().subscribe(
            (data) => {
                this.user = data;
                this.getPendingProposals();
            },
            (error) => {
                this.loading = false;
            },
        );
    }

    loadOverview() {
        this.loading = true;
        this.wordPressService.GetMijnOverzicht().subscribe(
            (response) => {
                this.dagen = response;
                this.loading = false;
                if (this.dagen.length == 0) {
                    this.dagenEmpty = true;
                }
            },
            (error) => {
                if (error.status === 500) {
                    this.errorMessage = error.error.message;
                }
                this.loading = false;
            },
        );
    }

    getPendingProposals() {
        if (this.user) {
            this.SwapService.GetProposedSwaps().subscribe((response) => {
                this.pendingProposals = response.filter(
                    (obj) => Number(obj.otherUserId) === Number(this.user.id),
                ).length;
            });
        }
    }

    openModal() {
        if (!this.user) {
            return;
        }
        const dialogRef = this.dialog.open(RuilLijstComponent, {
            width: "900px",
            panelClass: "custom-modal",
            data: { userid: this.user.id },
        });
        dialogRef.afterClosed().subscribe(() => {
            this.getPendingProposals();
        });
    }

    closeModal() {
        this.showRuilLijst = false;
    }

    selectButton(i: number, task: any) {
        this.selectedIndexStyle = i;
    }

    hasSelectedMyTask(): boolean {
        return this.selectedIndexStyle !== null;
    }

    hasSelectedOtherTasks(): boolean {
        return this.selectedTasksStyle.some((selected) => selected);
    }

    isShiftProposed(taskId: any): boolean {
        return this.myProposedSwaps.some((p) => p.id === taskId);
    }

    selectTaskButton(i: number, task: any) {
        this.selectedTasksStyle[i] = !this.selectedTasksStyle[i];
    }

    addProposals() {
        // TODO: Implement swap proposal logic
    }

    canProposeSwap(): boolean {
        return this.hasSelectedMyTask() && this.hasSelectedOtherTasks();
    }

    handleAcceptSwap(task: any) {
        this.isAccepting[task.id] = true;
        // TODO: Implement accept swap logic
    }

    handleDeleteProposal(proposal: any) {
        this.isDeleting[proposal.id] = true;
        // TODO: Implement delete proposal logic
    }

    handleRejectSwap(task: any) {
        this.isRejecting[task.id] = true;
        // TODO: Implement reject swap logic
    }
}
