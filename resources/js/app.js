import 'aos/dist/aos.css';
import 'lenis/dist/lenis.css';
import Alpine from 'alpinejs';
import { adminReportForm, initSubmitOnce } from './features/admin-report-form';
import { initHomeMotion } from './features/home-motion';
import { reportForm } from './features/report-form';
import { initSiteMotion } from './features/site-motion';
import { initFormValidation } from './features/form-validation';

window.Alpine = Alpine;

Alpine.data('reportForm', reportForm);
Alpine.data('adminReportForm', adminReportForm);
Alpine.start();

initSubmitOnce();
initSiteMotion();
initHomeMotion();
initFormValidation();
