export class Analysis {
    skill: {
        expectedPayout: string;
        optimalDraw: number[];
        optimalExpectedPayout: string;
        efficiency: string;
    };
    cardsLuck: {
        result: string,
        optimalExpectedPayout: string;
        cardsLuck: string;
        zScore: string;
        percentile: string;
    };
    handDealtLuck: {
        result: string,
        expectedPayout: string;
        actualPayout: string;
        zScore: string;
        percentile: string;
    };
}