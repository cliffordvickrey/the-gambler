import {ObserverInterface} from "./ObserverInterface";
import {EventType} from "./EventType";

export class Observable implements ObserverInterface {
    private observers: ObserverInterface[] = [];

    public register(observer: ObserverInterface): void
    {
        this.observers.push(observer);
    }

    public notify(event: EventType, params?: any): void {
        if ("undefined" === typeof params) {
            params = null;
        }

        this.observers.forEach((observer: ObserverInterface) => observer.notify(event, params));
    }

    public isBusy(): boolean {
        let busy = false;
        this.observers.forEach((observer: ObserverInterface) => {
            if (observer.isBusy()) {
                busy = true;
            }
        });
        return busy;
    }
}
