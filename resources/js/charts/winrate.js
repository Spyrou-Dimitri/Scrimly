import ApexCharts from 'apexcharts';

export const WinrateChart = {
    options: {
        chart: {
            type: 'radialBar',
        },
        series: [75],
        label: ['progress']
        
    },
    init() {
        console.log('test');
        const chart = new ApexCharts(document.getElementById('winrate-chart'), this.options);
        chart.render();
    }
};