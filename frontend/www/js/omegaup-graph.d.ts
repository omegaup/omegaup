import { Highcharts } from '@/third_party/js/highstock.js';
import { omegaup } from '@/js/omegaup/api.js';

declare namespace ograph {
	export interface OmegaupGraph {
	    verdictCounts(renderTo: Element, title: string, stats: omegaup.Stats): Highcharts.Chart;
	    normalizeRunCounts(stats: omegaup.Stats): omegaup.Verdict;
	    pendingRuns(refreshRate: number, updateStatsFn): Highcharts.Chart;
	    getDistribution(stats: omegaup.Stats): Array<number>;
	    distributionChart(renderTo: Element, title: string, stats: omegaup.Stats): Highcharts.Chart;
	}
}

export var oGraph: ograph.OmegaupGraph;
