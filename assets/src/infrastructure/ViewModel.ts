import {Dom} from "./Dom";
import {RulesInterface} from "../domain/RulesInterface";
import {ImageFlyweightFactory} from "./ImageFlyweightFactory";
import {Game} from "../domain/Game";
import {PlayerSession} from "../domain/PlayerSession";
import {GameMeta} from "../domain/GameMeta";
import {GameUtil} from "../domain/GameUtil";
import {SortDirection} from "./SortDirection";
import {HighScores} from "../domain/HighScores";
import {HighScore} from "../domain/HighScore";
import {log} from "util";

declare let $: JQueryStatic;

export class ViewModel {
    public game: Game = null;
    private readonly dom: Dom;
    private readonly imageFlyweightFactory: ImageFlyweightFactory;
    private cardsHeld: boolean[] = [false, false, false, false, false];
    private oddsAreStale: boolean = true;
    private oddsAreOutOfOrder: boolean = false;

    constructor(dom: Dom, imageFlyweightFactory: ImageFlyweightFactory) {
        this.dom = dom;
        this.imageFlyweightFactory = imageFlyweightFactory;
    }

    private static htmlEncode(content: string): string {
        let temp = <HTMLSpanElement>document.createElement("span");
        temp.textContent = content;
        return temp.innerHTML;
    }

    public setRules(rules: RulesInterface): void {
        for (let handType in rules.handPayouts) {
            let rulesPayoutCell = this.dom.getRulesPayoutCell(handType);
            rulesPayoutCell.textContent = rules.handPayouts[handType];
        }

        let defaultBetElements = this.dom.getDoodads("default-bet");
        for (let i = 0; i < defaultBetElements.length; i++) {
            defaultBetElements.item(i).innerText = rules.betAmount;
        }

        this.dom.getInput("amount").value = rules.betAmount.replace(/^\$/g, "").replace(/\.00$/g, "");
    }

    public showTab(tabName: string): void {
        $(this.dom.getTab(tabName)).tab("show");
    }

    public getNextCard(offset: number, up: boolean): number {
        let card = this.game.state.hand[offset];

        let nextCard = null;
        while (!nextCard) {
            card += (up ? 1 : -1);
            if (card < 1) {
                card = 52;
            } else if (card > 52) {
                card = 1;
            }

            let valid = true;
            for (let i = 0; i < this.game.state.hand.length; i++) {
                if (i === offset) {
                    continue;
                }

                if (this.game.state.hand[i] === card) {
                    valid = false;
                    break;
                }
            }

            if (valid) {
                nextCard = card;
            }
        }

        return nextCard;
    }

    public confirm(name: string): Promise<boolean> {
        return new Promise<boolean>((resolve) => {
            let modal = this.dom.getConfirmationModal(name);
            let jqModal = $(modal);
            jqModal.off("shown.bs.modal");
            jqModal.off("hidden.bs.modal");

            jqModal.on("shown.bs.modal", () => {
                let button = modal.querySelectorAll("button.btn-primary");
                let jqButton = $(button);
                jqButton.off("click");
                jqButton.on("click", () => {
                    resolve(true);
                    jqModal.modal("hide");
                });
            });

            jqModal.on("hidden.bs.modal", () => {
                resolve(false);
            });

            jqModal.modal("show");
        });
    }

