export class Analysis {
    skill: {
        expectedPayout: string;
        optimalDraw: number[];
        optimalExpectedPayout: string;
        efficiency: string;
    };
    cardsLuck: {
        optimalExpectedPayout: string;
        cardsLuck: string;
        zScore: string;
        percentile: string;
    };
    handDealtLuck: {
        expectedPayout: string;
        actualPayout: string;
        zScore: string;
        percentile: string;
    };
}