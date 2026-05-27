import './password-toggle.js';
import { AvatarPreview } from './previewAvatar.js';
import { WinrateChart } from './charts/winrate.js';

function initCalendarIfNeeded() {
    if (! document.getElementById('calendar')) {
        return;
    }
    import('./calendar.js').then(({ Calendar }) => Calendar.init());
}

document.addEventListener('livewire:navigated', initCalendarIfNeeded);

WinrateChart.registerLivewireHooks();
WinrateChart.scheduleRefresh();

AvatarPreview.init();
