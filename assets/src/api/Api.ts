import {RulesInterface} from "../domain/RulesInterface";
import {Network} from "../infrastructure/Network";
import {HttpResponse} from "./HttpResponse";
import {HighScores} from "../domain/HighScores";

export class Api {
    public constructor(root: string = "") {
        this._root = root;
    }

    private _root: string;

    set root(value: string) {
        this._root = value;
    }

    public main(): Promise<HttpResponse> {
        return <Promise<HttpResponse>>Network.get(this._root + "/");
    }

    public getRules(): Promise<RulesInterface> {
        return <Promise<RulesInterface>>Network.get(this._root + "/rules");
    }

    public authenticate(playerName: string): Promise<HttpResponse> {
        return <Promise<HttpResponse>>Network.post(this._root + "/authenticate/" + encodeURIComponent(playerName));
    }

    public newGame(): Promise<HttpResponse> {
        return <Promise<HttpResponse>>Network.post(this._root + "/new-game");
    }

    public getHighScores(gameId: string = null): Promise<HighScores> {
        let trailing = null === gameId ? "" : ("/" + encodeURIComponent(gameId));
        return <Promise<HighScores>>Network.get(this._root + "/high-scores" + trailing);
    }

    public bet(gameId: string, amount: number): Promise<HttpResponse> {
        let trailing = "/" + encodeURIComponent(gameId) + "/" + encodeURIComponent(String(amount));
        return <Promise<HttpResponse>>Network.post(this._root + "/bet" + trailing);
    }

    public play(gameId: string, draw: number): Promise<HttpResponse> {
        let trailing = "/" + encodeURIComponent(gameId) + "/" + encodeURIComponent(String(draw));
        return <Promise<HttpResponse>>Network.post(this._root + "/play" + trailing);
    }

    public spliceCard(gameId: string, offset: number, newCard: number): Promise<HttpResponse> {
        let trailing = "/" + encodeURIComponent(gameId) + "/" + encodeURIComponent(String(offset)) + "/" +
            encodeURIComponent(String(newCard));
        return <Promise<HttpResponse>>Network.post(this._root + "/splice" + trailing);
    }

    public cheat(gameId: string): Promise<HttpResponse> {
        let trailing = "/" + encodeURIComponent(gameId);
        return <Promise<HttpResponse>>Network.post(this._root + "/cheat" + trailing);
    }

    public destroy(gameId: string): Promise<HttpResponse> {
        let trailing = "/" + encodeURIComponent(gameId);
        return <Promise<HttpResponse>>Network.post(this._root + "/destroy" + trailing);
    }
}
