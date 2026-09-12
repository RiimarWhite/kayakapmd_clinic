import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import inject from '@rollup/plugin-inject';

export default defineConfig({
    base: '',//'/EConsultationv2/public/build/',
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                // Admin pages
                'resources/js/pages/admin/dashboard.js',
                'resources/js/pages/admin/profile.js',
                'resources/js/pages/admin/users/secretaries.js',
                'resources/js/pages/admin/users/doctors.js',
                'resources/js/pages/admin/diagnostics/requests.js',
                'resources/js/pages/admin/diagnostics/category.js',
                'resources/js/pages/admin/medicines.js',
                'resources/js/pages/admin/charges/masterlist.js',
                'resources/js/pages/admin/charges/category.js',
                'resources/js/pages/admin/reports.js',
                'resources/js/pages/admin/library/diagnostic.js',
                'resources/js/pages/admin/library/medicines.js',
                'resources/js/pages/admin/philhealth/consultation-linking.js',
                'resources/js/pages/admin/philhealth/enlistment.js',
                'resources/js/pages/admin/philhealth/patient-linking.js',
                'resources/js/pages/admin/philhealth/philhealth-api.js',
                'resources/js/pages/admin/philhealth/profile-validation-codes.js',
                'resources/js/pages/admin/philhealth/reports.js',
                'resources/js/pages/admin/philhealth/soap-edit.js',
                'resources/js/pages/admin/philhealth/soap-form.js',
                'resources/js/pages/admin/philhealth/soap-validation-codes.js',
                'resources/js/pages/admin/philhealth/yakap-management.js',
                'resources/js/pages/admin/stocks/stock_ledger.js',
                'resources/js/pages/admin/stocks/stock_listing.js',
                'resources/js/pages/admin/stocks/stock_management.js',
                // Doctor pages
                'resources/js/pages/doctor/dashboard.js',
                'resources/js/pages/doctor/consultation.js',
                'resources/js/pages/doctor/patients.js',
                'resources/js/pages/doctor/consultation/form.js',
                // Admin philhealth pages
                'resources/js/pages/admin/philhealth/yakap-management.js',
                'resources/js/pages/admin/philhealth/soap-form.js',
                // Secretary pages
                'resources/js/pages/secretary/queue.js',
                'resources/js/pages/secretary/hmo.js',
            ],
            refresh: true,
        }),
        inject({
            $: 'jquery',
            jQuery: 'jquery',
        }),
    ],
    // Detailed comment: Configure Vite development server to bind explicitly to localhost
    // and specify a clean HTTP origin, avoiding ambiguous IPv6 [::1] addresses in public/hot.
    server: {
        host: 'localhost',
        port: 5173,
        strictPort: true,
        origin: 'http://localhost:5173',
        watch: {
            ignored: ['**/storage/framework/views/**'],
        }
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules')) {
                        return 'vendor';
                    }
                }
            }
        }
    }
});
