import {PlayerSession} from "../domain/PlayerSession";
import {Game} from "../domain/Game";

export interface HttpResponse {
    game: Game,
    session: PlayerSession
}
