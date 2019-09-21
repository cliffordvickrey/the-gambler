import {CardView} from "./CardView";
import {OddsView} from "./OddsView";
import {TableView} from "./TableView";

export class Dom {
    private buttons: { [key: string]: HTMLButtonElement } = {};
    private cardViews: CardView[] = null;
    private confirmationModals: { [key: string]: HTMLDivElement } = {};
    private doodads: { [key: string]: NodeListOf<HTMLDivElement | HTMLSpanElement> } = {};
    private inputs: { [key: string]: HTMLInputElement } = {};
    private gameLog: HTMLDivElement = null;
    private gameView: HTMLDivElement = null;
    private highScoresView: TableView = null;
    private oddsView: OddsView = null;
    private selects: { [key: string]: HTMLSelectElement } = {};
    private spinner: HTMLDivElement = null;
    private tabs: { [key: string]: HTMLAnchorElement } = null;
    private tables: { [key: string]: HTMLTableElement } = {};

    public static enableElement(element: HTMLElement, enabled: boolean): void {
        let tagName = element.tagName.toLowerCase();

        if ("input" !== tagName && "button" !== tagName) {
            let disabled = element.classList.contains("disabled");

            if (enabled && disabled) {
                element.classList.remove("disabled");
                return;
            }

            if (!enabled && !disabled) {
                element.classList.add("disabled");
            }

            return;
        }

        (<HTMLInputElement>element).disabled = !enabled;
    }

    public static showElement(element: HTMLElement, show: boolean): void {
        let hidden = element.classList.contains("d-none");

        if (show && hidden) {
            element.classList.remove("d-none");
            return;
        }

        if (!show && !hidden) {
            element.classList.add("d-none");
        }

        return;
    }

    public getSortIcons(): HTMLCollection
    {
        return document.getElementsByClassName("app-sort-icon");
    }

    public getOddsView(): OddsView {
        if (null === this.oddsView) {
            let tableNames: string[] = ["frequencies", "percentages"];
            let oddsView: OddsView = {};

            tableNames.forEach(tableName => {
                let table = this.getTable(tableName);
                let rows = <NodeListOf<HTMLTableRowElement>>table.querySelectorAll("tr");

                let rowData: {[key: string]: {[key:string]: HTMLTableDataCellElement|HTMLTableHeaderCellElement}} = {};

                for (let i = 0; i < rows.length; i++) {
                    let row = rows.item(i);
                    let cells: NodeListOf<HTMLTableDataCellElement|HTMLTableHeaderCellElement>;
                    if (0 === i) {
                        cells = <NodeListOf<HTMLTableHeaderCellElement>>row.querySelectorAll("th");
                    } else {
                        cells = <NodeListOf<HTMLTableDataCellElement>>row.querySelectorAll("td");
                    }
                    let cellData: {[key: string]: HTMLTableDataCellElement} = {};
                    for (let ii = 0; ii < cells.length; ii++) {
                        let cell = cells.item(ii);
                        cellData[cell.getAttribute("data-odds-cell")] = cell;
                    }
                    rowData[String(i)] = cellData;
                }

                oddsView[tableName] = rowData;
            });

            this.oddsView = oddsView;
        }

        return this.oddsView;
    }

    public getHighScoresView(): TableView {
        if (null === this.highScoresView) {
            let table = this.getTable("high-scores");
            let rows = <NodeListOf<HTMLTableRowElement>>table.querySelectorAll("tr");

            let highScoresView: TableView = {};

            for (let i = 1; i < rows.length; i++) {
                let row = rows.item(i);
                let cells = <NodeListOf<HTMLTableDataCellElement>>row.querySelectorAll("td");
                let cellData: {[key: string]: HTMLTableDataCellElement} = {};
                for (let ii = 0; ii < cells.length; ii++) {
                    let cell = cells.item(ii);
                    cellData[cell.getAttribute("data-high-scores-cell")] = cell;
                }
                highScoresView[String(i)] = cellData;
            }

            this.highScoresView = highScoresView;
        }

        return this.highScoresView;
    }


    public getGameLog(): HTMLDivElement {
        if (null === this.gameLog) {
            this.gameLog = <HTMLDivElement>document.getElementById("app-game-log");
        }
        return this.gameLog;
    }

