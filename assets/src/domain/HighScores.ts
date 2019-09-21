import {HighScore} from "./HighScore";

export interface HighScores {
    highScores: HighScore[],
    rank: string,
    playerRating: string
}