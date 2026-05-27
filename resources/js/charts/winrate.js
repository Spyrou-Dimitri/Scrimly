import ApexCharts from 'apexcharts';
import { settings } from '../settings';

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

let refreshFrame = null;

export const WinrateChart = {
    chart: null,
    isSetup: false,

    getElement() {
        return document.getElementById(settings.chartsElementId);
    },

    readData(element) {
        const data = JSON.parse(element.dataset.property);

        return {
            value: data.value ?? 0,
            label: data.label ?? '',
        };
    },

    destroy() {
        if (this.chart) {
            this.chart.destroy();
            this.chart = null;
        }
    },

    clearStaleMarkup(element) {
        if (! this.chart && element.querySelector('.apexcharts-canvas')) {
            element.replaceChildren();
        }
    },

    render(element, value, label) {
        this.destroy();
        this.clearStaleMarkup(element);

        this.chart = new ApexCharts(element, {
            ...chartOptions,
            series: [value],
            labels: [label],
        });
        this.chart.render();
    },

    refresh() {
        const element = this.getElement();

        if (! element) {
            this.destroy();

            return;
        }

        const { value, label } = this.readData(element);
        const hasRenderedChart = element.querySelector('.apexcharts-canvas') !== null;

        if (this.chart && hasRenderedChart) {
            this.chart.updateSeries([value]);
            this.chart.updateOptions({ labels: [label] });

            return;
        }

        this.render(element, value, label);
    },

    scheduleRefresh() {
        if (refreshFrame !== null) {
            cancelAnimationFrame(refreshFrame);
        }

        refreshFrame = requestAnimationFrame(() => {
            refreshFrame = null;
            this.refresh();
        });
    },

    registerLivewireHooks() {
        if (this.isSetup) {
            return;
        }

        this.isSetup = true;

        document.addEventListener('livewire:navigated', () => this.scheduleRefresh());

        document.addEventListener('livewire:init', () => {
            this.scheduleRefresh();

            Livewire.hook('morphed', () => {
                if (this.getElement()) {
                    this.scheduleRefresh();
                }
            });

            Livewire.hook('morph.removed', ({ el }) => {
                if (el.id === settings.chartsElementId) {
                    this.destroy();
                }
            });
        });
    },
};
