export interface ProbabilityNode {
    draw: number[],
    frequencies: {[key: string]: number},
    percentages: {[key: string]: number},
    meanPayout: string
}
