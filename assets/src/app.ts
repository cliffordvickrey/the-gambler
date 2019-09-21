import {Api} from "./api/Api";
import {Dom} from "./infrastructure/Dom";
import {ViewModel} from "./infrastructure/ViewModel";
import {App} from "./domain/App";
import {ImageFlyweightFactory} from "./infrastructure/ImageFlyweightFactory";
import {Observable} from "./infrastructure/Observable";
import {Observer} from "./infrastructure/Observer";
import {EventBinder} from "./infrastructure/EventBinder";

require("./app.scss");

const api = new Api();
const dom = new Dom();
const imageFlyweightFactory = new ImageFlyweightFactory();
const viewModel = new ViewModel(dom, imageFlyweightFactory);

const observable = new Observable();
const observer = new Observer(api, viewModel);
observable.register(observer);

const eventBinder = new EventBinder(dom, observable, viewModel);

const app = new App(api, eventBinder, viewModel);

window.addEventListener("load", () => app.main());
