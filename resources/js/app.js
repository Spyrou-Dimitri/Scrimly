import './password-toggle.js';
import { AvatarPreview } from './previewAvatar.js';

function initCalendarIfNeeded() {
    if (!document.getElementById('calendar')) {
        return;
    }
    import('./calendar.js').then(({ Calendar }) => Calendar.init());
}
function initWinrateChartIfNeeded() {
    if (!document.getElementById('winrate-chart')) {
        return;
    }

    import('./charts/winrate.js').then(({ WinrateChart }) => WinrateChart.init());
}

document.addEventListener('livewire:navigated', initCalendarIfNeeded);
document.addEventListener('livewire:navigated', initWinrateChartIfNeeded);

AvatarPreview.init();