    public sortOddsColumn(column: string, direction: SortDirection): void {
        if ("cards" === column) {
            direction = SortDirection.asc;
        }

        let sortIcons = this.dom.getSortIcons();
        for (let i = sortIcons.length - 1; i >= 0; i--) {
            let sortIcon = sortIcons.item(i);
            sortIcon.parentElement.removeChild(sortIcon);
        }

        let oddsView = this.dom.getOddsView();

        Object.keys(oddsView).forEach((tableName: string) => {
            let toSort: any[] = [];

            let table = oddsView[tableName];
            Object.keys(table).forEach((rowId: string) => {
                if ("0" === rowId) {
                    return;
                }

                let cell = table[rowId][column];

                toSort.push({rowId: Number(rowId), sortValue: Number(cell.getAttribute("data-sort-value"))});
            });

            if (direction === SortDirection.desc) {
                toSort.sort((a: any, b: any) => (a.sortValue < b.sortValue) ? 1 : -1);
            } else {
                toSort.sort((a: any, b: any) => (a.sortValue > b.sortValue) ? 1 : -1);
            }

            let tBody = <HTMLTableSectionElement>this.dom.getTable(tableName).querySelector("tbody");

            toSort.forEach((sorted: any) => {
                let rowId = String(sorted.rowId);
                let row: HTMLTableRowElement = <HTMLTableRowElement>table[rowId]["cards"].parentElement;
                tBody.appendChild(row);
            });
        });

        if ("cards" === column) {
            this.oddsAreOutOfOrder = false;
            return;
        }

        Object.keys(oddsView).forEach((tableName: string) => {
            let heading = oddsView[tableName]["0"][column];
            let sortLink = <HTMLAnchorElement>heading.querySelector("a");

            let icon = <HTMLSpanElement>document.createElement("span");
            icon.className = "app-sort-icon fas fa-chevron-" + (SortDirection.asc === direction ? "up" : "down");
            sortLink.appendChild(icon);
        });

        this.oddsAreOutOfOrder = true;
    }

    public setGame(game: Game): void {
        this.game = game;
        this.cardsHeld = [false, false, false, false, false];

        let hasGame = null !== game;

        this.dom.enableButton("new-game", !hasGame);
        this.dom.enableButton("resign", hasGame);
        this.dom.enableTab("game", hasGame);

        let gameLog = this.dom.getGameLog();

        if (!hasGame) {
            gameLog.innerHTML = "";
            this.dom.enableTab("odds", false);
            this.setMeta(null);
            return;
        }

        let state = game.state;
        let meta = game.meta;

        let hand: number[] = state.hand;
        let hasHand: boolean;
        if (!hand) {
            hasHand = false;
            hand = [0, 0, 0, 0, 0];
        } else {
            hasHand = true;
        }

        let readyToBet = !hasHand || null !== state.handType;
        this.setHand(hand, state.cardsHeld, state.cardsDealt, meta.cheated);
        this.setMeta(meta);

        this.dom.showButton("bet", readyToBet);
        this.dom.showButton("draw", !readyToBet && hasHand);
        this.dom.enableButton("cheat", !meta.cheated);
        this.dom.enableButton("hint", meta.cheated && !readyToBet && hasHand);

        if (!readyToBet && hasHand) {
            this.oddsAreStale = true;
        }

        this.dom.enableTab("odds", meta.cheated && hasHand);

        let input = this.dom.getInput("amount");

        if (readyToBet && meta.cheated) {
            input.min = "0";
            input.max = "9999";
            input.readOnly = false;
        } else if (readyToBet) {
            let value = parseInt(input.value, 10);
            if (isNaN(value) || value < 0) {
                value = 0;
            }

            if (value > meta.purseNumeric) {
                input.value = meta.purse;
            }

            input.min = "0";
            input.max = String(meta.purseNumeric);
            input.readOnly = false;
        } else {
            input.readOnly = true;
        }

        let logId = game.meta.turn + 1;
        if (null !== game.state.handType) {
            logId--;
        }

        let logEntry: HTMLDivElement;
        logEntry = <HTMLDivElement>gameLog.querySelector("div[data-log-id='" + String(logId) + "']");
        if (null === logEntry) {
            logEntry = <HTMLDivElement>document.createElement("div");
            logEntry.setAttribute("data-log", "1");
            logEntry.setAttribute("data-log-id", String(logId));

            let childNodes = gameLog.childNodes;
            if (childNodes.length > 0) {
                gameLog.insertBefore(logEntry, childNodes[0]);
            } else {
                gameLog.appendChild(logEntry);
            }
        }

        logEntry.innerHTML = '<span class="font-weight-bold">Turn ' + String(logId) + ":</span> "
            + GameUtil.getGameMessage(game);

        let entries = <NodeListOf<HTMLDivElement>>gameLog.querySelectorAll("div[data-log='1']");
        if (entries.length > 100) {
            let lastEntry = entries.item(entries.length - 1);
            lastEntry.parentElement.removeChild(lastEntry);
        }

        this.showTab("game");
    }

