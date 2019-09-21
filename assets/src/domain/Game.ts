import {GameMeta} from "./GameMeta";
import {GameState} from "./GameState";
import {ProbabilityTree} from "./ProbabilityTree";

export interface Game {
    gameId: string,
    meta: GameMeta,
    state: GameState,
    probability: ProbabilityTree
}
