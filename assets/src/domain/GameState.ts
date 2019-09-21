import {HandType} from "./HandType";

export interface GameState {
    hand: any,
    betAmount: string,
    cardsHeld: number[],
    cardsDealt: number[],
    handType: HandType
}
