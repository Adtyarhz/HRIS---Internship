<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $user = Auth::user();
            $menu = [];

            if ($user) {
                switch ($user->role) {
                    case 'superadmin':
                       $menu = [
                            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'mdi-light--home'],
                            ['label' => 'Announcement', 'route' => 'announcement.index', 'icon' => 'bi--list-ul'],
                            ['label' => 'Employee Information', 'route' => 'employees.index', 'icon' => 'icon-park-outline--file-staff-one'],
                            ['label' => 'Employee Request', 'route' => '#', 'icon' => 'charm--git-request'],
                            ['label' => 'Organization Structure', 'route' => '#', 'icon' => 'fluent--organization-24-regular'],
                            ['label' => 'Careers Administration', 'route' => 'career.index', 'icon' => 'material-symbols--work-outline'],
                            ['label' => 'Time & Attendance', 'route' => '#', 'icon' => 'mdi--clock-outline'],
                            ['label' => 'Recruitment Applicant', 'route' => 'applicants.index', 'icon' => 'fluent-mdl2--recruitment-management'],
                            ['label' => 'Payroll', 'route' => '#', 'icon' => 'ri--bill-line'],
                            ['label' => 'Settings', 'route' => '#', 'icon' => 'uil--setting'],
                        ];
                        break;
                    case 'direksi':
                        $menu = [
                            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'mdi-light--home'],
                            ['label' => 'Employee Information', 'route' => 'employees.index', 'icon' => 'icon-park-outline--file-staff-one'],
                            ['label' => 'Organization Structure', 'route' => '#', 'icon' => 'fluent--organization-24-regular'],
                            ['label' => 'Careers Administration', 'route' => 'career.index', 'icon' => 'material-symbols--work-outline'],
                            ['label' => 'Time & Attendance', 'route' => '#', 'icon' => 'mdi--clock-outline'],
                            ['label' => 'Recruitment Applicant', 'route' => 'applicants.index', 'icon' => 'fluent-mdl2--recruitment-management'],
                            ['label' => 'Payroll', 'route' => '#', 'icon' => 'ri--bill-line'],
                            ['label' => 'Settings', 'route' => '#', 'icon' => 'uil--setting'],
                        ];
                        break;

                    case 'manager':
                        $menu = [
                            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'mdi-light--home'],
                            ['label' => 'Employee Information', 'route' => 'employees.index', 'icon' => 'icon-park-outline--file-staff-one'],
                            ['label' => 'Organization Structure', 'route' => '#', 'icon' => 'fluent--organization-24-regular'],
                            ['label' => 'Careers Administration', 'route' => 'career.index', 'icon' => 'material-symbols--work-outline'],
                            ['label' => 'Time & Attendance', 'route' => '#', 'icon' => 'mdi--clock-outline'],
                            ['label' => 'Recruitment Applicant', 'route' => 'applicants.index', 'icon' => 'fluent-mdl2--recruitment-management'],
                            ['label' => 'Payroll', 'route' => '#', 'icon' => 'ri--bill-line'],
                            ['label' => 'Settings', 'route' => '#', 'icon' => 'uil--setting'],
                        ];
                        break;
                    case 'section_head':
                        $menu = [
                            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'mdi-light--home'],
                            ['label' => 'Employee Information', 'route' => 'employees.index', 'icon' => 'icon-park-outline--file-staff-one'],
                            ['label' => 'Organization Structure', 'route' => '#', 'icon' => 'fluent--organization-24-regular'],
                            ['label' => 'Careers Administration', 'route' => 'career.index', 'icon' => 'material-symbols--work-outline'],
                            ['label' => 'Time & Attendance', 'route' => '#', 'icon' => 'mdi--clock-outline'],
                            ['label' => 'Recruitment Applicant', 'route' => 'applicants.index', 'icon' => 'fluent-mdl2--recruitment-management'],
                            ['label' => 'Payroll', 'route' => '#', 'icon' => 'ri--bill-line'],
                            ['label' => 'Settings', 'route' => '#', 'icon' => 'uil--setting'],
                        ];
                        break;          

                    case 'staff_bisnis':
                        $menu = [
                            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'mdi-light--home'],
                            ['label' => 'Employee Information', 'route' => 'employees.index', 'icon' => 'icon-park-outline--file-staff-one'],
                            ['label' => 'Organization Structure', 'route' => '#', 'icon' => 'fluent--organization-24-regular'],
                            ['label' => 'Careers Administration', 'route' => 'career.index', 'icon' => 'material-symbols--work-outline'],
                            ['label' => 'Time & Attendance', 'route' => '#', 'icon' => 'mdi--clock-outline'],
                            ['label' => 'Recruitment Applicant', 'route' => 'applicants.index', 'icon' => 'fluent-mdl2--recruitment-management'],
                            ['label' => 'Payroll', 'route' => '#', 'icon' => 'ri--bill-line'],
                            ['label' => 'Settings', 'route' => '#', 'icon' => 'uil--setting'],
                        ];
                        break;

                    case 'staff_support':
                        $menu = [
                            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'mdi-light--home'],
                            ['label' => 'Employee Information', 'route' => 'employees.index', 'icon' => 'icon-park-outline--file-staff-one'],
                            ['label' => 'Organization Structure', 'route' => '#', 'icon' => 'fluent--organization-24-regular'],
                            ['label' => 'Careers Administration', 'route' => 'career.index', 'icon' => 'material-symbols--work-outline'],
                            ['label' => 'Time & Attendance', 'route' => '#', 'icon' => 'mdi--clock-outline'],
                            ['label' => 'Recruitment Applicant', 'route' => 'applicants.index', 'icon' => 'fluent-mdl2--recruitment-management'],
                            ['label' => 'Payroll', 'route' => '#', 'icon' => 'ri--bill-line'],
                            ['label' => 'Settings', 'route' => '#', 'icon' => 'uil--setting'],
                        ];
                        break;

                    // Tambahkan case untuk role lain jika dibutuhkan
                }
            }

            $view->with('menuItems', $menu);
        });
    }
}
