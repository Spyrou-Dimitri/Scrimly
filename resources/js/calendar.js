import { Calendar as FullCalendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';

let calendarInstance = null;

export const Calendar = {
    init() {
        const calendarEl = document.getElementById('calendar');
        const events = JSON.parse(calendarEl.dataset.events);

        if (!calendarEl) {
            return;
        }
        if (calendarInstance) {
            calendarInstance.destroy();
        }

        calendarInstance = new FullCalendar(calendarEl, {
            locale: 'fr',
            plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin, listPlugin],
            initialView: this.getInitialView(),
            slotMinTime: '08:00:00',
            slotMaxTime: '23:00:00',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek',
            },
            events: events,
            dateClick: (info) => {
                const wireId = calendarEl.closest('[data-calendar="calendar"]')?.getAttribute('wire:id');
                if (wireId) {
                    console.log(wireId);
                }
                Livewire.find(wireId).call('handleDateClick', info.dateStr);
            },
        });

        calendarInstance.render();

        Livewire.on('calendar-refreshed', ({ events }) => {
            calendarInstance.removeAllEvents();
            calendarInstance.addEventSource(events);
        });

        window.addEventListener('resize', () => {
            let newView = '';

            if (window.innerWidth >= 1024) {
                newView = 'dayGridMonth';
            } else if (window.innerWidth >= 768) {
                newView = 'timeGridWeek';
            } else {
                newView = 'listWeek';
            }
            calendarInstance.changeView(newView);
        });
    },
    getInitialView() {
        if (window.innerWidth >= 1024) {
            return 'dayGridMonth';
        } else if (window.innerWidth >= 768) {
            return 'timeGridWeek';
        } else {
            return 'listWeek';
        }
    },
};
