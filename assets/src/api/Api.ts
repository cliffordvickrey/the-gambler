import {RulesInterface} from "../domain/RulesInterface";
import {Network} from "../infrastructure/Network";
import {HttpResponse} from "./HttpResponse";
import {HighScores} from "../domain/HighScores";

export class Api {
    private readonly root: string;

    public constructor(root: string = "") {
        this.root = root;
    }

    public main(): Promise<HttpResponse> {
        return <Promise<HttpResponse>>Network.get(this.root + "/");
    }

    public getRules(): Promise<RulesInterface> {
        return <Promise<RulesInterface>>Network.get(this.root + "/rules");
    }

    public authenticate(playerName: string): Promise<HttpResponse> {
        return <Promise<HttpResponse>>Network.post(this.root + "/authenticate/" + encodeURIComponent(playerName));
    }

    public newGame(): Promise<HttpResponse> {
        return <Promise<HttpResponse>>Network.post(this.root + "/new-game");
    }

    public getHighScores(gameId: string = null): Promise<HighScores> {
        let trailing = null === gameId ? "" : ("/" + encodeURIComponent(gameId));
        return <Promise<HighScores>>Network.get(this.root + "/high-scores" + trailing);
    }

    public bet(gameId: string, amount: number): Promise<HttpResponse> {
        let trailing = "/" + encodeURIComponent(gameId) + "/" + encodeURIComponent(String(amount));
        return <Promise<HttpResponse>>Network.post(this.root + "/bet" + trailing);
    }

    public play(gameId: string, draw: number): Promise<HttpResponse> {
        let trailing = "/" + encodeURIComponent(gameId) + "/" + encodeURIComponent(String(draw));
        return <Promise<HttpResponse>>Network.post(this.root + "/play" + trailing);
    }

    public spliceCard(gameId: string, offset: number, newCard: number): Promise<HttpResponse> {
        let trailing = "/" + encodeURIComponent(gameId) + "/" + encodeURIComponent(String(offset)) + "/" +
            encodeURIComponent(String(newCard));
        return <Promise<HttpResponse>>Network.post(this.root + "/splice" + trailing);
    }

    public cheat(gameId: string): Promise<HttpResponse> {
        let trailing = "/" + encodeURIComponent(gameId);
        return <Promise<HttpResponse>>Network.post(this.root + "/cheat" + trailing);
    }

    public destroy(gameId: string): Promise<HttpResponse> {
        let trailing = "/" + encodeURIComponent(gameId);
        return <Promise<HttpResponse>>Network.post(this.root + "/destroy" + trailing);
    }
}
