<?php

return [
    'title' => "YPF bSpots - Registro de puntos negros ",
    'template_menu' => [
        [
            'text' => 'Inicio',
            'url' => 'admin::home',
            'can' => 'dashboard-admin',
            'icon' => 'fas fa-tachometer-alt',
        ],
        [
            'text' => 'Búsquedas',
            'url' => 'admin::searches.index',
            'can' => 'dashboard-admin',
            'icon' => 'fab fa-twitter',
        ],
    ],
    'plugins' => [
        'FontAwesome' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css',
                ]
            ],
        ],
        'RemixIcon' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => 'https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css',
                ]
            ],
        ],
        'BootstrapIcons' => [
            'active' => true,
            'files' => [[
                'type' => 'css',
                'asset' => false,
                'location' => 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
            ]],
        ],

//        'GridJs' => [
//            'active' => true,
//            'files' => [
//                [
//                    'type' => 'js',
//                    'asset' => false,
//                    'location' => 'https://unpkg.com/gridjs/dist/gridjs.umd.js',
//                ],
//                [
//                    'type' => 'css',
//                    'asset' => false,
//                    'location' => 'https://unpkg.com/gridjs/dist/theme/mermaid.min.css',
//                ],
//                [
//                    'type' => 'css',
//                    'asset' => true,
//                    'location' => 'plugins/gridjs/dark.css',
//                ],
//            ],
//        ],


        'Tooltips' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => 'https://unpkg.com/@popperjs/core@2',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => 'https://unpkg.com/tippy.js@6',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => 'https://unpkg.com/tippy.js@6/dist/tippy.css',
                ],

            ],
        ],


//        'Modals' => [
//            'active' => true,
//            'files' => [
//
//                [
//                    'type' => 'css',
//                    'asset' => true,
//                    'location' => 'template/src/assets/css/light/components/modal.css',
//                ],
//
//            ],
//        ],
        'Toaster' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.0/js/toastr.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.0/css/toastr.css',
                ],

            ],
        ],
        'css-gridjs' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'plugins/dark_gridjs/css/table.css',
                ],

            ],
        ],
//        'Datatables' => [
//            'active' => true,
//            'files' => [
//                [
//                    'type' => 'js',
//                    'asset' => true,
//                    'location' => 'template/src/plugins/src/table/datatable/datatables.js',
//                ],
//                [
//                    'type' => 'css',
//                    'asset' => true,
//                    'location' => 'template/src/plugins/src/table/datatable/datatables.css',
//                ],
//                [
//                    'type' => 'css',
//                    'asset' => true,
//                    'location' => 'template/src/plugins/css/light/table/datatable/dt-global_style.css',
//                ],
//            ],
//        ],
//        'Select2' => [
//            'active' => true,
//            'files' => [
//                [
//                    'type' => 'js',
//                    'asset' => false,
//                    'location' => 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js',
//                ],
//                [
//                    'type' => 'css',
//                    'asset' => false,
//                    'location' => 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css',
//                ],
//            ],
//        ],
        'Chartjs' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@8',
                ],
            ],
        ],
        'Pace' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],

        'Moment' => [
            'name' => 'Moment',
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => 'https://cdn.jsdelivr.net/momentjs/latest/moment.min.js',
                ]
            ],
        ],
//        'datepicker' => [
//            'name' => 'datepicker',
//            'active' => true,
//            'files' => [
//                [
//                    'type' => 'css',
//                    'asset' => false,
//                    'location' => 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker3.min.css',
//                ],
//                [
//                    'type' => 'js',
//                    'asset' => false,
//                    'location' => 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js',
//                ],
//            ],
//        ],
//        [
//            'name' => 'Colorpicker',
//            'active' => true,
//            'files' => [
//                [
//                    'type' => 'js',
//                    'asset' => false,
//                    'location' => 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/3.2.0/js/bootstrap-colorpicker.min.js',
//                ],
//                [
//                    'type' => 'css',
//                    'asset' => false,
//                    'location' => 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/3.2.0/css/bootstrap-colorpicker.min.css',
//                ],
//            ],
//        ],
        'Colorpicker' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/3.4.0/js/bootstrap-colorpicker.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/3.4.0/css/bootstrap-colorpicker.min.css',
                ],
            ],
        ],

        [
            'name' => 'Moment',
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => 'https://cdn.jsdelivr.net/momentjs/latest/moment.min.js',
                ], [
                    'type' => 'js',
                    'asset' => false,
                    'location' => 'https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.34/moment-timezone-with-data.min.js',
                ],
            ],
        ],


        'DateTimePicker-flatpickr' => [
            'name' => 'flatpickr',
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => 'https://cdn.jsdelivr.net/npm/flatpickr',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css',
                ],
            ],
        ],

//        [
//            'name' => 'DateRangePicker',
//            'active' => true,
//            'files' => [
//                [
//                    'type' => 'js',
//                    'asset' => false,
//                    'location' => 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js',
//                ],
//                [
//                    'type' => 'css',
//                    'asset' => false,
//                    'location' => 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css',
//                ],
//            ],
//        ],
//        [
//            'name' => 'DateRangePicker',
//            'active' => true,
//            'files' => [
//                [
//                    'type' => 'js',
//                    'asset' => false,
//                    'location' => 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/moment.min.js',
//                ],
//                [
//                    'type' => 'js',
//                    'asset' => false,
//                    'location' => 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.js',
//                ],
//                [
//                    'type' => 'css',
//                    'asset' => false,
//                    'location' => 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.css',
//                ],
//            ],
//        ],
//        'JqueryUI' =>[
//            'active' => true,
//            'files' => [
//                [
//                    'type' => 'js',
//                    'asset' => false,
//                    'location' => '//code.jquery.com/ui/1.12.1/jquery-ui.min.js',
//                ],
//                [
//                    'type' => 'css',
//                    'asset' => false,
//                    'location' => '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css',
//                ],
//
//            ],
//        ],
    ],

];
