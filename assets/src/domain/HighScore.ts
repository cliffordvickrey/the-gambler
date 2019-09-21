import {GameMeta} from "./GameMeta";

export interface HighScore {
    player: string,
    date: string,
    gameId: string,
    meta: GameMeta
}