    public setHighScores(highScores: HighScores): void {
        let rankElement = this.dom.getDoodad("rank");
        if (null === highScores.rank) {
            Dom.showElement(rankElement.parentElement.parentElement, false);
        } else {
            Dom.showElement(rankElement.parentElement.parentElement, true);
            let ratingElement = this.dom.getDoodad("rating");
            rankElement.innerText = highScores.rank;
            ratingElement.innerText = highScores.playerRating;
        }

        let scores = [...highScores.highScores];

        let highScoresView = this.dom.getHighScoresView();

        for (let i = 1; i <= 10; i++) {
            let key = String(i);
            let row = highScoresView[key];

            let score: HighScore = null;

            if (scores.length > 0) {
                score = scores.shift();
                row["player"].innerText = score.player;
                row["date"].innerText = score.date;
                row["efficiency"].innerText = score.meta.efficiency;
                row["luck"].innerText = score.meta.luck;
                row["maximum-purse"].innerText = score.meta.highPurse;
                row["score"].innerText = String(score.meta.score);
            } else {
                row["player"].innerHTML = "&nbsp;";
                row["date"].innerHTML = "&nbsp;";
                row["efficiency"].innerHTML = "&nbsp;";
                row["luck"].innerHTML = "&nbsp;";
                row["maximum-purse"].innerHTML = "&nbsp;";
                row["score"].innerHTML = "&nbsp;";
            }
        }
    }

    public showOdds(): void {
        if (!this.oddsAreStale) {
            return;
        }

        if (this.oddsAreOutOfOrder) {
            this.sortOddsColumn("cards", SortDirection.asc);
        }

        let hand = this.game.state.hand;
        let nodes = this.game.probability.nodes;
        let oddsView = this.dom.getOddsView();

        let sortIcons = this.dom.getSortIcons();
        for (let i = 0; i < sortIcons.length; i++) {
            let sortIcon = sortIcons.item(i);
            sortIcon.parentElement.removeChild(sortIcon);
        }

        for (let i = 0; i < nodes.length; i++) {
            let oddsKey = String(i + 1);
            let node = nodes[i];
            let cards: number[] = [];

            for (let ii = 0; ii < hand.length; ii++) {
                if (node.draw[ii]) {
                    cards.push(hand[ii]);
                }
            }

            let cardHtml = GameUtil.cardsToHtml(cards, false);

            let frequenciesView = oddsView["frequencies"][oddsKey];
            let percentagesView = oddsView["percentages"][oddsKey];

            frequenciesView["cards"].innerHTML = cardHtml;
            percentagesView["cards"].innerHTML = cardHtml;
            frequenciesView["cards"].setAttribute("data-sort-value", String(i));
            percentagesView["cards"].setAttribute("data-sort-value", String(i));

            frequenciesView["payout"].innerText = node.meanPayout;
            percentagesView["payout"].innerText = node.meanPayout;

            let meanPayout = node.meanPayout.replace(/^\$/g, "");
            frequenciesView["payout"].setAttribute("data-sort-value", meanPayout);
            percentagesView["payout"].setAttribute("data-sort-value", meanPayout);

            Object.keys(node.frequencies).forEach((handType: string) => {
                let frequency = String(node.frequencies[handType]);
                let percentage = String(node.percentages[handType]);

                frequenciesView[handType].innerText = frequency;
                percentagesView[handType].innerText = percentage;

                frequenciesView[handType].setAttribute("data-sort-value", frequency);
                percentagesView[handType].setAttribute("data-sort-value", percentage.replace(/%$/g, ""));
            });
        }

        this.oddsAreStale = false;
    }

    public setCardsHeld(cardsHeld: boolean[]): void {
        for (let i = 0; i < cardsHeld.length; i++) {
            let cardHeld = cardsHeld[i];
            let draw = this.dom.getCardView(i).draw;
            let held = draw.classList.contains("fas");

            if (cardHeld && !held) {
                draw.classList.remove("far");
                draw.classList.add("fas");
            } else if (!cardHeld && held) {
                draw.classList.remove("fas");
                draw.classList.add("far");
            }
        }

        this.cardsHeld = cardsHeld;
    }

    public setSession(session: PlayerSession) {
        if (null === session) {
            return;
        }

        this.dom.getInput("player-name").value = session.player;
    }

