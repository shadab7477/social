// -------------------------------------------------------------
// SOCIALPILOTX — MASTER JAVASCRIPT ENTRYPOINT
// -------------------------------------------------------------

import './bootstrap';

// Import Bootstrap 5 bundle (includes Popper)
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// Import Chart.js for Analytics
import Chart from 'chart.js/auto';
window.Chart = Chart;

// Import FullCalendar
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';

window.FullCalendar = {
    Calendar,
    dayGridPlugin,
    timeGridPlugin,
    listPlugin,
    interactionPlugin,
};

// Dispatch global ready event
document.addEventListener('DOMContentLoaded', () => {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
});
