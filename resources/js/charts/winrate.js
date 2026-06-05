import ApexCharts from 'apexcharts';

const chartOptions = {
    chart: {
        height: 350,
        type: 'radialBar',
    },
    colors: ['#C99C3D'],
    plotOptions: {
        radialBar: {
            hollow: {
                margin: 0,
                size: '70%',
            },
            dataLabels: {
                name: {
                    offsetY: -10,
                    color: '#fff',
                    fontSize: '13px',
                },
                value: {
                    color: '#fff',
                    fontSize: '30px',
                    show: true,
                },
            },
        },
    },
    fill: {
        type: 'gradient',
        gradient: {
            type: 'vertical',
            gradientToColors: ['#F4C25D'],
            stops: [0, 100],
        },
    },
    stroke: {
        lineCap: 'round',
    },
};

document.addEventListener('alpine:init', () => {
    window.Alpine.data('winrateChart', () => ({
        chart: null,
        observer: null,

        init() {
            const { value, label } = this.readData();

            this.chart = new ApexCharts(this.$refs.chart, {
                ...chartOptions,
                series: [value],
                labels: [label],
            });
            this.chart.render();

            this.observer = new MutationObserver(() => this.update());
            this.observer.observe(this.$el, {
                attributes: true,
                attributeFilter: ['data-property'],
            });
        },

        readData() {
            const data = JSON.parse(this.$el.dataset.property);

            return {
                value: data.value ?? 0,
                label: data.label ?? '',
            };
        },

        update() {
            const { value, label } = this.readData();

            this.chart.updateSeries([value]);
            this.chart.updateOptions({ labels: [label] });
        },

        destroy() {
            this.observer?.disconnect();
            this.chart?.destroy();
            this.chart = null;
        },
    }));
});
