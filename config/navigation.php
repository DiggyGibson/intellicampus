<?php

return [
    /**
     * IntelliCampus Navigation Configuration
     * 
     * This file defines all navigation menus for different user roles.
     * Each role sees only the menu items they have permission to access.
     * 
     * IMPORTANT: The 'Dashboard' link is role-specific and routes to different
     * pages based on the user's primary role.
     */
    
    // ============================================================
    // ROLE-SPECIFIC LANDING PAGES
    // ============================================================
    'landing_pages' => [
        'super-administrator' => '/admin/dashboard',
        'system-administrator' => '/admin/dashboard',
        'academic-administrator' => '/admin/dashboard',
        'financial-administrator' => '/financial/admin/dashboard',
        'registrar' => '/registrar/dashboard',
        'dean' => '/department/dashboard',
        'department-head' => '/department/dashboard',
        'faculty' => '/faculty/dashboard',
        'advisor' => '/advisor/dashboard',
        'staff' => '/staff/dashboard',
        'student' => '/student/dashboard',
        'parent-guardian' => '/parent/dashboard',
        'auditor' => '/audit/dashboard',
        'alumni' => '/alumni/dashboard',
        'applicant' => '/admissions/portal',
        'admissions-director' => '/admissions/admin/dashboard',
        'admissions-officer' => '/admissions/admin/dashboard',
        'guest' => '/public',
    ],

    // ============================================================
    // MAIN SIDEBAR NAVIGATION
    // ============================================================
    'sidebar' => [
        
        // ============================================================
        // STUDENT MENU (Only visible to students)
        // ============================================================
        [
            'title' => 'Student Portal',
            'order' => 10,
            'roles' => ['student'],
            'items' => [
                [
                    'label' => 'My Dashboard',
                    'icon' => 'fas fa-home',
                    'route' => 'student.dashboard',
                    'roles' => ['student'],
                ],
                [
                    'label' => 'Course Registration',
                    'icon' => 'fas fa-clipboard-check',
                    'route' => 'registration.browse',
                    'roles' => ['student'],
                    'children' => [
                        [
                            'label' => 'Browse Courses',
                            'route' => 'registration.browse',
                            'roles' => ['student'],
                        ],
                        [
                            'label' => 'Registration Cart',
                            'route' => 'registration.cart',
                            'roles' => ['student'],
                        ],
                        [
                            'label' => 'My Schedule',
                            'route' => 'registration.schedule',
                            'roles' => ['student'],
                        ],
                        [
                            'label' => 'Waitlist',
                            'route' => 'registration.waitlist',
                            'roles' => ['student'],
                        ],
                    ],
                ],
                [
                    'label' => 'My Grades',
                    'icon' => 'fas fa-chart-line',
                    'route' => 'student.grades.index',
                    'roles' => ['student'],
                    'children' => [
                        [
                            'label' => 'Current Grades',
                            'route' => 'student.grades.current',
                            'roles' => ['student'],
                        ],
                        [
                            'label' => 'Grade History',
                            'route' => 'student.grades.history',
                            'roles' => ['student'],
                        ],
                        [
                            'label' => 'GPA Calculator',
                            'route' => 'student.grades.gpa-calculator',
                            'roles' => ['student'],
                        ],
                    ],
                ],
                [
                    'label' => 'Academic Records',
                    'icon' => 'fas fa-folder-open',
                    'route' => 'student.records',
                    'roles' => ['student'],
                    'children' => [
                        [
                            'label' => 'Degree Audit',
                            'route' => 'degree-audit.my',
                            'roles' => ['student'],
                        ],
                        [
                            'label' => 'Request Transcript',
                            'route' => 'transcripts.my',
                            'roles' => ['student'],
                        ],
                        [
                            'label' => 'Enrollment Verification',
                            'route' => 'student.enrollment.verify',
                            'roles' => ['student'],
                        ],
                    ],
                ],
                [
                    'label' => 'Financial Account',
                    'icon' => 'fas fa-dollar-sign',
                    'route' => 'financial.student.dashboard',
                    'roles' => ['student'],
                    'children' => [
                        [
                            'label' => 'Account Summary',
                            'route' => 'financial.student.dashboard',
                            'roles' => ['student'],
                        ],
                        [
                            'label' => 'Make Payment',
                            'route' => 'financial.student.pay',
                            'roles' => ['student'],
                        ],
                        [
                            'label' => 'Payment History',
                            'route' => 'financial.student.payments',
                            'roles' => ['student'],
                        ],
                        [
                            'label' => 'Financial Aid',
                            'route' => 'financial.student.aid',
                            'roles' => ['student'],
                        ],
                    ],
                ],
                [
                    'label' => 'Library Services',
                    'icon' => 'fas fa-book',
                    'route' => 'library.index',
                    'roles' => ['student'],
                ],
                [
                    'label' => 'Messages',
                    'icon' => 'fas fa-envelope',
                    'route' => 'messages.index',
                    'roles' => ['student'],
                    'badge' => [
                        'callback' => function($user) {
                            return $user->unread_messages_count ?? 0;
                        },
                        'class' => 'bg-danger'
                    ],
                ],
            ],
        ],

        // ============================================================
        // FACULTY MENU (Only visible to faculty)
        // ============================================================
        [
            'title' => 'Faculty Portal',
            'order' => 20,
            'roles' => ['faculty', 'instructor'],
            'items' => [
                [
                    'label' => 'Faculty Dashboard',
                    'icon' => 'fas fa-chalkboard-teacher',
                    'route' => 'faculty.dashboard',
                    'roles' => ['faculty', 'instructor'],
                ],
                [
                    'label' => 'My Courses',
                    'icon' => 'fas fa-book-reader',
                    'route' => 'faculty.courses.index',
                    'roles' => ['faculty', 'instructor'],
                    'children' => [
                        [
                            'label' => 'Current Sections',
                            'route' => 'faculty.sections.current',
                            'roles' => ['faculty'],
                        ],
                        [
                            'label' => 'Course Materials',
                            'route' => 'lms.materials.index',
                            'roles' => ['faculty'],
                        ],
                        [
                            'label' => 'Assignments',
                            'route' => 'lms.assignments.index',
                            'roles' => ['faculty'],
                        ],
                    ],
                ],
                [
                    'label' => 'Gradebook',
                    'icon' => 'fas fa-edit',
                    'route' => 'grades.my-sections',
                    'roles' => ['faculty', 'instructor'],
                    'children' => [
                        [
                            'label' => 'Enter Grades',
                            'route' => 'grades.entry',
                            'roles' => ['faculty'],
                        ],
                        [
                            'label' => 'Grade Components',
                            'route' => 'grades.components',
                            'roles' => ['faculty'],
                        ],
                        [
                            'label' => 'Grade Statistics',
                            'route' => 'grades.statistics.overview',
                            'roles' => ['faculty'],
                        ],
                    ],
                ],
                [
                    'label' => 'Attendance',
                    'icon' => 'fas fa-clipboard-check',
                    'route' => 'faculty.attendance.index',
                    'roles' => ['faculty', 'instructor'],
                ],
                [
                    'label' => 'Office Hours',
                    'icon' => 'fas fa-clock',
                    'route' => 'faculty.office-hours.index',
                    'roles' => ['faculty', 'instructor'],
                ],
                [
                    'label' => 'My Advisees',
                    'icon' => 'fas fa-user-friends',
                    'route' => 'advisor.advisees.index',
                    'roles' => ['advisor', 'faculty'],
                ],
                [
                    'label' => 'Messages',
                    'icon' => 'fas fa-envelope',
                    'route' => 'messages.index',
                    'roles' => ['faculty'],
                ],
            ],
        ],

        // ============================================================
        // ADMISSIONS & ENROLLMENT MENU
        // ============================================================
        [
            'title' => 'Admissions & Enrollment',
            'order' => 35,
            'roles' => ['super-administrator', 'admin', 'admissions-director', 'admissions-officer', 'registrar'],
            'items' => [
                [
                    'label' => 'Admissions Dashboard',
                    'icon' => 'fas fa-university',
                    'route' => 'admin.admissions.dashboard',
                    'roles' => ['super-administrator', 'admin', 'admissions-director', 'admissions-officer'],
                    'active_patterns' => ['admin.admissions.*'],
                ],
                [
                    'label' => 'Applications',
                    'icon' => 'fas fa-file-alt',
                    'route' => 'admin.admissions.applications.index',
                    'roles' => ['super-administrator', 'admin', 'admissions-director', 'admissions-officer'],
                    'badge' => [
                        'callback' => function($user) {
                            return \App\Models\AdmissionApplication::where('status', 'submitted')->count();
                        },
                        'class' => 'bg-warning'
                    ],
                    'children' => [
                        [
                            'label' => 'All Applications',
                            'route' => 'admin.admissions.applications.index',
                            'icon' => 'fas fa-list',
                        ],
                        [
                            'label' => 'Pending Review',
                            'route' => 'admin.admissions.applications.pending',
                            'icon' => 'fas fa-clock',
                        ],
                        [
                            'label' => 'Under Review',
                            'route' => 'admin.admissions.applications.under-review',
                            'icon' => 'fas fa-spinner',
                        ],
                        [
                            'label' => 'Incomplete',
                            'route' => 'admin.admissions.applications.incomplete',
                            'icon' => 'fas fa-exclamation-triangle',
                        ],
                    ],
                ],
                [
                    'label' => 'Review Management',
                    'icon' => 'fas fa-tasks',
                    'route' => 'admin.admissions.reviews.index',
                    'roles' => ['super-administrator', 'admin', 'admissions-officer'],
                    'children' => [
                        [
                            'label' => 'My Reviews',
                            'route' => 'admin.admissions.reviews.my-reviews',
                            'icon' => 'fas fa-user-check',
                        ],
                        [
                            'label' => 'Review Queue',
                            'route' => 'admin.admissions.reviews.queue',
                            'icon' => 'fas fa-list-ol',
                        ],
                        [
                            'label' => 'Review Statistics',
                            'route' => 'admin.admissions.reviews.statistics',
                            'icon' => 'fas fa-chart-bar',
                        ],
                    ],
                ],
                [
                    'label' => 'Decisions',
                    'icon' => 'fas fa-gavel',
                    'route' => 'admin.admissions.decisions.index',
                    'roles' => ['super-administrator', 'admin', 'admissions-director'],
                    'children' => [
                        [
                            'label' => 'Pending Decisions',
                            'route' => 'admin.admissions.decisions.pending',
                            'icon' => 'fas fa-hourglass-half',
                        ],
                        [
                            'label' => 'Decision History',
                            'route' => 'admin.admissions.decisions.history',
                            'icon' => 'fas fa-history',
                        ],
                        [
                            'label' => 'Decision Letters',
                            'route' => 'admin.admissions.decisions.letters',
                            'icon' => 'fas fa-envelope',
                        ],
                    ],
                ],
                [
                    'label' => 'Document Verification',
                    'icon' => 'fas fa-check-double',
                    'route' => 'admin.admissions.verification.index',
                    'roles' => ['super-administrator', 'admin', 'admissions-officer'],
                    'children' => [
                        [
                            'label' => 'Pending Documents',
                            'route' => 'admin.admissions.verification.pending',
                            'icon' => 'fas fa-file-upload',
                        ],
                        [
                            'label' => 'Verified Documents',
                            'route' => 'admin.admissions.verification.verified',
                            'icon' => 'fas fa-check-circle',
                        ],
                        [
                            'label' => 'Rejected Documents',
                            'route' => 'admin.admissions.verification.rejected',
                            'icon' => 'fas fa-times-circle',
                        ],
                    ],
                ],
                [
                    'label' => 'Entrance Exams',
                    'icon' => 'fas fa-clipboard-list',
                    'route' => 'admin.admissions.exams.index',
                    'roles' => ['super-administrator', 'admin', 'admissions-officer'],
                    'children' => [
                        [
                            'label' => 'Exam Schedule',
                            'route' => 'admin.admissions.exams.schedule',
                            'icon' => 'fas fa-calendar-alt',
                        ],
                        [
                            'label' => 'Registrations',
                            'route' => 'admin.admissions.exams.registrations',
                            'icon' => 'fas fa-users',
                        ],
                        [
                            'label' => 'Results',
                            'route' => 'admin.admissions.exams.results',
                            'icon' => 'fas fa-chart-line',
                        ],
                    ],
                ],
                [
                    'label' => 'Enrollment',
                    'icon' => 'fas fa-user-graduate',
                    'route' => 'admin.admissions.enrollment.index',
                    'roles' => ['super-administrator', 'admin', 'admissions-officer', 'registrar'],
                    'children' => [
                        [
                            'label' => 'Pending Enrollments',
                            'route' => 'admin.admissions.enrollment.pending',
                            'icon' => 'fas fa-hourglass',
                        ],
                        [
                            'label' => 'Confirmed',
                            'route' => 'admin.admissions.enrollment.confirmed',
                            'icon' => 'fas fa-check',
                        ],
                        [
                            'label' => 'Deposit Status',
                            'route' => 'admin.admissions.enrollment.deposits',
                            'icon' => 'fas fa-dollar-sign',
                        ],
                    ],
                ],
                [
                    'label' => 'Reports & Analytics',
                    'icon' => 'fas fa-chart-bar',
                    'route' => 'admin.admissions.reports.index',
                    'roles' => ['super-administrator', 'admin', 'admissions-director'],
                    'children' => [
                        [
                            'label' => 'Statistics',
                            'route' => 'admin.admissions.statistics',
                            'icon' => 'fas fa-chart-pie',
                        ],
                        [
                            'label' => 'Conversion Rates',
                            'route' => 'admin.admissions.reports.conversion',
                            'icon' => 'fas fa-percentage',
                        ],
                        [
                            'label' => 'Demographic Analysis',
                            'route' => 'admin.admissions.reports.demographics',
                            'icon' => 'fas fa-users',
                        ],
                        [
                            'label' => 'Custom Reports',
                            'route' => 'admin.admissions.reports.custom',
                            'icon' => 'fas fa-file-export',
                        ],
                    ],
                ],
                [
                    'label' => 'Settings',
                    'icon' => 'fas fa-cog',
                    'route' => 'admin.admissions.settings.index',
                    'roles' => ['super-administrator', 'admin', 'admissions-director'],
                    'children' => [
                        [
                            'label' => 'Requirements',
                            'route' => 'admin.admissions.settings.requirements',
                            'icon' => 'fas fa-list-ul',
                        ],
                        [
                            'label' => 'Fee Structure',
                            'route' => 'admin.admissions.settings.fees',
                            'icon' => 'fas fa-money-bill',
                        ],
                        [
                            'label' => 'Email Templates',
                            'route' => 'admin.admissions.settings.templates',
                            'icon' => 'fas fa-envelope-open-text',
                        ],
                        [
                            'label' => 'Scoring Rubric',
                            'route' => 'admin.admissions.settings.rubric',
                            'icon' => 'fas fa-star',
                        ],
                    ],
                ],
            ],
        ],

        // ============================================================
        // REGISTRAR MENU (Only visible to registrar staff)
        // ============================================================
        [
            'title' => 'Registrar Office',
            'order' => 30,
            'roles' => ['registrar'],
            'items' => [
                [
                    'label' => 'Registrar Dashboard',
                    'icon' => 'fas fa-university',
                    'route' => 'registrar.dashboard',
                    'roles' => ['registrar'],
                ],
                [
                    'label' => 'Student Records',
                    'icon' => 'fas fa-folder-open',
                    'route' => 'registrar.students.index',
                    'roles' => ['registrar'],
                    'children' => [
                        [
                            'label' => 'Search Students',
                            'route' => 'registrar.students.search',
                            'roles' => ['registrar'],
                        ],
                        [
                            'label' => 'Academic Records',
                            'route' => 'registrar.students.index',
                            'roles' => ['registrar'],
                        ],
                        [
                            'label' => 'Name Changes',
                            'route' => 'registrar.name-changes.pending',
                            'roles' => ['registrar'],
                        ],
                    ],
                ],
                [
                    'label' => 'Enrollment Management',
                    'icon' => 'fas fa-user-check',
                    'route' => 'registrar.enrollment.index',
                    'roles' => ['registrar'],
                    'children' => [
                        [
                            'label' => 'Enrollment Verification',
                            'route' => 'registrar.enrollment.verification',
                            'roles' => ['registrar'],
                        ],
                        [
                            'label' => 'Enrollment Statistics',
                            'route' => 'registrar.enrollment.statistics',
                            'roles' => ['registrar'],
                        ],
                    ],
                ],
                [
                    'label' => 'Transcripts',
                    'icon' => 'fas fa-file-alt',
                    'route' => 'registrar.transcripts.admin',
                    'roles' => ['registrar'],
                    'children' => [
                        [
                            'label' => 'Pending Requests',
                            'route' => 'registrar.transcripts.pending',
                            'roles' => ['registrar'],
                        ],
                        [
                            'label' => 'Process Requests',
                            'route' => 'registrar.transcripts.requests',
                            'roles' => ['registrar'],
                        ],
                    ],
                ],
                [
                    'label' => 'Grade Management',
                    'icon' => 'fas fa-percentage',
                    'route' => 'registrar.grades.changes',
                    'roles' => ['registrar'],
                    'children' => [
                        [
                            'label' => 'Grade Changes',
                            'route' => 'registrar.grades.changes',
                            'roles' => ['registrar'],
                        ],
                        [
                            'label' => 'Grade History',
                            'route' => 'registrar.grades.history',
                            'roles' => ['registrar'],
                        ],
                    ],
                ],
                [
                    'label' => 'Graduation',
                    'icon' => 'fas fa-graduation-cap',
                    'route' => 'registrar.graduation.candidates',
                    'roles' => ['registrar'],
                ],
                [
                    'label' => 'Reports',
                    'icon' => 'fas fa-chart-bar',
                    'route' => 'registrar.reports.index',
                    'roles' => ['registrar'],
                ],
            ],
        ],

        // ============================================================
        // DEPARTMENT HEAD MENU
        // ============================================================
        [
            'title' => 'Department Management',
            'order' => 25,
            'roles' => ['department-head', 'department-chair', 'dean'],
            'items' => [
                [
                    'label' => 'Department Dashboard',
                    'icon' => 'fas fa-building',
                    'route' => 'department.dashboard',
                    'roles' => ['department-head', 'department-chair', 'dean'],
                ],
                [
                    'label' => 'Faculty Management',
                    'icon' => 'fas fa-users',
                    'route' => 'department.faculty.index',
                    'roles' => ['department-head', 'dean'],
                ],
                [
                    'label' => 'Course Management',
                    'icon' => 'fas fa-book',
                    'route' => 'department.courses.index',
                    'roles' => ['department-head', 'dean'],
                ],
                [
                    'label' => 'Curriculum Planning',
                    'icon' => 'fas fa-project-diagram',
                    'route' => 'department.curriculum.index',
                    'roles' => ['department-head', 'dean'],
                ],
                [
                    'label' => 'Course Scheduling',
                    'icon' => 'fas fa-calendar-alt',
                    'route' => 'department.scheduling.index',
                    'roles' => ['department-head'],
                ],
                [
                    'label' => 'Department Reports',
                    'icon' => 'fas fa-chart-line',
                    'route' => 'department.reports.index',
                    'roles' => ['department-head', 'dean'],
                ],
            ],
        ],

        // ============================================================
        // FINANCIAL ADMINISTRATOR MENU
        // ============================================================
        [
            'title' => 'Financial Administration',
            'order' => 40,
            'roles' => ['financial-administrator'],
            'items' => [
                [
                    'label' => 'Financial Dashboard',
                    'icon' => 'fas fa-chart-pie',
                    'route' => 'financial.admin.dashboard',
                    'roles' => ['financial-administrator'],
                ],
                [
                    'label' => 'Student Accounts',
                    'icon' => 'fas fa-user-graduate',
                    'route' => 'financial.admin.accounts',
                    'roles' => ['financial-administrator'],
                ],
                [
                    'label' => 'Billing & Invoices',
                    'icon' => 'fas fa-file-invoice',
                    'route' => 'financial.admin.billing',
                    'roles' => ['financial-administrator'],
                ],
                [
                    'label' => 'Payment Processing',
                    'icon' => 'fas fa-credit-card',
                    'route' => 'financial.admin.payments',
                    'roles' => ['financial-administrator'],
                ],
                [
                    'label' => 'Financial Aid',
                    'icon' => 'fas fa-hand-holding-usd',
                    'route' => 'financial.admin.aid',
                    'roles' => ['financial-administrator'],
                ],
                [
                    'label' => 'Fee Management',
                    'icon' => 'fas fa-tags',
                    'route' => 'financial.admin.fees',
                    'roles' => ['financial-administrator'],
                ],
                [
                    'label' => 'Financial Reports',
                    'icon' => 'fas fa-chart-bar',
                    'route' => 'financial.admin.reports',
                    'roles' => ['financial-administrator'],
                ],
            ],
        ],

        // ============================================================
        // ADMISSIONS MENU
        // ============================================================
        [
            'title' => 'Admissions',
            'order' => 35,
            'roles' => ['admissions-director', 'admissions-officer'],
            'items' => [
                [
                    'label' => 'Admissions Dashboard',
                    'icon' => 'fas fa-university',
                    'route' => 'admissions.admin.dashboard',
                    'roles' => ['admissions-director', 'admissions-officer'],
                ],
                [
                    'label' => 'Applications',
                    'icon' => 'fas fa-file-alt',
                    'route' => 'admissions.admin.applications',
                    'roles' => ['admissions-director', 'admissions-officer'],
                    'children' => [
                        [
                            'label' => 'Pending Review',
                            'route' => 'admissions.admin.applications.pending',
                            'roles' => ['admissions-officer'],
                        ],
                        [
                            'label' => 'Under Review',
                            'route' => 'admissions.admin.applications.reviewing',
                            'roles' => ['admissions-officer'],
                        ],
                        [
                            'label' => 'Decisions',
                            'route' => 'admissions.admin.applications.decisions',
                            'roles' => ['admissions-director'],
                        ],
                    ],
                ],
                [
                    'label' => 'Applicants',
                    'icon' => 'fas fa-users',
                    'route' => 'admissions.admin.applicants',
                    'roles' => ['admissions-officer'],
                ],
                [
                    'label' => 'Requirements',
                    'icon' => 'fas fa-clipboard-list',
                    'route' => 'admissions.admin.requirements',
                    'roles' => ['admissions-director'],
                ],
                [
                    'label' => 'Reports',
                    'icon' => 'fas fa-chart-bar',
                    'route' => 'admissions.admin.reports',
                    'roles' => ['admissions-director'],
                ],
            ],
        ],

        // ============================================================
        // ADMINISTRATION MENU (Only for admin roles)
        // ============================================================
        [
            'title' => 'Administration',
            'order' => 50,
            'roles' => ['super-administrator', 'system-administrator', 'academic-administrator'],
            'items' => [
                [
                    'label' => 'Admin Dashboard',
                    'icon' => 'fas fa-tachometer-alt',
                    'route' => 'admin.dashboard',
                    'roles' => ['super-administrator', 'system-administrator', 'academic-administrator'],
                ],
                [
                    'label' => 'User Management',
                    'icon' => 'fas fa-users-cog',
                    'route' => 'admin.users.index',
                    'roles' => ['super-administrator', 'system-administrator'],
                    'children' => [
                        [
                            'label' => 'All Users',
                            'route' => 'admin.users.index',
                            'roles' => ['super-administrator'],
                        ],
                        [
                            'label' => 'Create User',
                            'route' => 'admin.users.create',
                            'roles' => ['super-administrator'],
                        ],
                        [
                            'label' => 'Roles',
                            'route' => 'admin.roles.index',
                            'roles' => ['super-administrator'],
                        ],
                        [
                            'label' => 'Permissions',
                            'route' => 'admin.permissions.index',
                            'roles' => ['super-administrator'],
                        ],
                    ],
                ],
                [
                    'label' => 'Academic Management',
                    'icon' => 'fas fa-graduation-cap',
                    'route' => 'admin.academic.index',
                    'roles' => ['academic-administrator'],
                    'children' => [
                        [
                            'label' => 'Programs',
                            'route' => 'admin.programs.index',
                            'roles' => ['academic-administrator'],
                        ],
                        [
                            'label' => 'Courses',
                            'route' => 'admin.courses.index',
                            'roles' => ['academic-administrator'],
                        ],
                        [
                            'label' => 'Terms',
                            'route' => 'admin.terms.index',
                            'roles' => ['academic-administrator'],
                        ],
                    ],
                ],
                [
                    'label' => 'System Reports',
                    'icon' => 'fas fa-chart-bar',
                    'route' => 'admin.reports.index',
                    'roles' => ['super-administrator', 'system-administrator'],
                ],
            ],
        ],

        // ============================================================
        // SYSTEM MENU (Only for system administrators)
        // ============================================================
        [
            'title' => 'System',
            'order' => 60,
            'roles' => ['super-administrator', 'system-administrator'],
            'items' => [
                [
                    'label' => 'System Settings',
                    'icon' => 'fas fa-cogs',
                    'route' => 'system.settings.index',
                    'roles' => ['system-administrator', 'super-administrator'],
                ],
                [
                    'label' => 'Module Management',
                    'icon' => 'fas fa-puzzle-piece',
                    'route' => 'system.modules.index',
                    'roles' => ['system-administrator', 'super-administrator'],
                ],
                [
                    'label' => 'Audit Logs',
                    'icon' => 'fas fa-history',
                    'route' => 'system.audit-logs.index',
                    'roles' => ['system-administrator', 'super-administrator'],
                ],
                [
                    'label' => 'Backups',
                    'icon' => 'fas fa-database',
                    'route' => 'system.backups.index',
                    'roles' => ['system-administrator', 'super-administrator'],
                ],
                [
                    'label' => 'System Analysis',
                    'icon' => 'fas fa-microscope',
                    'route' => 'system.analysis',
                    'roles' => ['super-administrator'],
                ],
            ],
        ],

        // ============================================================
        // PARENT/GUARDIAN MENU
        // ============================================================
        [
            'title' => 'Parent Portal',
            'order' => 70,
            'roles' => ['parent-guardian'],
            'items' => [
                [
                    'label' => 'Dashboard',
                    'icon' => 'fas fa-home',
                    'route' => 'parent.dashboard',
                    'roles' => ['parent-guardian'],
                ],
                [
                    'label' => 'Student Overview',
                    'icon' => 'fas fa-child',
                    'route' => 'parent.student.overview',
                    'roles' => ['parent-guardian'],
                ],
                [
                    'label' => 'Grades & Progress',
                    'icon' => 'fas fa-chart-line',
                    'route' => 'parent.grades.view',
                    'roles' => ['parent-guardian'],
                ],
                [
                    'label' => 'Attendance',
                    'icon' => 'fas fa-calendar-check',
                    'route' => 'parent.attendance.view',
                    'roles' => ['parent-guardian'],
                ],
                [
                    'label' => 'Financial',
                    'icon' => 'fas fa-dollar-sign',
                    'route' => 'parent.financial.view',
                    'roles' => ['parent-guardian'],
                ],
                [
                    'label' => 'Communication',
                    'icon' => 'fas fa-envelope',
                    'route' => 'parent.messages',
                    'roles' => ['parent-guardian'],
                ],
            ],
        ],

        // ============================================================
        // ADVISOR MENU
        // ============================================================
        [
            'title' => 'Academic Advising',
            'order' => 28,
            'roles' => ['advisor'],
            'items' => [
                [
                    'label' => 'Advisor Dashboard',
                    'icon' => 'fas fa-user-tie',
                    'route' => 'advisor.dashboard',
                    'roles' => ['advisor'],
                ],
                [
                    'label' => 'My Advisees',
                    'icon' => 'fas fa-user-friends',
                    'route' => 'advisor.advisees.index',
                    'roles' => ['advisor'],
                ],
                [
                    'label' => 'Degree Audits',
                    'icon' => 'fas fa-tasks',
                    'route' => 'advisor.degree-audits.index',
                    'roles' => ['advisor'],
                ],
                [
                    'label' => 'Registration Holds',
                    'icon' => 'fas fa-hand-paper',
                    'route' => 'advisor.holds.index',
                    'roles' => ['advisor'],
                ],
                [
                    'label' => 'Academic Plans',
                    'icon' => 'fas fa-project-diagram',
                    'route' => 'advisor.plans.index',
                    'roles' => ['advisor'],
                ],
                [
                    'label' => 'Messages',
                    'icon' => 'fas fa-envelope',
                    'route' => 'advisor.messages',
                    'roles' => ['advisor'],
                ],
            ],
        ],

        // ============================================================
        // AUDITOR MENU
        // ============================================================
        [
            'title' => 'Audit & Compliance',
            'order' => 80,
            'roles' => ['auditor'],
            'items' => [
                [
                    'label' => 'Audit Dashboard',
                    'icon' => 'fas fa-shield-alt',
                    'route' => 'audit.dashboard',
                    'roles' => ['auditor'],
                ],
                [
                    'label' => 'System Logs',
                    'icon' => 'fas fa-file-alt',
                    'route' => 'audit.logs.index',
                    'roles' => ['auditor'],
                ],
                [
                    'label' => 'User Activity',
                    'icon' => 'fas fa-user-clock',
                    'route' => 'audit.activity.index',
                    'roles' => ['auditor'],
                ],
                [
                    'label' => 'Financial Audit',
                    'icon' => 'fas fa-money-check-alt',
                    'route' => 'audit.financial.index',
                    'roles' => ['auditor'],
                ],
                [
                    'label' => 'Academic Audit',
                    'icon' => 'fas fa-graduation-cap',
                    'route' => 'audit.academic.index',
                    'roles' => ['auditor'],
                ],
                [
                    'label' => 'Compliance Reports',
                    'icon' => 'fas fa-file-export',
                    'route' => 'audit.reports.index',
                    'roles' => ['auditor'],
                ],
            ],
        ],

        // ============================================================
        // ALUMNI MENU
        // ============================================================
        [
            'title' => 'Alumni Services',
            'order' => 90,
            'roles' => ['alumni'],
            'items' => [
                [
                    'label' => 'Alumni Dashboard',
                    'icon' => 'fas fa-university',
                    'route' => 'alumni.dashboard',
                    'roles' => ['alumni'],
                ],
                [
                    'label' => 'Request Transcript',
                    'icon' => 'fas fa-file-alt',
                    'route' => 'alumni.transcripts',
                    'roles' => ['alumni'],
                ],
                [
                    'label' => 'Verification Services',
                    'icon' => 'fas fa-certificate',
                    'route' => 'alumni.verification',
                    'roles' => ['alumni'],
                ],
                [
                    'label' => 'Events',
                    'icon' => 'fas fa-calendar-alt',
                    'route' => 'alumni.events',
                    'roles' => ['alumni'],
                ],
                [
                    'label' => 'Giving',
                    'icon' => 'fas fa-hand-holding-heart',
                    'route' => 'alumni.giving',
                    'roles' => ['alumni'],
                ],
                [
                    'label' => 'Update Information',
                    'icon' => 'fas fa-user-edit',
                    'route' => 'alumni.profile.edit',
                    'roles' => ['alumni'],
                ],
            ],
        ],

        // ============================================================
        // STAFF MENU (General staff)
        // ============================================================
        [
            'title' => 'Staff Portal',
            'order' => 45,
            'roles' => ['staff'],
            'items' => [
                [
                    'label' => 'Staff Dashboard',
                    'icon' => 'fas fa-home',
                    'route' => 'staff.dashboard',
                    'roles' => ['staff'],
                ],
                [
                    'label' => 'Student Search',
                    'icon' => 'fas fa-search',
                    'route' => 'staff.students.search',
                    'roles' => ['staff'],
                ],
                [
                    'label' => 'Course Catalog',
                    'icon' => 'fas fa-book',
                    'route' => 'staff.courses.catalog',
                    'roles' => ['staff'],
                ],
                [
                    'label' => 'Calendar',
                    'icon' => 'fas fa-calendar',
                    'route' => 'staff.calendar',
                    'roles' => ['staff'],
                ],
                [
                    'label' => 'Documents',
                    'icon' => 'fas fa-folder',
                    'route' => 'staff.documents',
                    'roles' => ['staff'],
                ],
                [
                    'label' => 'Messages',
                    'icon' => 'fas fa-envelope',
                    'route' => 'staff.messages',
                    'roles' => ['staff'],
                ],
            ],
        ],

        // ============================================================
        // COMMON ITEMS (Available to all authenticated users)
        // ============================================================
        [
            'title' => 'Common',
            'order' => 100,
            'items' => [
                [
                    'label' => 'My Profile',
                    'icon' => 'fas fa-user',
                    'route' => 'profile.edit',
                    'roles' => ['*'], // All authenticated users
                ],
                [
                    'label' => 'Announcements',
                    'icon' => 'fas fa-bullhorn',
                    'route' => 'announcements.index',
                    'roles' => ['*'],
                ],
                [
                    'label' => 'Help & Support',
                    'icon' => 'fas fa-question-circle',
                    'route' => 'help.index',
                    'roles' => ['*'],
                ],
                [
                    'label' => 'Logout',
                    'icon' => 'fas fa-sign-out-alt',
                    'route' => 'logout',
                    'roles' => ['*'],
                ],
            ],
        ],
    ],

    // ============================================================
    // PUBLIC NAVIGATION (For guests/non-authenticated users)
    // ============================================================
    'public' => [
        'navbar' => [
            [
                'title' => null,
                'items' => [
                    [
                        'label' => 'Home',
                        'route' => 'public.home',
                        'icon' => 'fas fa-home',
                    ],
                    [
                        'label' => 'Admissions',
                        'route' => 'admissions.public.index',
                        'icon' => 'fas fa-university',
                    ],
                    [
                        'label' => 'Programs',
                        'route' => 'public.programs',
                        'icon' => 'fas fa-graduation-cap',
                    ],
                    [
                        'label' => 'About',
                        'route' => 'public.about',
                        'icon' => 'fas fa-info-circle',
                    ],
                    [
                        'label' => 'Contact',
                        'route' => 'public.contact',
                        'icon' => 'fas fa-envelope',
                    ],
                    [
                        'label' => 'Login',
                        'route' => 'login',
                        'icon' => 'fas fa-sign-in-alt',
                    ],
                ],
            ],
        ],
    ],

    // ============================================================
    // QUICK ACTIONS (Context-sensitive quick access buttons)
    // ============================================================
    'quick_actions' => [
        'student' => [
            ['label' => 'Register for Classes', 'route' => 'registration.browse', 'icon' => 'fas fa-plus'],
            ['label' => 'View Grades', 'route' => 'student.grades.current', 'icon' => 'fas fa-chart-line'],
            ['label' => 'Make Payment', 'route' => 'financial.student.pay', 'icon' => 'fas fa-dollar-sign'],
        ],
        'faculty' => [
            ['label' => 'Enter Grades', 'route' => 'grades.entry', 'icon' => 'fas fa-edit'],
            ['label' => 'Take Attendance', 'route' => 'faculty.attendance.index', 'icon' => 'fas fa-clipboard-check'],
            ['label' => 'Office Hours', 'route' => 'faculty.office-hours.index', 'icon' => 'fas fa-clock'],
        ],
        'registrar' => [
            ['label' => 'Process Transcript', 'route' => 'registrar.transcripts.pending', 'icon' => 'fas fa-file-alt'],
            ['label' => 'Verify Enrollment', 'route' => 'registrar.enrollment.verification', 'icon' => 'fas fa-check'],
            ['label' => 'Grade Changes', 'route' => 'registrar.grades.changes', 'icon' => 'fas fa-edit'],
        ],
    ],
];