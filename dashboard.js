// DATE
document.getElementById("date").innerText = new Date().toDateString();

// CHART
const ctx = document.getElementById('chart');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
        datasets: [{
            label: 'Security Score',
            data: [70,75,80,78,85,88,90],
            borderColor: '#38bdf8',
            fill: false,
            tension:0.4
        }]
    }
});