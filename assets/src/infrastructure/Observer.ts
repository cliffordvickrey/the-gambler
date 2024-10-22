import {ObserverInterface} from "./ObserverInterface";
import {ViewModel} from "./ViewModel";
import {EventType} from "./EventType";
import {Api} from "../api/Api";
import {HttpResponse} from "../api/HttpResponse";
import {HighScores} from "../domain/HighScores";

export class Observer implements ObserverInterface {
    private busy: boolean = false;
    private autoPilot: boolean = false;
    private resigning: boolean = false;
    private readonly api: Api;
    private readonly viewModel: ViewModel;

    constructor(api: Api, viewModel: ViewModel) {
        this.api = api;
        this.viewModel = viewModel;
    }

    public notify(event: EventType, params?: any): void {
        if (
            (this.busy && !this.autoPilot) ||
            (this.resigning && event === EventType.tabClick && null !== params && "high-scores" === params.tab)
        ) {
            return;
        }

        if (!(event === EventType.autopilot
            || event === EventType.hint
            || event === EventType.bet
            || event === EventType.draw)) {
            this.setAutopilot(false);
        }

        this.busy = true;
        this.viewModel.showSpinner(true);

        this.dispatch(event, params).then(
            () => {
                this.viewModel.showSpinner(false);
                this.busy = false;
                this.viewModel.clearAlerts();
            },
            (err: Error) => {
                this.viewModel.showSpinner(false);
                this.busy = false;
                this.viewModel.alert(err.message, "danger");
                this.setAutopilot(false);
            }
        );
    }

    public isBusy(): boolean {
        return this.busy;
    }

    private setAutopilot(autoPilot: boolean): void {
        this.autoPilot = autoPilot;
        this.viewModel.setAutopilot(autoPilot);

        if (!autoPilot) {
            return;
        }

        this.viewModel.resetDefaultBetAmount();

        new Promise((resolve, reject) => {
            let game = this.viewModel.game;
            let doHint = null !== game.state.hand && null === game.state.handType;

            if (doHint) {
                let hintParams = {cardsHeld: [...this.viewModel.game.probability.highDraw]};
                this.dispatch(EventType.hint, hintParams).then(
                    () => {
                        if (!this.autoPilot) {
                            resolve();
                            return;
                        }

                        setTimeout(() => {
                            if (!this.autoPilot) {
                                resolve();
                                return;
                            }

                            let drawParams = {gameId: game.gameId, draw: this.viewModel.getDrawId()};
                            this.dispatch(EventType.draw, drawParams).then(
                                () => resolve(),
                                (err: Error) => reject(err)
                            );
                        }, 1000);
                    },
                    (err: Error) => reject(err)
                );
            } else {
                let betParams = {gameId: game.gameId, amount: this.viewModel.defaultBetAmount};
                this.dispatch(EventType.bet, betParams).then(
                    () => resolve(),
                    (err: Error) => reject(err)
                );
            }
        }).then(
            () => setTimeout(() => {
                this.notify(EventType.autopilot, {autopilot: this.autoPilot})
            }, 1500),
            (err: Error) => {
                this.viewModel.showSpinner(false);
                this.busy = false;
                this.viewModel.alert(err.message, "danger");
                this.setAutopilot(false);
            }
        );
    }

