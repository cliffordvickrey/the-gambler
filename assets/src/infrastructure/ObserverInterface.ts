import {EventType} from "./EventType";

export interface ObserverInterface {
    notify (event: EventType, params?: any): void

    isBusy(): boolean;
}