    public alert(content: string, level: string): void {
        let html = "<div class=\"alert alert-%1$s alert-dismissible fade show\" role=\"alert\">\n" +
            "  %2$s\n" +
            "  <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">\n" +
            "    <span aria-hidden=\"true\">&times;</span>\n" +
            "  </button>\n" +
            "</div>";

        html = html.replace(/%1\$s/g, level);
        html = html.replace(/%2\$s/g, ViewModel.htmlEncode(content));

        let alertContainer = document.getElementById("app-alert-container");
        alertContainer.innerHTML = html;
    }

    public clearAlerts(): void {
        let alertContainer = document.getElementById("app-alert-container");
        alertContainer.innerHTML = "";
    }

    public setLoaded(loaded: boolean): void {
        let spinner = this.dom.getSpinner();
        let gameView = this.dom.getGameView();

        Dom.showElement(spinner, !loaded);
        Dom.showElement(gameView, loaded);
    }

    public showSpinner(show: boolean): void {
        Dom.showElement(this.dom.getSpinner(), show);
    }

    public holdCard(offset: number): void {
        let held = !this.cardsHeld[offset];
        this.cardsHeld[offset] = held;
        let draw = this.dom.getCardView(offset).draw;

        if (held) {
            draw.classList.remove("far");
            draw.classList.add("fas");
            return;
        }

        draw.classList.remove("fas");
        draw.classList.add("far");
    }

    public getDrawId(): number {
        let id = (this.cardsHeld[0] ? 1 : 0) * 16;
        id += ((this.cardsHeld[1] ? 1 : 0) * 8);
        id += ((this.cardsHeld[2] ? 1 : 0) * 4);
        id += ((this.cardsHeld[3] ? 1 : 0) * 2);
        id += ((this.cardsHeld[4] ? 1 : 0));
        id += 1;
        return id;
    }

    public showOddsView(view: "frequencies" | "percentages"): void {
        let viewToHide = "frequencies" === view ? "percentages" : "frequencies";
        Dom.showElement(this.dom.getTable(viewToHide), false);
        Dom.showElement(this.dom.getTable(view), true);
    }

    private setHand(hand: number[], cardsHeld: number[], cardsDealt: number[], cheated: boolean): void {
        let cardsToSplice: number[] = [];
        let readyToPlay = null === cardsHeld;
        if (!readyToPlay) {
            cardsToSplice = [...cardsDealt];
        }

        hand.forEach((cardId: number, index: number) => {
            let cardView = this.dom.getCardView(index);

            Dom.enableElement(cardView.increase, cheated && readyToPlay);
            Dom.enableElement(cardView.decrease, cheated && readyToPlay);

            let playable = readyToPlay && 0 !== cardId;
            cardView.draw.setAttribute("data-playable", playable ? "1" : "0");
            let held = cardView.draw.classList.contains("fas");

            if (readyToPlay && held) {
                cardView.draw.classList.remove("fas");
                cardView.draw.classList.add("far");
            } else if (!readyToPlay) {
                let toHold = -1 !== cardsHeld.indexOf(cardId);

                if (!toHold) {
                    cardId = cardsToSplice.shift();
                }

                if (toHold && !held) {
                    cardView.draw.classList.remove("far");
                    cardView.draw.classList.add("fas");
                } else if (!toHold && held) {
                    cardView.draw.classList.remove("fas");
                    cardView.draw.classList.add("far");
                }
            }

            this.imageFlyweightFactory.get(cardId).then((image: HTMLImageElement) => {
                cardView.card.innerHTML = "";
                cardView.card.appendChild(image);
            });
        });
    }

    private setMeta(meta: GameMeta): void {
        let purse = null === meta ? "" : meta.purse;
        let score = null === meta ? "" : String(meta.score);
        let efficiency = null === meta ? "" : meta.efficiency;
        let luck = null === meta ? "" : meta.luck;

        this.dom.getDoodad("purse").innerText = purse;
        this.dom.getDoodad("score").innerText = score;
        this.dom.getDoodad("efficiency").innerText = efficiency;
        this.dom.getDoodad("luck").innerText = luck;
    }
}