    private dispatch(event: EventType, params: any): Promise<void> {
        return new Promise<void>((resolve, reject) => {
            switch (event) {
                case EventType.authenticate:
                    this.authenticate(params.playerName).then(
                        () => resolve(),
                        (err) => reject(err)
                    );
                    return;
                case EventType.autopilot:
                    this.setAutopilot(null === params ? !this.autoPilot : params.autopilot);
                    resolve();
                    return;
                case EventType.bet:
                    this.bet(params.gameId, params.amount).then(
                        () => resolve(),
                        (err) => reject(err)
                    );
                    return;
                case EventType.cheat:
                    this.cheat(params.gameId).then(
                        () => resolve(),
                        (err) => reject(err)
                    );
                    return;
                case EventType.draw:
                    this.draw(params.gameId, params.draw).then(
                        () => resolve(),
                        (err) => reject(err)
                    );
                    return;
                case EventType.hint:
                    this.viewModel.setCardsHeld(params.cardsHeld);
                    resolve();
                    break;
                case EventType.holdCard:
                    this.viewModel.holdCard(params.offset);
                    resolve();
                    return;
                case EventType.oddsSelect:
                    this.viewModel.showOddsView(params.selected);
                    resolve();
                    return;
                case EventType.oddsSort:
                    this.viewModel.sortOddsColumn(params.column, params.direction);
                    resolve();
                    return;
                case EventType.resign:
                    this.resign(params.gameId).then(
                        () => resolve(),
                        (err) => reject(err)
                    );
                    return;
                case EventType.spliceCard:
                    this.spliceCard(params.gameId, params.offset, params.card).then(
                        () => resolve(),
                        (err) => reject(err)
                    );
                    return;
                case EventType.tabClick:
                    switch (params.tab) {
                        case "odds":
                            this.viewModel.showOdds();
                            resolve();
                            break;
                        case "high-scores":
                            this.highScores(params.gameId).then(
                                () => resolve(),
                                (err) => reject(err)
                            );
                            break;
                        default:
                            resolve();
                    }
                    return;
                default:
                    resolve();
                    break;
            }
        });
    }

    private bet(gameId: string, amount: number): Promise<void> {
        return new Promise<void>((resolve, reject) => {
            this.api.bet(gameId, amount).then(
                (httpResponse: HttpResponse) => {
                    this.viewModel.setGame(httpResponse.game);
                    resolve();
                },
                (err) => reject(err)
            );
        });
    };

    private spliceCard(gameId: string, offset: number, card: number): Promise<void> {
        return new Promise<void>((resolve, reject) => {
            this.api.spliceCard(gameId, offset, card).then(
                (httpResponse: HttpResponse) => {
                    this.viewModel.setGame(httpResponse.game, true);
                    resolve();
                },
                (err) => reject(err)
            );
        });
    };

    private cheat(gameId: string): Promise<void> {
        return new Promise<void>((resolve, reject) => {
            this.api.cheat(gameId).then(
                (httpResponse: HttpResponse) => {
                    this.viewModel.setGame(httpResponse.game);
                    resolve();
                },
                (err) => reject(err)
            );
        });
    };

    private highScores(gameId: string): Promise<void> {
        return new Promise<void>((resolve, reject) => {
            this.api.getHighScores(gameId).then(
                (highScores: HighScores) => {
                    this.viewModel.setHighScores(highScores);
                    resolve();
                },
                (err) => reject(err)
            );
        });
    };

    private resign(gameId: string): Promise<void> {
        return new Promise<void>((resolve, reject) => {
            this.resigning = true;

            this.dispatch(EventType.tabClick, {tab: "high-scores", gameId: this.viewModel.game.gameId}).then(
                () => {
                    this.viewModel.showTab("high-scores");

                    this.api.destroy(gameId).then(
                        (httpResponse: HttpResponse) => {
                            this.resigning = false;
                            this.viewModel.setGame(httpResponse.game);
                            resolve();
                        },
                        (err) => {
                            this.resigning = false;
                            reject(err)
                        }
                    );
                },
                (err) => {
                    this.resigning = false;
                    reject(err)
                }
            );
        });
    };

    private draw(gameId: string, draw: number): Promise<void> {
        return new Promise<void>((resolve, reject) => {
            this.api.play(gameId, draw).then(
                (httpResponse: HttpResponse) => {
                    this.viewModel.setGame(httpResponse.game);
                    resolve();
                },
                (err) => reject(err)
            );
        });
    };

    private authenticate(playerName: string): Promise<void> {
        return new Promise<void>((resolve, reject) => {
            this.api.authenticate(playerName).then(
                (httpResponse: HttpResponse) => {
                    this.viewModel.setSession(httpResponse.session);
                    this.api.newGame().then(
                        (httpResponse) => {
                            this.viewModel.setGame(httpResponse.game);
                            this.viewModel.showTab("game");
                            resolve();
                        },
                        (err) => reject(err)
                    );
                },
                (err) => reject(err)
            );
        });
    }
}