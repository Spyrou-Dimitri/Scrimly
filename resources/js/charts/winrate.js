import ApexCharts from 'apexcharts';

export const WinrateChart = {
    options: {
        chart: {
            height: 350,
            type: "radialBar",
          },
        
          series: [],
          colors: ["#C99C3D"],
          plotOptions: {
            radialBar: {
              hollow: {
                margin: 0,
                size: "70%",
              },
              
              dataLabels: {
                name: {
                  offsetY: -10,
                  color: "#fff",
                  fontSize: "13px"
                },
                value: {
                  color: "#fff",
                  fontSize: "30px",
                  show: true
                }
              }
            }
          },
          fill: {
            type: "gradient",
            gradient: {
              type: "vertical",
              gradientToColors: ["#F4C25D"],
              stops: [0, 100]
            }
          },
          stroke: {
            lineCap: "round"
          },
          labels: ["Taux de victoire Scrim"]
    },
    init() {
        const chart = document.getElementById('winrate-chart');
        const winrate = JSON.parse(chart.dataset.property);
        this.options.series = [winrate];
        const chartInstance = new ApexCharts(chart, this.options);
        chartInstance.render();
    }
};