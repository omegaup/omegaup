import { Highcharts } from '@/third_party/js/highstock.js';
import { omegaup } from '@/js/omegaup/api.js';

declare class OmegaupGraph {

    verdictCounts(renderTo: Element, title: string, stats: omegaup.Stats): Highcharts.Chart;
    normalizeRunCounts(stats: omegaup.Stats): omegaup.Verdict;
    pendingRuns(refreshRate: number, updateStatsFn): Highcharts.Chart;
    getDistribution(stats: omegaup.Stats): Array<number>;
    distributionChart(renderTo: Element, title: string, stats: omegaup.Stats): Highcharts.Chart;
}

declare var oGraph: OmegaupGraph;
export default oGraph;