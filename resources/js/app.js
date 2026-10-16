import 'aos/dist/aos.css';
import 'lenis/dist/lenis.css';
import Alpine from 'alpinejs';
import { adminReportForm, initSubmitOnce } from './features/admin-report-form';
import { initHomeMotion } from './features/home-motion';
import { reportForm } from './features/report-form';
import { usulanForm } from './features/usulan-form';
import { heroSlider, initHeroBgSlider, initVillageShowcase } from './features/hero-slider';
import { initSiteMotion } from './features/site-motion';
import { initFormValidation } from './features/form-validation';
import { initDialogOtomatis } from './features/dialog-otomatis';
import { initDashboardCharts } from './features/dashboard-charts';

window.Alpine = Alpine;

Alpine.data('reportForm', reportForm);
Alpine.data('adminReportForm', adminReportForm);
Alpine.data('usulanForm', usulanForm);
Alpine.data('heroSlider', heroSlider);
Alpine.start();

initSubmitOnce();
initSiteMotion();
initHomeMotion();
initHeroBgSlider();
initVillageShowcase();
initFormValidation();
initDialogOtomatis();
initDashboardCharts();
