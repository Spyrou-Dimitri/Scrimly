import ApexCharts from 'apexcharts';

export const WinrateChart = {
    options: {
        chart: {
            height: 350,
            type: "radialBar",
          },
        
          series: [67],
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
        console.log('test');
        const chart = new ApexCharts(document.getElementById('winrate-chart'), this.options);
        chart.render();
    }
};