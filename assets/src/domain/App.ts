import {Api} from "../api/Api";
import {RulesInterface} from "./RulesInterface";
import {ViewModel} from "../infrastructure/ViewModel";
import {HttpResponse} from "../api/HttpResponse";
import {EventBinder} from "../infrastructure/EventBinder";

export class App {
    private readonly api: Api;
    private readonly eventBinder: EventBinder;
    private readonly viewModel: ViewModel;

    constructor(api: Api, eventBinder: EventBinder, viewModel: ViewModel) {
        this.api = api;
        this.eventBinder = eventBinder;
        this.viewModel = viewModel;
    }

    public main(): void {
        this.api.getRules().then(
            (rules: RulesInterface) => {
                this.viewModel.setRules(rules);

                this.api.main().then(
                    (response: HttpResponse) => {
                        this.viewModel.setSession(response.session);
                        this.viewModel.setGame(response.game);

                        if (null !== response.game) {
                            this.viewModel.showTab("game");
                        }

                        this.viewModel.setLoaded(true);
                        this.eventBinder.bindUiActions();

                        /**
                         * Gently (?) scold the user for using IE
                         */
                        const isIe = window.navigator.userAgent.indexOf("MSIE ") > 0
                            || !!navigator.userAgent.match(/Trident.*rv\:11\./);
                        const hasScolded = "1" === localStorage.getItem("hasScolded");
                        if (isIe && !hasScolded) {
                            this.viewModel.alert(
                                "Are you still using Internet Explorer? Join me by stepping boldly into the present, my"
                                + " friend! Take a quick break from churning your own butter and download a web browser"
                                + " from this century.",
                                "info"
                            );
                            localStorage.setItem("hasScolded", "1");
                        }
                    },
                    (error: Error) => this.viewModel.alert(error.message, "danger")
                );
            },
            (error: Error) => this.viewModel.alert(error.message, "danger")
        );
    }
}