    public getSpinner(): HTMLDivElement {
        if (null === this.spinner) {
            this.spinner = <HTMLDivElement>document.getElementById("app-spinner");
        }
        return this.spinner;
    }

    public getInput(inputName: string): HTMLInputElement {
        if (inputName in this.inputs) {
            return this.inputs[inputName];
        }

        this.inputs[inputName] = <HTMLInputElement>document.querySelector("input[data-input='" + inputName + "']");
        return this.inputs[inputName];
    }

    public getSelect(selectName: string): HTMLSelectElement {
        if (selectName in this.selects) {
            return this.selects[selectName];
        }

        this.selects[selectName] = <HTMLSelectElement>document.querySelector("select[data-select='" + selectName + "']");
        return this.selects[selectName];
    }

    public getGameView(): HTMLDivElement {
        if (null === this.gameView) {
            this.gameView = <HTMLDivElement>document.getElementById("app-game-view");
        }
        return this.gameView;
    }

    public getTable(tableName: string): HTMLTableElement {
        if (tableName in this.tables) {
            return this.tables[tableName];
        }

        this.tables[tableName] = <HTMLTableElement>document.querySelector("table[data-table='" + tableName + "']");
        return this.tables[tableName];
    }

    public getConfirmationModal(name: string): HTMLDivElement {
        if (name in this.confirmationModals) {
            return this.confirmationModals[name];
        }

        this.confirmationModals[name] = <HTMLDivElement>document.querySelector("div[data-confirmation='" + name + "']");
        return this.confirmationModals[name];
    }

    public getDoodads(doodadName: string): NodeListOf<HTMLDivElement | HTMLSpanElement> {
        if (doodadName in this.doodads) {
            return this.doodads[doodadName];
        }

        this.doodads[doodadName] = <NodeListOf<HTMLDivElement | HTMLSpanElement>>document.querySelectorAll(
            "[data-doodad='" + doodadName + "']"
        );
        return this.doodads[doodadName];
    }

    public getDoodad(doodadName: string): HTMLDivElement | HTMLSpanElement {
        let doodads = this.getDoodads(doodadName);
        return doodads.item(0);
    }

    public getButton(buttonName: string): HTMLButtonElement {
        if (buttonName in this.buttons) {
            return this.buttons[buttonName];
        }

        this.buttons[buttonName] = <HTMLButtonElement>document.querySelector("button[data-button='" + buttonName + "']");
        return this.buttons[buttonName];
    }

    public getRulesPayoutCell(handType: string): HTMLTableDataCellElement {
        return this.getTable("rules").querySelector("td[data-rules-payout='" + handType + "']");
    }

    public getCardView(offset: number): CardView {
        if (null === this.cardViews) {
            this.cardViews = [];
            for (let i = 0; i < 5; i++) {
                this.cardViews.push({
                    card: <HTMLDivElement>document.querySelector("div[data-card-offset='" + String(i) + "']"),
                    draw: <HTMLSpanElement>document.querySelector("span[data-draw-offset='" + String(i) + "']"),
                    increase: <HTMLButtonElement>document.querySelector("button[data-increase-offset='" + String(i) + "']"),
                    decrease: <HTMLButtonElement>document.querySelector("button[data-decrease-offset='" + String(i) + "']")
                });
            }
        }

        return this.cardViews[offset];
    }

    public getTab(tabName: string): HTMLAnchorElement {
        if (null === this.tabs) {
            let tabObject: { [key: string]: HTMLAnchorElement } = {};
            let tabContainer = <HTMLUListElement>document.getElementById("app-tabs");
            let tabs = <NodeListOf<HTMLAnchorElement>>tabContainer.querySelectorAll("a.nav-link");

            for (let i = 0; i < tabs.length; i++) {
                let tab: HTMLAnchorElement = tabs[i];
                let id = tab.id;
                let tabName = id.replace(/-tab$/g, "");
                tabObject[tabName] = tab;
            }

            this.tabs = tabObject;
        }

        return this.tabs[tabName];
    }

    public enableButton(buttonName: string, enabled: boolean = true): void {
        Dom.enableElement(this.getButton(buttonName), enabled);
    }

    public showButton(buttonName: string, show: boolean = true): void {
        Dom.showElement(this.getButton(buttonName), show);
    }

    public enableTab(tabName: string, enabled: boolean = true): void {
        Dom.enableElement(this.getTab(tabName), enabled);
    }
}
