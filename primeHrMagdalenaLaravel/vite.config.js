import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';


export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/admin.css',
                'resources/css/adminDashboard.css',
                'resources/css/adminAttendance.css',
                'resources/css/adminRecruitment.css',
                'resources/css/adminTraining.css',
                'resources/css/adminPerformance.css',
                'resources/css/adminDepartment.css',
                'resources/css/employeeWizard.css',
                'resources/css/adminPersonnel.css',
                'resources/css/joborder.css',
                'resources/css/permanent.css',
                'resources/js/app.js',
                'resources/js/employeeWizard.js',
                'resources/js/adminPersonnel.js',
                'resources/js/personnelTopbar.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
