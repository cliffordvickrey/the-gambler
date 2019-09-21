import Card0 from "../img/card0.png";
import Card1 from "../img/card1.png";
import Card2 from "../img/card2.png";
import Card3 from "../img/card3.png";
import Card4 from "../img/card4.png";
import Card5 from "../img/card5.png";
import Card6 from "../img/card6.png";
import Card7 from "../img/card7.png";
import Card8 from "../img/card8.png";
import Card9 from "../img/card9.png";
import Card10 from "../img/card10.png";
import Card11 from "../img/card11.png";
import Card12 from "../img/card12.png";
import Card13 from "../img/card13.png";
import Card14 from "../img/card14.png";
import Card15 from "../img/card15.png";
import Card16 from "../img/card16.png";
import Card17 from "../img/card17.png";
import Card18 from "../img/card18.png";
import Card19 from "../img/card19.png";
import Card20 from "../img/card20.png";
import Card21 from "../img/card21.png";
import Card22 from "../img/card22.png";
import Card23 from "../img/card23.png";
import Card24 from "../img/card24.png";
import Card25 from "../img/card25.png";
import Card26 from "../img/card26.png";
import Card27 from "../img/card27.png";
import Card28 from "../img/card28.png";
import Card29 from "../img/card29.png";
import Card30 from "../img/card30.png";
import Card31 from "../img/card31.png";
import Card32 from "../img/card32.png";
import Card33 from "../img/card33.png";
import Card34 from "../img/card34.png";
import Card35 from "../img/card35.png";
import Card36 from "../img/card36.png";
import Card37 from "../img/card37.png";
import Card38 from "../img/card38.png";
import Card39 from "../img/card39.png";
import Card40 from "../img/card40.png";
import Card41 from "../img/card41.png";
import Card42 from "../img/card42.png";
import Card43 from "../img/card43.png";
import Card44 from "../img/card44.png";
import Card45 from "../img/card45.png";
import Card46 from "../img/card46.png";
import Card47 from "../img/card47.png";
import Card48 from "../img/card48.png";
import Card49 from "../img/card49.png";
import Card50 from "../img/card50.png";
import Card51 from "../img/card51.png";
import Card52 from "../img/card52.png";

export class ImageFlyweightFactory {
    private images: { [key: string]: HTMLImageElement } = {};

    public get(cardId: number): Promise<HTMLImageElement> {
        let key = "card" + String(cardId);

        return new Promise<HTMLImageElement>((resolve, reject) => {
            if (key in this.images) {
                resolve(<HTMLImageElement>this.images[key].cloneNode(true));
            }

            let image = new Image();
            image.addEventListener("load", () => {
                this.images[key] = image;
                resolve(<HTMLImageElement>image.cloneNode(true))
            });
            image.addEventListener("error", () => reject("Failed to load card with ID #" + String(cardId)));
            image.className = "rounded";

            switch (cardId) {
                case 0:
                    image.src = Card0;
                    break;
                case 1:
                    image.src = Card1;
                    break;
                case 2:
                    image.src = Card2;
                    break;
                case 3:
                    image.src = Card3;
                    break;
                case 4:
                    image.src = Card4;
                    break;
                case 5:
                    image.src = Card5;
                    break;
                case 6:
                    image.src = Card6;
                    break;
                case 7:
                    image.src = Card7;
                    break;
                case 8:
                    image.src = Card8;
                    break;
                case 9:
                    image.src = Card9;
                    break;
                case 10:
                    image.src = Card10;
                    break;
                case 11:
                    image.src = Card11;
                    break;
                case 12:
                    image.src = Card12;
                    break;
                case 13:
                    image.src = Card13;
                    break;
                case 14:
                    image.src = Card14;
                    break;
                case 15:
                    image.src = Card15;
                    break;
                case 16:
                    image.src = Card16;
                    break;
                case 17:
                    image.src = Card17;
                    break;
                case 18:
                    image.src = Card18;
                    break;
                case 19:
                    image.src = Card19;
                    break;
                case 20:
                    image.src = Card20;
                    break;
                case 21:
                    image.src = Card21;
                    break;
                case 22:
                    image.src = Card22;
                    break;
                case 23:
                    image.src = Card23;
                    break;
                case 24:
                    image.src = Card24;
                    break;
                case 25:
                    image.src = Card25;
                    break;
                case 26:
                    image.src = Card26;
                    break;
                case 27:
                    image.src = Card27;
                    break;
                case 28:
                    image.src = Card28;
                    break;
                case 29:
                    image.src = Card29;
                    break;
                case 30:
                    image.src = Card30;
                    break;
                case 31:
                    image.src = Card31;
                    break;
                case 32:
                    image.src = Card32;
                    break;
                case 33:
                    image.src = Card33;
                    break;
                case 34:
                    image.src = Card34;
                    break;
                case 35:
                    image.src = Card35;
                    break;
                case 36:
                    image.src = Card36;
                    break;
                case 37:
                    image.src = Card37;
                    break;
                case 38:
                    image.src = Card38;
                    break;
                case 39:
                    image.src = Card39;
                    break;
                case 40:
                    image.src = Card40;
                    break;
                case 41:
                    image.src = Card41;
                    break;
                case 42:
                    image.src = Card42;
                    break;
                case 43:
                    image.src = Card43;
                    break;
                case 44:
                    image.src = Card44;
                    break;
                case 45:
                    image.src = Card45;
                    break;
                case 46:
                    image.src = Card46;
                    break;
                case 47:
                    image.src = Card47;
                    break;
                case 48:
                    image.src = Card48;
                    break;
                case 49:
                    image.src = Card49;
                    break;
                case 50:
                    image.src = Card50;
                    break;
                case 51:
                    image.src = Card51;
                    break;
                case 52:
                    image.src = Card52;
                    break;
                default:
                    reject("Invalid card ID #" + String(cardId));
                    break;
            }
        });
    }
}
