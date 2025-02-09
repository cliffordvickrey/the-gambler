import {ObserverInterface} from "./ObserverInterface";
import {EventType} from "./EventType";
import {ViewModel} from "./ViewModel";
import {Dom} from "./Dom";
import {SortDirection} from "./SortDirection";

export class EventBinder {
    private dom: Dom;
    private observable: ObserverInterface;
    private viewModel: ViewModel;

    constructor(dom: Dom, observable: ObserverInterface, viewModel: ViewModel) {
        this.dom = dom;
        this.observable = observable;
        this.viewModel = viewModel;
    }

    public bindUiActions(): void {
        $('[data-toggle="tooltip"]').tooltip();

        $("a[data-toggle='tab']").on("hide.bs.tab", (e: any) => {
            let relatedTarget: HTMLAnchorElement = <HTMLAnchorElement>e.relatedTarget;
            let regEx = /#(.*)$/g;
            let matches = regEx.exec(relatedTarget.href);
            let tab = matches[1];

            let gameId: string = null;
            if (null !== this.viewModel.game) {
                gameId = this.viewModel.game.gameId;
            }
            this.observable.notify(EventType.tabClick, {tab: tab, gameId: gameId});
        });

        this.dom.getButton("new-game").addEventListener("click", () => {
            let playerName = this.dom.getInput("player-name").value.trim();

            if ("" === playerName) {
                this.viewModel.alert("Please enter your name", "warning");
                return;
            }
            this.observable.notify(EventType.authenticate, {playerName: playerName});
        });

        this.dom.getButton("bet").addEventListener("click", () => {
            let amount = parseInt(this.dom.getInput("amount").value, 10);
            if (isNaN(amount)) {
                amount = 0;
            }

            if (!this.viewModel.game.meta.cheated && this.viewModel.game.meta.purseNumeric < 1) {
                this.viewModel.alert("Sorry, pal: you're tapped!", "warning");
                return;
            }

            if (amount < 1) {
                this.viewModel.alert("Please enter an amount greater than 0", "warning");
                return;
            }

            this.observable.notify(EventType.bet, {gameId: this.viewModel.game.gameId, amount: amount});
        });

        this.dom.getButton("draw").addEventListener("click", () => {
            this.observable.notify(
                EventType.draw,
                {gameId: this.viewModel.game.gameId, draw: this.viewModel.getDrawId()}
            );
        });

        this.dom.getButton("cheat").addEventListener("click", () => {
            this.viewModel.confirm("cheat").then((result: boolean) => {
                if (result) {
                    this.observable.notify(EventType.cheat, {gameId: this.viewModel.game.gameId});
                }
            });
        });

        this.dom.getButton("hint").addEventListener("click", () => {
            this.observable.notify(EventType.hint, {cardsHeld: [...this.viewModel.game.probability.highDraw]});
        });

        this.dom.getButton("resign").addEventListener("click", () => {
            this.viewModel.confirm("resign").then((result: boolean) => {
                if (result) {
                    this.observable.notify(EventType.resign, {gameId: this.viewModel.game.gameId});
                }
            });
        });

        this.dom.getButton("autopilot").addEventListener("click", () => {
            this.observable.notify(EventType.autopilot);
        });

        let oddsSelect = this.dom.getSelect("odds");
        oddsSelect.addEventListener("change", () => {
            this.observable.notify(EventType.oddsSelect, {selected: oddsSelect.value});
        });

        for (let i = 0; i < 5; i++) {
            let cardView = this.dom.getCardView(i);

            cardView.draw.addEventListener("click", () => {
                let target = cardView.draw;
                let playable = "1" === target.getAttribute("data-playable");
                if (!playable) {
                    return;
                }

                let offset = parseInt(target.getAttribute("data-draw-offset"), 10);
                this.observable.notify(EventType.holdCard, {offset: offset});
            });

            cardView.card.addEventListener("click", () => {
                cardView.draw.click();
            });

            cardView.increase.addEventListener("click", () => {
                let target = cardView.increase;
                let offset = parseInt(target.getAttribute("data-increase-offset"), 10);
                let nextCard = this.viewModel.getNextCard(offset, true);
                this.observable.notify(
                    EventType.spliceCard,
                    {gameId: this.viewModel.game.gameId, offset: offset, card: nextCard}
                );
            });

            cardView.decrease.addEventListener("click", () => {
                let target = cardView.decrease;
                let offset = parseInt(target.getAttribute("data-decrease-offset"), 10);
                let nextCard = this.viewModel.getNextCard(offset, false);
                this.observable.notify(
                    EventType.spliceCard,
                    {gameId: this.viewModel.game.gameId, offset: offset, card: nextCard}
                );
            });
        }

        let oddsView = this.dom.getOddsView();
        Object.keys(oddsView).forEach((tableName: string) => {
            let headerRow = oddsView[tableName]["0"];
            Object.keys(headerRow).forEach((column: string) => {
                let sortingLink = <HTMLAnchorElement>headerRow[column].querySelector("a");
                if (null !== sortingLink) {
                    sortingLink.addEventListener("click", (e: Event) => {
                        let sortIcon = <HTMLSpanElement>sortingLink.querySelector("span.app-sort-icon");
                        let sortDirection: SortDirection;
                        let sortColumn = column;

                        if (null === sortIcon) {
                            sortDirection = SortDirection.asc;
                        } else if (sortIcon.classList.contains("fa-chevron-up")) {
                            sortDirection = SortDirection.desc;
                        } else {
                            sortDirection = SortDirection.asc;
                            sortColumn = "cards";
                        }

                        this.observable.notify(EventType.oddsSort, {
                            column: sortColumn, direction: sortDirection
                        });
                        e.preventDefault();
                        return false;
                    });
                }
            });
        });

        let infoLink = this.dom.getInfoLink();
        infoLink.addEventListener("click", (e: Event) => {
            $(this.dom.getDoodad("info-modal")).modal("show");
            this.observable.notify(EventType.analyze);
            e.preventDefault();
            return false;
        });

        let distributionLink = this.dom.getProbabilityDistributionLink();
        distributionLink.addEventListener("click", (e: Event) => {
            $(this.dom.getDoodad("distribution-modal")).modal("show");
            e.preventDefault();
            return false;
        });
    }
}