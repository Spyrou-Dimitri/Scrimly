import { AvatarPreview } from './previewAvatar.js';
import './charts/winrate.js';
import { Password } from './password.js';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */
import './echo';
import './chat.js';
import './presence.js';

function initCalendarIfNeeded() {
    if (! document.getElementById('calendar')) {
        return;
    }
    import('./calendar.js').then(({ Calendar }) => Calendar.init());
}
document.addEventListener('livewire:navigated', initCalendarIfNeeded);

AvatarPreview.init();
Password.init();


