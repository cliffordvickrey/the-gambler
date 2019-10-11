import {GameMeta} from "./GameMeta";
import {GameState} from "./GameState";
import {ProbabilityTree} from "./ProbabilityTree";
import {Analysis} from "./Analysis";

export interface Game {
    gameId: string;
    meta: GameMeta;
    state: GameState;
    probability: ProbabilityTree;
    analysis: Analysis;
}
