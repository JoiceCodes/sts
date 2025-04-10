<style>
    /* --- Base Styles --- */
    .availability-available {
        color: #1cc88a;
        font-weight: bold;
    }

    .availability-busy {
        color: #e74a3b;
        font-weight: bold;
    }

    .availability-unknown {
        color: #858796;
        font-style: italic;
    }

    .text-warning {
        color: #f6c23e !important;
    }

    .solved-count-error {
        color: #e74a3b;
        font-style: italic;
    }

    .chart-container {
        position: relative;
        margin: auto;
        height: 250px;
        width: 100%;
        padding: 15px;
    }

    .card-body-chart {
        padding: 0 !important;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .dataTables_wrapper .row:first-child {
        margin-bottom: 0.5rem;
    }

    .dataTables_wrapper .row:last-child {
        margin-top: 0.5rem;
    }

    .dataTables_wrapper .row:has(>.dt-buttons) {
        margin-top: 1rem;
    }

    /* --- Print Styles --- */
    @media print {
        body {
            background-color: #fff !important;
            color: #000 !important;
            margin: 0;
            padding: 0;
            font-size: 10pt;
            width: 100% !important;
        }

        /* --- Hide Sidebar --- */
        /* Add multiple common selectors. Only one needs to match. */
        #accordionSidebar,
        /* Most common SB Admin 2 ID */
        #sidebar,
        /* Alternative ID */
        .sidebar,
        /* Common Class */
        ul.navbar-nav.bg-gradient-primary.sidebar {
            /* More specific selector based on common structure */
            display: none !important;
        }

        /* --- Hide Topbar --- */
        #content-wrapper #topbar,
        nav.navbar.topbar {
            /* Common topbar selectors */
            display: none !important;
        }

        /* --- Hide other non-essential elements --- */
        .btn,
        .modal,
        a.scroll-to-top,
        footer.sticky-footer,
        .dataTables_filter,
        .dataTables_length,
        .dataTables_paginate,
        .dataTables_info,
        .dt-buttons,
        /* Hides the container for CSV/Excel buttons */
        .alert .close,
        .dropdown-menu,
        /* Hide dropdown menus */
        .dropdown-list

        /* Hide dropdown lists (like notifications/user menu) */
            {
            display: none !important;
        }

        /* Ensure main content takes full width */
        #wrapper,
        #content-wrapper,
        #content {
            margin: 0 !important;
            padding: 10px !important;
            width: 100% !important;
            overflow: visible !important;
            background: none !important;
        }

        .container-fluid {
            padding: 0 !important;
            width: 100% !important;
        }

        /* Style cards for printing */
        .card {
            border: 1px solid #ccc !important;
            box-shadow: none !important;
            margin-bottom: 15px !important;
            page-break-inside: avoid;
            width: 100% !important;
        }

        .card-header {
            background-color: #eee !important;
            color: #000 !important;
            border-bottom: 1px solid #ccc !important;
            padding: 5px 10px !important;
        }

        .card-body {
            padding: 10px !important;
        }

        /* Table print styles */
        .table,
        .table th,
        .table td {
            border: 1px solid #666 !important;
            color: #000 !important;
            font-size: 9pt;
        }

        .table thead {
            background-color: #f2f2f2 !important;
            font-weight: bold;
        }

        .table-responsive {
            overflow: visible !important;
        }

        .table th,
        .table td {
            padding: 4px 6px !important;
        }

        /* Links */
        a,
        a:visited {
            text-decoration: none !important;
            color: #000 !important;
        }

        a[href]:after {
            content: none !important;
        }

        /* Charts */
        .chart-container {
            height: 200px;
            width: 98%;
            padding: 5px;
            page-break-inside: avoid;
        }

        canvas {
            max-width: 100%;
        }

        /* Page layout */
        .row>div {
            page-break-inside: avoid;
        }

        h1,
        h3,
        h6 {
            color: #000 !important;
            margin-bottom: 10px;
        }

        .text-primary,
        .text-danger,
        .text-success,
        .text-info {
            color: #000 !important;
        }

        .text-muted {
            color: #333 !important;
        }

        .font-weight-bold {
            font-weight: bold !important;
        }

        .fas.fa-star,
        .fas.fa-star-half-alt,
        .far.fa-star {
            color: #555 !important;
        }

        .alert {
            border: 1px solid #ccc;
            background-color: #f9f9f9;
            color: #000;
            padding: 5px;
            margin-bottom: 10px;
        }

        /* Page setup */
        @page {
            size: A4;
            margin: 0.75in;
        }
    }
</style>