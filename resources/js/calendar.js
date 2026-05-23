import { Calendar as FullCalendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';

let calendarInstance = null;

export const Calendar = {
    init() {
        const calendarEl = document.getElementById('calendar');
        if (!calendarEl) {
            return;
        }
        if (calendarInstance) {
            calendarInstance.destroy();
        }

        calendarInstance = new FullCalendar(calendarEl, {
            locale: 'fr',
            plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
            initialView: 'dayGridMonth',
        });

        calendarInstance.render();
    },
};