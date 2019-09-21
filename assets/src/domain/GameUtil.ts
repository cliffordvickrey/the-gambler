import {Game} from "./Game";
import {HandType} from "./HandType";

export class GameUtil {
    public static getGameMessage(game: Game): string {
        if (null === game.state.hand) {
            return "";
        }

        let message = "Bet " + game.state.betAmount + ".";
        message += " Dealt " + this.cardsToHtml(game.state.hand) + ".";

        if (null === game.state.cardsHeld) {
            return message;
        }

        message += " Held " + GameUtil.cardsToHtml(game.state.cardsHeld) + ".";
        message += " Dealt " + GameUtil.cardsToHtml(game.state.cardsDealt) + ".";
        message += " Resulting hand is " + GameUtil.inflectHandType(game.state.handType);
        message += " [payout: " + game.meta.lastPayout + "].";
        return message;
    }

    public static cardToHtml(card: number): string {
        let rank = card % 13;
        if (0 === rank) {
            rank = 13;
        }
        let suit = Math.floor((card - 1) / 13) + 1;

        let rankHtml: string;
        switch (rank) {
            case 1:
                rankHtml = "A";
                break;
            case 11:
                rankHtml = "J";
                break;
            case 12:
                rankHtml = "Q";
                break;
            case 13:
                rankHtml = "K";
                break;
            default:
                rankHtml = String(rank);
                break;
        }

        let cssClass: string;
        let suitHtml: string;

        switch (suit) {
            case 1:
                cssClass = "app-suit-black";
                suitHtml = "&clubsuit;";
                break;
            case 2:
                cssClass = "app-suit-red";
                suitHtml = "&diamondsuit;";
                break;
            case 3:
                cssClass = "app-suit-red";
                suitHtml = "&heartsuit;";
                break;
            case 4:
                cssClass = "app-suit-black";
                suitHtml = "&spadesuit;";
                break;
        }

        return '<span class="' + cssClass + '">' + rankHtml + suitHtml + '</span>';
    }

    public static cardsToHtml(cards: number[], oxfordCommas: boolean = true): string {
        if (0 === cards.length) {
            return "nothing";
        }

        let formattedCards = cards.map(card => GameUtil.cardToHtml(card));

        if (!oxfordCommas) {
            return formattedCards.join(" ");
        }

        if (1 === formattedCards.length) {
            return formattedCards.pop();
        }

        if (2 === formattedCards.length) {
            return formattedCards[0] + " and " + formattedCards[1];
        }

        let lastCard = formattedCards.pop();
        let cardHtml = formattedCards.join(", ");
        return cardHtml + ", and " + lastCard;
    }

    private static inflectHandType(handType: HandType): string {
        switch (handType) {
            case HandType.nothing:
                return "nothing";
            case HandType.jacksOrBetter:
                return "Jacks or better";
            case HandType.twoPair:
                return "a two pair";
            case HandType.threeOfAKind:
                return "a three of a kind";
            case HandType.straight:
                return "a straight";
            case HandType.flush:
                return "a flush";
            case HandType.fullHouse:
                return "a full house";
            case HandType.fourOfAKind:
                return "a four of a kind";
            case HandType.straightFlush:
                return "a straight flush (ooh ...)";
            case HandType.royalFlush:
                return "a royal flush (woah!)";
        }
    }
}