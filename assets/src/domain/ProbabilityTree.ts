import {ProbabilityNode} from "./ProbabilityNode";

export interface ProbabilityTree {
    highDraw: boolean[],
    nodes: ProbabilityNode[]
}
