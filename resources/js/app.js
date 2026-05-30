import './password-toggle.js';
import { AvatarPreview } from './previewAvatar.js';
import { WinrateChart } from './charts/winrate.js';
/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */
import './echo';

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


