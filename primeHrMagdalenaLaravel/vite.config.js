import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

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
                'resources/css/employee.css',
                'resources/css/employeeDashboard.css',
                'resources/css/adminChatbot.css',
                'resources/css/adminPayroll.css',
                'resources/css/adminLeaveAndBenefits.css',
                'resources/css/topbarTheme.css',
                'resources/js/app.js',
                'resources/js/employeeDashboard.js',
                'resources/js/personnel/employeeWizard.js',
                'resources/js/personnel/employeeWizardComplete.js',
                'resources/js/personnel/adminPersonnel.js',
                'resources/js/personnel/assignSchedule.js',
                'resources/js/personnel/bulkAssignSchedule.js',
                'resources/js/personnel/viewSchedules.js',
                'resources/js/adminAttendance.js',
                'resources/js/adminLeaveAndBenefits.js',
                'resources/js/adminDashboard.js'
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
