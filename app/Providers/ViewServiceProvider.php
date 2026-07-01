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
                            ['label' => 'Announcements', 'route' => 'announcement.index', 'icon' => 'mdi:megaphone-outline'],
                            ['label' => 'Employees', 'route' => 'employees.index', 'icon' => 'mdi:account-group-outline'],
                            ['label' => 'Employee Requests', 'route' => 'employee-edit-requests.index', 'icon' => 'mdi:account-edit-outline'],
                            ['label' => 'Organization', 'route' => 'organization.structure.index', 'icon' => 'mdi:sitemap-outline'],
                            ['label' => 'Careers', 'route' => 'career.index', 'icon' => 'mdi:briefcase-outline'],
                            ['label' => 'Overtime', 'route' => 'overtime-applications.index', 'icon' => 'mdi:clock-outline'],
                            ['label' => 'Applicants', 'route' => 'applicants.index', 'icon' => 'mdi:account-plus-outline'],
                            ['label' => 'KPIs', 'route' => 'kpi-templates.index', 'icon' => 'mdi:chart-line'],
                            ['label' => 'Onboarding', 'route' => 'onboarding.index', 'icon' => 'mdi:clipboard-check-outline'],
                            ['label' => 'Settings', 'route' => '#', 'icon' => 'mdi:cog-outline'],
                        ];
                        break;
                    case 'hc':
                        $menu = [
                            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'mdi:home-outline'],
                            ['label' => 'Announcements', 'route' => 'announcement.index', 'icon' => 'mdi:megaphone-outline'],
                            ['label' => 'Employees', 'route' => 'employees.index', 'icon' => 'mdi:account-group-outline'],
                            ['label' => 'Employee Requests', 'route' => 'employee-edit-requests.index', 'icon' => 'mdi:account-edit-outline'],
                            ['label' => 'Organization', 'route' => 'organization.structure.index', 'icon' => 'mdi:sitemap-outline'],
                            ['label' => 'Careers', 'route' => 'career.index', 'icon' => 'mdi:briefcase-outline'],
                            ['label' => 'Overtime', 'route' => 'overtime-applications.index', 'icon' => 'mdi:clock-outline'],
                            ['label' => 'Applicants', 'route' => 'applicants.index', 'icon' => 'mdi:account-plus-outline'],
                            ['label' => 'KPIs', 'route' => 'kpi-templates.index', 'icon' => 'mdi:chart-line'],
                            ['label' => 'Approvals', 'route' => 'approvals.index', 'icon' => 'mdi:check-decagram-outline'],
                            ['label' => 'Onboarding', 'route' => 'hc.onboarding.index', 'icon' => 'mdi:clipboard-check-outline'],
                            ['label' => 'Settings', 'route' => '#', 'icon' => 'mdi:cog-outline'],
                        ];
                        break;
                    case 'direksi':
                        $menu = [
                            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'mdi:home-outline'],
                            $employeeId
                            ? ['label' => 'My Data', 'route' => 'employees.show', 'params' => ['employee' => $employeeId], 'icon' => 'mdi:account-outline']
                            : ['label' => 'Employees', 'route' => 'employees.index', 'icon' => 'mdi:account-group-outline'],
                            ['label' => 'Organization', 'route' => 'organization.structure.index', 'icon' => 'mdi:sitemap-outline'],
                            ['label' => 'Careers', 'route' => 'career.index', 'icon' => 'mdi:briefcase-outline'],
                            ['label' => 'Applicants', 'route' => 'applicants.index', 'icon' => 'mdi:account-plus-outline'],
                            ['label' => 'KPIs', 'route' => 'kpi-assessments.index', 'icon' => 'mdi:chart-line'],
                            ['label' => 'Settings', 'route' => '#', 'icon' => 'mdi:cog-outline'],
                        ];
                        break;
                    case 'manager':
                        $menu = [
                            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'mdi:home-outline'],
                            $employeeId
                            ? ['label' => 'My Data', 'route' => 'employees.show', 'params' => ['employee' => $employeeId], 'icon' => 'mdi:account-outline']
                            : ['label' => 'Employees', 'route' => 'employees.index', 'icon' => 'mdi:account-group-outline'],
                            ['label' => 'Organization', 'route' => 'organization.structure.index', 'icon' => 'mdi:sitemap-outline'],
                            $careerMenu = $employeeId
                            ? ['label' => 'My Career', 'route' => 'employees.showCareer', 'params' => ['employee' => $employeeId], 'icon' => 'mdi:briefcase-outline']
                            : ['label' => 'Careers', 'route' => '#', 'icon' => 'mdi:briefcase-outline'],
                            ['label' => 'Overtime', 'route' => 'overtime-applications.index', 'icon' => 'mdi:clock-outline'],
                            ['label' => 'Applicants', 'route' => 'applicants.index', 'icon' => 'mdi:account-plus-outline'],
                            ['label' => 'KPIs', 'route' => 'kpi-assessments.index', 'icon' => 'mdi:chart-line'],
                            ['label' => 'Settings', 'route' => '#', 'icon' => 'mdi:cog-outline'],
                        ];
                        break;
                    case 'section_head':
                        $menu = [
                            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'mdi:home-outline'],
                            $employeeId
                            ? ['label' => 'My Data', 'route' => 'employees.show', 'params' => ['employee' => $employeeId], 'icon' => 'mdi:account-outline']
                            : ['label' => 'Employees', 'route' => 'employees.index', 'icon' => 'mdi:account-group-outline'],
                            ['label' => 'Organization', 'route' => 'organization.structure.index', 'icon' => 'mdi:sitemap-outline'],
                            $careerMenu = $employeeId
                            ? ['label' => 'My Career', 'route' => 'employees.showCareer', 'params' => ['employee' => $employeeId], 'icon' => 'mdi:briefcase-outline']
                            : ['label' => 'Careers', 'route' => '#', 'icon' => 'mdi:briefcase-outline'],
                            ['label' => 'Overtime', 'route' => 'overtime-applications.index', 'icon' => 'mdi:clock-outline'],
                            ['label' => 'Applicants', 'route' => 'applicants.index', 'icon' => 'mdi:account-plus-outline'],
                            ['label' => 'KPIs', 'route' => 'kpi-assessments.index', 'icon' => 'mdi:chart-line'],
                            ['label' => 'Settings', 'route' => '#', 'icon' => 'mdi:cog-outline'],
                        ];
                        break;
                    case 'staff_bisnis':
                        $menu = [
                            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'mdi:home-outline'],
                            $employeeId
                            ? ['label' => 'My Data', 'route' => 'employees.show', 'params' => ['employee' => $employeeId], 'icon' => 'mdi:account-outline']
                            : ['label' => 'Employees', 'route' => 'employees.index', 'icon' => 'mdi:account-group-outline'],
                            ['label' => 'Organization', 'route' => 'organization.structure.index', 'icon' => 'mdi:sitemap-outline'],
                            $careerMenu = $employeeId
                            ? ['label' => 'My Career', 'route' => 'employees.showCareer', 'params' => ['employee' => $employeeId], 'icon' => 'mdi:briefcase-outline']
                            : ['label' => 'Careers', 'route' => '#', 'icon' => 'mdi:briefcase-outline'],
                            ['label' => 'Overtime', 'route' => 'overtime-applications.index', 'icon' => 'mdi:clock-outline'],
                            ['label' => 'KPIs', 'route' => 'kpi-assessments.index', 'icon' => 'mdi:chart-line'],
                            ['label' => 'Settings', 'route' => '#', 'icon' => 'mdi:cog-outline'],
                        ];
                        break;
                    case 'staff_support':
                        $menu = [
                            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'mdi:home-outline'],
                            $employeeId
                            ? ['label' => 'My Data', 'route' => 'employees.show', 'params' => ['employee' => $employeeId], 'icon' => 'mdi:account-outline']
                            : ['label' => 'Employees', 'route' => 'employees.index', 'icon' => 'mdi:account-group-outline'],
                            ['label' => 'Organization', 'route' => 'organization.structure.index', 'icon' => 'mdi:sitemap-outline'],
                            $careerMenu = $employeeId
                            ? ['label' => 'My Career', 'route' => 'employees.showCareer', 'params' => ['employee' => $employeeId], 'icon' => 'mdi:briefcase-outline']
                            : ['label' => 'Careers', 'route' => '#', 'icon' => 'mdi:briefcase-outline'],
                            ['label' => 'Overtime', 'route' => 'overtime-applications.index', 'icon' => 'mdi:clock-outline'],
                            ['label' => 'KPIs', 'route' => 'kpi-assessments.index', 'icon' => 'mdi:chart-line'],
                            ['label' => 'Settings', 'route' => '#', 'icon' => 'mdi:cog-outline'],
                        ];
                        break;
                }
            }

            $view->with('menu', $menu);
        });
    }
}
