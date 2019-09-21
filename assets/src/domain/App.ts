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
                        this.viewModel.setLoaded(true);
                        this.eventBinder.bindUiActions();
                    },
                    (error: Error) => this.viewModel.alert(error.message, "danger")
                );
            },
            (error: Error) => this.viewModel.alert(error.message, "danger")
        );
    }
}
