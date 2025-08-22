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
            $employeeId = $user?->employee?->id;
            $menu = [];

            if ($user) {
                switch ($user->role) {
                    case 'superadmin':
                      $menu = [
                            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'mdi:home-outline'],
                            ['label' => 'Announcement', 'route' => 'announcement.index', 'icon' => 'bi:list-ul'],
                            ['label' => 'Employee Information', 'route' => 'employees.index', 'icon' => 'icon-park-outline:file-staff-one'],
                            ['label' => 'Employee Request', 'route' => 'employee-edit-requests.index', 'icon' => 'charm:git-request'],
                            ['label' => 'Organization Structure', 'route' => '#', 'icon' => 'fluent:organization-24-regular'],
                            ['label' => 'Careers Administration', 'route' => 'career.index', 'icon' => 'material-symbols:work-outline'],
                            ['label' => 'Time & Attendance', 'route' => '#', 'icon' => 'mdi:clock-outline'],
                            ['label' => 'Recruitment Applicant', 'route' => 'applicants.index', 'icon' => 'fluent-mdl2:recruitment-management'],
                            ['label' => 'Payroll', 'route' => '#', 'icon' => 'ri:bill-line'],
                            ['label' => 'Settings', 'route' => '#', 'icon' => 'uil:setting'],
                        ];
                        break;
                    case 'hc':
                         $menu = [
                            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'mdi:home-outline'],
                            ['label' => 'Announcement', 'route' => 'announcement.index', 'icon' => 'bi:list-ul'],
                            ['label' => 'Employee Information', 'route' => 'employees.index', 'icon' => 'icon-park-outline:file-staff-one'],
                            ['label' => 'Employee Request', 'route' => 'employee-edit-requests.index', 'icon' => 'charm:git-request'],
                            ['label' => 'Organization Structure', 'route' => '#', 'icon' => 'fluent:organization-24-regular'],
                            ['label' => 'Careers Administration', 'route' => 'career.index', 'icon' => 'material-symbols:work-outline'],
                            ['label' => 'Time & Attendance', 'route' => '#', 'icon' => 'mdi:clock-outline'],
                            ['label' => 'Recruitment Applicant', 'route' => 'applicants.index', 'icon' => 'fluent-mdl2:recruitment-management'],
                            ['label' => 'Payroll', 'route' => '#', 'icon' => 'ri:bill-line'],
                            ['label' => 'Settings', 'route' => '#', 'icon' => 'uil:setting'],
                        ];
                        break;
                    case 'direksi':
                        $menu = [
                            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'mdi:home-outline'],
                            $employeeId
                            ? ['label' => 'Employee Information', 'route' => 'employees.show', 'params' => ['employee' => $employeeId], 'icon' => 'icon-park-outline:file-staff-one']
                            : ['label' => 'Employee Information', 'route' => 'employees.index', 'icon' => 'icon-park-outline:file-staff-one'],
                            ['label' => 'Organization Structure', 'route' => '#', 'icon' => 'fluent:organization-24-regular'],
                            ['label' => 'Careers Administration', 'route' => 'career.index', 'icon' => 'material-symbols:work-outline'],
                            ['label' => 'Time & Attendance', 'route' => '#', 'icon' => 'mdi:clock-outline'],
                            ['label' => 'Recruitment Applicant', 'route' => 'applicants.index', 'icon' => 'fluent-mdl2:recruitment-management'],
                            ['label' => 'Payroll', 'route' => '#', 'icon' => 'ri:bill-line'],
                            ['label' => 'Settings', 'route' => '#', 'icon' => 'uil:setting'],
                        ];
                        break;
                    case 'manager':
                        $menu = [
                            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'mdi:home-outline'],
                            $employeeId
                            ? ['label' => 'Employee Information', 'route' => 'employees.show', 'params' => ['employee' => $employeeId], 'icon' => 'icon-park-outline:file-staff-one']
                            : ['label' => 'Employee Information', 'route' => 'employees.index', 'icon' => 'icon-park-outline:file-staff-one'],
                            ['label' => 'Organization Structure', 'route' => '#', 'icon' => 'fluent:organization-24-regular'],
                            $careerMenu = $employeeId
                            ? ['label' => 'Careers Administration', 'route' => 'employees.showCareer', 'params' => ['employee' => $employeeId], 'icon' => 'material-symbols:work-outline']
                            : ['label' => 'Careers Administration', 'route' => '#', 'icon' => 'material-symbols:work-outline'],
                            ['label' => 'Time & Attendance', 'route' => '#', 'icon' => 'mdi:clock-outline'],
                            ['label' => 'Recruitment Applicant', 'route' => 'applicants.index', 'icon' => 'fluent-mdl2:recruitment-management'],
                            ['label' => 'Payroll', 'route' => '#', 'icon' => 'ri:bill-line'],
                            ['label' => 'Settings', 'route' => '#', 'icon' => 'uil:setting'],
                        ];
                        break;
                    case 'section_head':
                       $menu = [
                            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'mdi:home-outline'],
                            $employeeId
                            ? ['label' => 'Employee Information', 'route' => 'employees.show', 'params' => ['employee' => $employeeId], 'icon' => 'icon-park-outline:file-staff-one']
                            : ['label' => 'Employee Information', 'route' => 'employees.index', 'icon' => 'icon-park-outline:file-staff-one'],
                            ['label' => 'Organization Structure', 'route' => '#', 'icon' => 'fluent:organization-24-regular'],
                            $careerMenu = $employeeId
                            ? ['label' => 'Careers Administration', 'route' => 'employees.showCareer', 'params' => ['employee' => $employeeId], 'icon' => 'material-symbols:work-outline']
                            : ['label' => 'Careers Administration', 'route' => '#', 'icon' => 'material-symbols:work-outline'],
                            ['label' => 'Time & Attendance', 'route' => '#', 'icon' => 'mdi:clock-outline'],
                            ['label' => 'Recruitment Applicant', 'route' => 'applicants.index', 'icon' => 'fluent-mdl2:recruitment-management'],
                            ['label' => 'Payroll', 'route' => '#', 'icon' => 'ri:bill-line'],
                            ['label' => 'Settings', 'route' => '#', 'icon' => 'uil:setting'],
                        ];
                        break;
                    case 'staff_bisnis':
                        $menu = [
                            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'mdi:home-outline'],
                            $employeeId
                            ? ['label' => 'Employee Information', 'route' => 'employees.show', 'params' => ['employee' => $employeeId], 'icon' => 'icon-park-outline:file-staff-one']
                            : ['label' => 'Employee Information', 'route' => 'employees.index', 'icon' => 'icon-park-outline:file-staff-one'],
                            ['label' => 'Organization Structure', 'route' => '#', 'icon' => 'fluent:organization-24-regular'],
                            $careerMenu = $employeeId
                            ? ['label' => 'Careers Administration', 'route' => 'employees.showCareer', 'params' => ['employee' => $employeeId], 'icon' => 'material-symbols:work-outline']
                            : ['label' => 'Careers Administration', 'route' => '#', 'icon' => 'material-symbols:work-outline'],
                            ['label' => 'Time & Attendance', 'route' => '#', 'icon' => 'mdi:clock-outline'],
                            ['label' => 'Payroll', 'route' => '#', 'icon' => 'ri:bill-line'],
                            ['label' => 'Settings', 'route' => '#', 'icon' => 'uil:setting'],
                        ];
                        break;
                    case 'staff_support':
                        $menu = [
                            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'mdi:home-outline'],
                            $employeeId
                            ? ['label' => 'Employee Information', 'route' => 'employees.show', 'params' => ['employee' => $employeeId], 'icon' => 'icon-park-outline:file-staff-one']
                            : ['label' => 'Employee Information', 'route' => 'employees.index', 'icon' => 'icon-park-outline:file-staff-one'],
                            ['label' => 'Organization Structure', 'route' => '#', 'icon' => 'fluent:organization-24-regular'],
                            $careerMenu = $employeeId
                            ? ['label' => 'Careers Administration', 'route' => 'employees.showCareer', 'params' => ['employee' => $employeeId], 'icon' => 'material-symbols:work-outline']
                            : ['label' => 'Careers Administration', 'route' => '#', 'icon' => 'material-symbols:work-outline'],
                            ['label' => 'Time & Attendance', 'route' => '#', 'icon' => 'mdi:clock-outline'],
                            ['label' => 'Payroll', 'route' => '#', 'icon' => 'ri:bill-line'],
                            ['label' => 'Settings', 'route' => '#', 'icon' => 'uil:setting'],
                        ];
                        break;
                }
            }

            $view->with('menu', $menu);
        });
    }
}
