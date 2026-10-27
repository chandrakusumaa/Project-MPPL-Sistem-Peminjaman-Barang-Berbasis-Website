import './bootstrap';


async function setup() {
 const Chart=document.querySelector('canvas[data-composition],canvas[data-series]')?(await import('chart.js/auto')).default:null;
 const Html5QrcodeScanner=document.getElementById('qr-reader')?(await import('html5-qrcode')).Html5QrcodeScanner:null;
 document.querySelectorAll('canvas[data-composition]').forEach(el=>{const counts=JSON.parse(el.dataset.composition);const current=Chart.getChart(el);if(current){current.data.labels=Object.keys(counts);current.data.datasets[0].data=Object.values(counts);current.update('none');return;}new Chart(el,{type:'doughnut',data:{labels:Object.keys(counts),datasets:[{data:Object.values(counts),backgroundColor:['#16a34a','#dc2626','#f59e0b','#64748b']}]},options:{responsive:true,maintainAspectRatio:false}});});
 document.querySelectorAll('canvas[data-series]').forEach(el=>{const counts=JSON.parse(el.dataset.series);const current=Chart.getChart(el);if(current){current.data.labels=Object.keys(counts);current.data.datasets[0].data=Object.values(counts);current.update('none');return;}new Chart(el,{type:'bar',data:{labels:Object.keys(counts),datasets:[{label:'Peminjaman',data:Object.values(counts),backgroundColor:'#dc2626'}]},options:{responsive:true,maintainAspectRatio:false,scales:{y:{beginAtZero:true,ticks:{precision:0}}}}});});
 const scan=document.getElementById('qr-reader');if(scan && !scan.dataset.ready){scan.dataset.ready='true';const scanner=new Html5QrcodeScanner('qr-reader',{fps:10,qrbox:220},false);scanner.render(text=>{try{const url=new URL(text);if(url.origin!==location.origin||!url.pathname.startsWith('/o/'))throw Error();scanner.clear();location.assign(url.href);}catch{document.getElementById('qr-error').textContent='QR / Aset Invalid';}},()=>{});}
}
document.addEventListener('DOMContentLoaded',setup);
document.addEventListener('livewire:navigated',setup);

let refreshTimer;
function watchUpdates(){window.Livewire.hook('morph.updated',()=>{clearTimeout(refreshTimer);refreshTimer=setTimeout(setup,0);});}
if(window.Livewire)watchUpdates();else document.addEventListener('livewire:init',watchUpdates);
