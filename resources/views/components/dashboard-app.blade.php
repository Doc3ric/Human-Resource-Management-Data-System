<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'HDMS- Human Resource Data Management System') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="{{ asset('css/theme-default.css') }}">
    <link rel="stylesheet" href="{{ asset('css/emerald-night.css') }}">
    <link rel="stylesheet" href="{{ asset('css/theme-financial.css') }}">
    <link rel="stylesheet" href="{{ asset('css/theme-corona.css') }}">
    <style>
        /* â”€â”€ Flatpickr Premium Overrides â”€â”€ */
        .flatpickr-calendar {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
            border-radius: 16px !important;
            border: 1px solid #e5e7eb !important;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .12), 0 8px 20px rgba(0, 0, 0, .06) !important;
            overflow: hidden !important;
            padding: 4px !important;
        }

        .flatpickr-months {
            background: linear-gradient(135deg, #1d4ed8, #2563eb) !important;
            padding: 10px 6px 8px !important;
            border-radius: 12px 12px 0 0 !important;
        }

        .flatpickr-months .flatpickr-month,
        .flatpickr-months .flatpickr-prev-month,
        .flatpickr-months .flatpickr-next-month {
            color: #fff !important;
            fill: #fff !important;
        }

        .flatpickr-months .flatpickr-prev-month:hover,
        .flatpickr-months .flatpickr-next-month:hover {
            background: rgba(255, 255, 255, 0.15) !important;
            border-radius: 8px !important;
        }

        .flatpickr-months .flatpickr-prev-month:hover svg,
        .flatpickr-months .flatpickr-next-month:hover svg {
            fill: #fff !important;
        }

        .flatpickr-current-month {
            color: #fff !important;
            font-size: 1rem !important;
            font-weight: 700 !important;
        }

        .flatpickr-current-month .numInputWrapper:hover,
        .flatpickr-current-month .flatpickr-monthDropdown-months:hover {
            background: rgba(255, 255, 255, .15) !important;
            border-radius: 6px !important;
        }

        .flatpickr-current-month input.cur-year,
        .flatpickr-current-month .flatpickr-monthDropdown-months {
            color: #fff !important;
            font-weight: 700 !important;
        }

        /* Fix: month dropdown options must have dark text on light bg (browser-native select) */
        .flatpickr-monthDropdown-months {
            background-color: #1d4ed8 !important;
            color: #fff !important;
        }

        .flatpickr-monthDropdown-months option {
            background-color: #ffffff !important;
            color: #111827 !important;
            font-weight: 500 !important;
        }

        .flatpickr-weekdays {
            background: #eff6ff !important;
            padding: 6px 0 4px !important;
        }

        .flatpickr-weekday {
            color: #2563eb !important;
            font-weight: 700 !important;
            font-size: 0.7rem !important;
            text-transform: uppercase !important;
        }

        .flatpickr-day {
            border-radius: 8px !important;
            font-size: 0.8125rem !important;
            font-weight: 500 !important;
            color: #374151 !important;
            transition: all 0.15s ease !important;
        }

        .flatpickr-day:hover {
            background: #dbeafe !important;
            border-color: #dbeafe !important;
            color: #1d4ed8 !important;
        }

        .flatpickr-day.today {
            border-color: #2563eb !important;
            font-weight: 700 !important;
            color: #2563eb !important;
        }

        .flatpickr-day.today:hover {
            background: #2563eb !important;
            color: #fff !important;
        }

        .flatpickr-day.selected,
        .flatpickr-day.selected:hover {
            background: linear-gradient(135deg, #1d4ed8, #2563eb) !important;
            border-color: #2563eb !important;
            color: #fff !important;
            font-weight: 700 !important;
            box-shadow: 0 4px 12px rgba(37, 99, 235, .35) !important;
        }

        .flatpickr-day.flatpickr-disabled,
        .flatpickr-day.prevMonthDay,
        .flatpickr-day.nextMonthDay {
            color: #d1d5db !important;
        }

        /* Input styling when flatpickr is active */
        input.flatpickr-input {
            cursor: pointer !important;
        }

        input.flatpickr-input.active {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
        }

        /* â”€â”€ Global Page Loader â”€â”€ */
        #global-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: transparent;
            z-index: 999999;
            overflow: hidden;
            display: none;
        }
        #global-loader.active {
            display: block;
        }
        #global-loader::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, #3b82f6, #93c5fd, #2563eb, transparent);
            animation: loader-slide 1.5s infinite ease-in-out;
        }
        @keyframes loader-slide {
            0% { left: -100%; }
            100% { left: 100%; }
        }
    </style>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
        }


        .sidebar {
            background-color: #ffffff;
            min-height: 100vh;
            padding: 0;
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            box-shadow: 2px 0 8px rgba(0, 0, 0, .05);
            display: flex;
            flex-direction: column;
            border-right: 1px solid #e5e7eb;
        }

        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .sidebar-logo {
            width: 54px;
            height: 54px;
            object-fit: contain;
            border-radius: 12px;
            flex-shrink: 0;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.12);
            background: #ffffff;
            padding: 3px;
            transition: transform 0.2s ease;
        }

        .sidebar-logo:hover {
            transform: scale(1.06);
        }

        .sidebar-title-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .sidebar-title-main {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            color: #1e293b;
            font-size: 17px;
            font-weight: 700;
            letter-spacing: -0.2px;
            line-height: 1.25;
        }

        .sidebar-title-sub {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            color: #2563eb;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-top: 3px;
        }

        .sidebar-nav {
            flex: 1;
            padding: 14px 0;
            overflow-y: auto;
        }

        .nav-item {
            margin: 6px 10px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            color: #4b5563;
            text-decoration: none;
            border-radius: 8px;
            transition: all .25s;
            font-size: 14px;
            font-weight: 500;
        }

        .nav-link:hover {
            color: #111827;
            background-color: #f3f4f6;
        }

        .nav-link.active {
            background-color: #eff6ff;
            color: #2563eb;
            font-weight: 600;
        }

        .nav-icon {
            font-size: 18px;
            display: flex;
            align-items: center;
            flex-shrink: 0;
        }

        .nav-section {
            padding: 8px 24px 4px;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #9ca3af;
            font-weight: 600;
        }

        .sidebar-footer {
            padding: 14px 10px;
            border-top: 1px solid #e5e7eb;
        }

        .main-content {
            margin-left: 280px;
            min-height: 100vh;
        }

        .topbar {
            background-color: #ffffff;
            color: #111827;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .05);
            border-bottom: 1px solid #e5e7eb;
        }

        .page-content {
            padding: 24px;
        }

        /* Pagination fix - Bootstrap works here since we load Bootstrap */
        .pagination .page-item.active .page-link {
            background-color: #2563eb;
            border-color: #2563eb;
            color: #ffffff;
        }

        .pagination .page-link {
            color: #2563eb;
        }

        /* â”€â”€ Premium Logout Modal â”€â”€ */
        .logout-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .55);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            animation: fadeInBg .2s ease;
        }

        .logout-overlay.active {
            display: flex;
        }

        @keyframes fadeInBg {
            from {
                opacity: 0
            }

            to {
                opacity: 1
            }
        }

        .logout-modal-card {
            background: #fff;
            border-radius: 20px;
            padding: 0;
            max-width: 420px;
            width: calc(100% - 32px);
            box-shadow: 0 25px 60px rgba(0, 0, 0, .18), 0 8px 20px rgba(0, 0, 0, .08);
            position: relative;
            overflow: hidden;
            animation: slideUp .3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px) scale(.95)
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1)
            }
        }

        .logout-modal-top {
            background: linear-gradient(135deg, #fff1f2, #ffe4e6);
            padding: 36px 32px 24px;
            text-align: center;
            border-bottom: 1px solid #fecdd3;
        }

        .logout-modal-icon-wrap {
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
            box-shadow: 0 8px 24px rgba(220, 38, 38, .3);
            animation: pulse-red 2s infinite;
        }

        @keyframes pulse-red {

            0%,
            100% {
                box-shadow: 0 8px 24px rgba(220, 38, 38, .30)
            }

            50% {
                box-shadow: 0 8px 32px rgba(220, 38, 38, .55)
            }
        }

        .logout-modal-icon-wrap i {
            font-size: 30px;
            color: #fff;
        }

        .logout-modal-title {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 6px;
            letter-spacing: -.3px;
        }

        .logout-modal-subtitle {
            font-size: 13px;
            color: #64748b;
            margin: 0;
            line-height: 1.6;
        }

        .logout-modal-body {
            padding: 22px 28px 28px;
        }

        .logout-modal-info {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            background: #fef9c3;
            border: 1px solid #fde68a;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 22px;
            font-size: 12.5px;
            color: #854d0e;
            line-height: 1.5;
        }

        .logout-modal-info i {
            font-size: 15px;
            color: #ca8a04;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .logout-modal-actions {
            display: flex;
            gap: 10px;
        }

        .logout-btn-cancel {
            flex: 1;
            padding: 12px 16px;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            font-size: 13.5px;
            font-weight: 600;
            color: #475569;
            background: #f8fafc;
            cursor: pointer;
            transition: all .2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .logout-btn-cancel:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
            color: #0f172a;
        }

        .logout-btn-confirm {
            flex: 1;
            padding: 12px 16px;
            border: none;
            border-radius: 12px;
            font-size: 13.5px;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            cursor: pointer;
            transition: all .2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            box-shadow: 0 4px 14px rgba(220, 38, 38, .3);
        }

        .logout-btn-confirm:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            box-shadow: 0 6px 20px rgba(220, 38, 38, .45);
            transform: translateY(-1px);
        }

        .logout-btn-confirm:active {
            transform: translateY(0);
        }

        .logout-modal-close {
            position: absolute;
            top: 12px;
            right: 14px;
            width: 28px;
            height: 28px;
            background: rgba(0, 0, 0, .06);
            border: none;
            border-radius: 50%;
            color: #64748b;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all .2s;
        }

        .logout-modal-close:hover {
            background: rgba(0, 0, 0, .12);
            color: #0f172a;
        }

        /* Tailwind-ish utilities we use in our new views */
        .space-y-6>*+* {
            margin-top: 1.5rem;
        }

        .space-y-4>*+* {
            margin-top: 1rem;
        }

        .space-y-2>*+* {
            margin-top: .5rem;
        }

        .flex {
            display: flex;
        }

        .flex-1 {
            flex: 1;
        }

        .flex-wrap {
            flex-wrap: wrap;
        }

        .items-center {
            align-items: center;
        }

        .items-start {
            align-items: flex-start;
        }

        .justify-between {
            justify-content: space-between;
        }

        .justify-end {
            justify-content: flex-end;
        }

        .justify-center {
            justify-content: center;
        }

        .gap-1 {
            gap: .25rem;
        }

        .gap-2 {
            gap: .5rem;
        }

        .gap-3 {
            gap: .75rem;
        }

        .gap-4 {
            gap: 1rem;
        }

        .gap-6 {
            gap: 1.5rem;
        }

        .grid {
            display: grid;
        }

        .grid-cols-1 {
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }

        .grid-cols-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .grid-cols-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .col-span-2 {
            grid-column: span 2;
        }

        .hidden {
            display: none !important;
        }

        .block {
            display: block;
        }

        .inline-flex {
            display: inline-flex;
        }

        .inline-block {
            display: inline-block;
        }

        .overflow-hidden {
            overflow: hidden;
        }

        .overflow-x-auto {
            overflow-x: auto;
        }

        .overflow-y-auto {
            overflow-y: auto;
        }

        .w-full {
            width: 100%;
        }

        .w-4 {
            width: 1rem;
        }

        .w-5 {
            width: 1.25rem;
        }

        .w-6 {
            width: 1.5rem;
        }

        .w-8 {
            width: 2rem;
        }

        .w-12 {
            width: 3rem;
        }

        .h-4 {
            height: 1rem;
        }

        .h-5 {
            height: 1.25rem;
        }

        .h-6 {
            height: 1.5rem;
        }

        .h-8 {
            height: 2rem;
        }

        .h-12 {
            height: 3rem;
        }

        .max-w-xs {
            max-width: 20rem;
        }

        .max-w-4xl {
            max-width: 56rem;
            margin-left: auto;
            margin-right: auto;
        }

        .max-w-5xl {
            max-width: 64rem;
            margin-left: auto;
            margin-right: auto;
        }

        .max-w-2xl {
            max-width: 42rem;
            margin-left: auto;
            margin-right: auto;
        }

        .min-w-48 {
            min-width: 12rem;
        }

        .min-w-44 {
            min-width: 11rem;
        }

        .mx-auto {
            margin-left: auto;
            margin-right: auto;
        }

        .mt-1 {
            margin-top: .25rem;
        }

        .mt-2 {
            margin-top: .5rem;
        }

        .mt-4 {
            margin-top: 1rem;
        }

        .mt-3 {
            margin-top: .75rem;
        }

        .mt-auto {
            margin-top: auto;
        }

        .mb-1 {
            margin-bottom: .25rem;
        }

        .mb-2 {
            margin-bottom: .5rem;
        }

        .mb-3 {
            margin-bottom: .75rem;
        }

        .mb-4 {
            margin-bottom: 1rem;
        }

        .ml-1 {
            margin-left: .25rem;
        }

        .p-3 {
            padding: .75rem;
        }

        .p-4 {
            padding: 1rem;
        }

        .p-5 {
            padding: 1.25rem;
        }

        .p-6 {
            padding: 1.5rem;
        }

        .p-8 {
            padding: 2rem;
        }

        .px-2 {
            padding-left: .5rem;
            padding-right: .5rem;
        }

        .px-3 {
            padding-left: .75rem;
            padding-right: .75rem;
        }

        .px-4 {
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .px-5 {
            padding-left: 1.25rem;
            padding-right: 1.25rem;
        }

        .px-6 {
            padding-left: 1.5rem;
            padding-right: 1.5rem;
        }

        .py-0\.5 {
            padding-top: .125rem;
            padding-bottom: .125rem;
        }

        .py-1 {
            padding-top: .25rem;
            padding-bottom: .25rem;
        }

        .py-1\.5 {
            padding-top: .375rem;
            padding-bottom: .375rem;
        }

        .py-2 {
            padding-top: .5rem;
            padding-bottom: .5rem;
        }

        .py-3 {
            padding-top: .75rem;
            padding-bottom: .75rem;
        }

        .py-4 {
            padding-top: 1rem;
            padding-bottom: 1rem;
        }

        .py-8 {
            padding-top: 2rem;
            padding-bottom: 2rem;
        }

        .py-12 {
            padding-top: 3rem;
            padding-bottom: 3rem;
        }

        .pt-2 {
            padding-top: .5rem;
        }

        .pt-3 {
            padding-top: .75rem;
        }

        .pt-4 {
            padding-top: 1rem;
        }

        .pt-6 {
            padding-top: 1.5rem;
        }

        .pb-2 {
            padding-bottom: .5rem;
        }

        .rounded {
            border-radius: .25rem;
        }

        .rounded-lg {
            border-radius: .5rem;
        }

        .rounded-xl {
            border-radius: .75rem;
        }

        .rounded-full {
            border-radius: 9999px;
        }

        .border {
            border-width: 1px;
            border-style: solid;
        }

        .border-t {
            border-top-width: 1px;
            border-top-style: solid;
        }

        .border-b {
            border-bottom-width: 1px;
            border-bottom-style: solid;
        }

        .border-2 {
            border-width: 2px;
            border-style: solid;
        }

        .border-dashed {
            border-style: dashed;
        }

        .border-gray-100 {
            border-color: #f3f4f6;
        }

        .border-gray-200 {
            border-color: #e5e7eb;
        }

        .border-gray-300 {
            border-color: #d1d5db;
        }

        .border-b-2 {
            border-bottom-width: 2px;
            border-bottom-style: solid;
        }

        .border-transparent {
            border-color: transparent;
        }

        .border-red-500 {
            border-color: #ef4444;
        }

        .border-amber-500 {
            border-color: #f59e0b;
        }

        .border-blue-400 {
            border-color: #60a5fa;
        }

        .border-blue-500 {
            border-color: #3b82f6;
        }

        .border-blue-200 {
            border-color: #bfdbfe;
        }

        .border-red-200 {
            border-color: #fecaca;
        }

        .border-green-200 {
            border-color: #bbf7d0;
        }

        .border-amber-200 {
            border-color: #fde68a;
        }

        .border-l-5 {
            border-left-width: 5px;
            border-left-style: solid;
        }

        .shadow-sm {
            box-shadow: 0 1px 2px rgba(0, 0, 0, .05);
        }

        .text-xs {
            font-size: .75rem;
            line-height: 1rem;
        }

        .text-sm {
            font-size: .875rem;
            line-height: 1.25rem;
        }

        .text-base {
            font-size: 1rem;
        }

        .text-lg {
            font-size: 1.125rem;
        }

        .text-xl {
            font-size: 1.25rem;
        }

        .text-2xl {
            font-size: 1.5rem;
        }

        .text-3xl {
            font-size: 1.875rem;
        }

        .text-4xl {
            font-size: 2.25rem;
        }

        .font-medium {
            font-weight: 500;
        }

        .font-semibold {
            font-weight: 600;
        }

        .font-bold {
            font-weight: 700;
        }

        .font-mono {
            font-family: ui-monospace, monospace;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .text-white {
            color: #fff;
        }

        .text-gray-400 {
            color: #9ca3af;
        }

        .text-gray-500 {
            color: #6b7280;
        }

        .text-gray-600 {
            color: #4b5563;
        }

        .text-gray-700 {
            color: #374151;
        }

        .text-gray-800 {
            color: #1f2937;
        }

        .text-blue-600 {
            color: #2563eb;
        }

        .text-blue-700 {
            color: #1d4ed8;
        }

        .text-blue-800 {
            color: #1e40af;
        }

        .text-red-600 {
            color: #dc2626;
        }

        .text-red-700 {
            color: #b91c1c;
        }

        .text-red-800 {
            color: #991b1b;
        }

        .text-green-600 {
            color: #16a34a;
        }

        .text-green-700 {
            color: #15803d;
        }

        .text-green-800 {
            color: #166534;
        }

        .text-amber-600 {
            color: #d97706;
        }

        .text-amber-700 {
            color: #b45309;
        }

        .text-amber-800 {
            color: #92400e;
        }

        .text-indigo-600 {
            color: #4f46e5;
        }

        .text-purple-600 {
            color: #9333ea;
        }

        .text-orange-700 {
            color: #c2410c;
        }

        .text-yellow-700 {
            color: #a16207;
        }

        .opacity-40 {
            opacity: .4;
        }

        .opacity-60 {
            opacity: .6;
        }

        .opacity-70 {
            opacity: .7;
        }

        .opacity-75 {
            opacity: .75;
        }

        .opacity-80 {
            opacity: .8;
        }

        .opacity-90 {
            opacity: .9;
        }

        .italic {
            font-style: italic;
        }

        .truncate {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .transition {
            transition: all .2s;
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .list-disc {
            list-style-type: disc;
        }

        .list-inside {
            list-style-position: inside;
        }

        .list-decimal {
            list-style-type: decimal;
        }

        .max-h-48 {
            max-height: 12rem;
        }

        .divide-y>*+* {
            border-top-width: 1px;
            border-top-style: solid;
            border-top-color: #f3f4f6;
        }

        /* Backgrounds */
        .bg-white {
            background-color: #fff;
        }

        .bg-gray-50 {
            background-color: #f9fafb;
        }

        .bg-gray-100 {
            background-color: #f3f4f6;
        }

        .bg-gray-200 {
            background-color: #e5e7eb;
        }

        .bg-blue-50 {
            background-color: #eff6ff;
        }

        .bg-blue-100 {
            background-color: #dbeafe;
        }

        .bg-blue-500 {
            background-color: #3b82f6;
        }

        .bg-blue-600 {
            background-color: #2563eb;
        }

        .bg-blue-700 {
            background-color: #1d4ed8;
        }

        .bg-red-50 {
            background-color: #fef2f2;
        }

        .bg-red-100 {
            background-color: #fee2e2;
        }

        .bg-red-500 {
            background-color: #ef4444;
        }

        .bg-red-600 {
            background-color: #dc2626;
        }

        .bg-red-700 {
            background-color: #b91c1c;
        }

        .bg-green-50 {
            background-color: #f0fdf4;
        }

        .bg-green-100 {
            background-color: #dcfce7;
        }

        .bg-green-600 {
            background-color: #16a34a;
        }

        .bg-green-700 {
            background-color: #15803d;
        }

        .bg-amber-50 {
            background-color: #fffbeb;
        }

        .bg-amber-100 {
            background-color: #fef3c7;
        }

        .bg-amber-500 {
            background-color: #f59e0b;
        }

        .bg-amber-600 {
            background-color: #d97706;
        }

        .bg-indigo-600 {
            background-color: #4f46e5;
        }

        .bg-indigo-700 {
            background-color: #4338ca;
        }

        .bg-purple-100 {
            background-color: #f3e8ff;
        }

        .bg-purple-600 {
            background-color: #9333ea;
        }

        .bg-orange-100 {
            background-color: #ffedd5;
        }

        .bg-yellow-100 {
            background-color: #fef9c3;
        }

        .hover\:bg-gray-50:hover {
            background-color: #f9fafb;
        }

        .hover\:bg-gray-100:hover {
            background-color: #f3f4f6;
        }

        .hover\:bg-gray-200:hover {
            background-color: #e5e7eb;
        }

        .hover\:bg-blue-50:hover {
            background-color: #eff6ff;
        }

        .hover\:bg-blue-700:hover {
            background-color: #1d4ed8;
        }

        .hover\:bg-amber-50:hover {
            background-color: #fffbeb;
        }

        .hover\:bg-amber-600:hover {
            background-color: #d97706;
        }

        .hover\:bg-red-50:hover {
            background-color: #fef2f2;
        }

        .hover\:bg-red-600:hover {
            background-color: #dc2626;
        }

        .hover\:bg-green-700:hover {
            background-color: #15803d;
        }

        .hover\:bg-indigo-700:hover {
            background-color: #4338ca;
        }

        .hover\:text-gray-600:hover {
            color: #4b5563;
        }

        .hover\:text-gray-700:hover {
            color: #374151;
        }

        .hover\:text-white:hover {
            color: #fff;
        }

        .hover\:border-gray-300:hover {
            border-color: #d1d5db;
        }

        .hover\:border-blue-400:hover {
            border-color: #60a5fa;
        }

        .hover\:underline:hover {
            text-decoration: underline;
        }

        .focus\:ring-2:focus {
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .3);
        }

        .focus\:border-blue-500:focus {
            border-color: #3b82f6;
        }

        .outline-none {
            outline: none;
        }

        /* Tables */
        table {
            border-collapse: collapse;
        }

        .table-cell {
            display: table-cell;
        }

        /* -mb-px */
        .-mb-px {
            margin-bottom: -1px;
        }

        @media (min-width: 768px) {
            .md\:table-cell {
                display: table-cell !important;
            }
        }

        @media (min-width: 1024px) {
            .lg\:col-span-1 {
                grid-column: span 1;
            }

            .lg\:col-span-2 {
                grid-column: span 2;
            }

            .lg\:table-cell {
                display: table-cell !important;
            }

            .grid-cols-1 {
                grid-template-columns: repeat(1, minmax(0, 1fr));
            }

            .sm\:grid-cols-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .sm\:grid-cols-3 {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .lg\:grid-cols-3 {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (min-width: 1280px) {
            .xl\:table-cell {
                display: table-cell !important;
            }
        }

        @media (min-width: 640px) {
            .sm\:grid-cols-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .sm\:grid-cols-3 {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .sm\:grid-cols-4 {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        /* â”€â”€ SweetAlert2 Custom Premium Styling â”€â”€ */
        .swal2-popup.custom-swal {
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            border-top: 4px solid #3b82f6;
        }

        .swal2-icon.custom-swal-icon {
            border: none;
            background: #eff6ff;
            color: #2563eb;
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem auto;
        }

        .swal2-title.custom-swal-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: #111827;
            margin-bottom: 0.5rem;
        }

        .swal2-html-container.custom-swal-text {
            font-size: 0.95rem;
            color: #6b7280;
            line-height: 1.5;
            margin: 0 0 2rem 0;
        }

        .swal2-actions.custom-swal-actions {
            margin-top: 0;
            gap: 12px;
            width: 100%;
        }

        .swal2-confirm.custom-swal-confirm {
            background-color: #2563eb !important;
            color: white !important;
            border-radius: 8px !important;
            padding: 12px 24px !important;
            font-weight: 600 !important;
            font-size: 0.95rem !important;
            flex: 1 !important;
            box-shadow: 0 2px 10px rgba(37, 99, 235, 0.2) !important;
            transition: all 0.2s !important;
        }

        .swal2-confirm.custom-swal-confirm:hover {
            background-color: #1d4ed8 !important;
            transform: translateY(-1px);
        }

        .swal2-cancel.custom-swal-cancel {
            background-color: white !important;
            color: #4b5563 !important;
            border: 1px solid #d1d5db !important;
            border-radius: 8px !important;
            padding: 12px 24px !important;
            font-weight: 600 !important;
            font-size: 0.95rem !important;
            flex: 1 !important;
            transition: all 0.2s !important;
        }

        .swal2-cancel.custom-swal-cancel:hover {
            background-color: #f9fafb !important;
            color: #111827 !important;
        }

        /* Footer text area to match the image visually */
        .swal2-footer.custom-swal-footer {
            border-top: 1px solid #f3f4f6;
            margin-top: 2rem;
            padding-top: 1.5rem;
            color: #9ca3af;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
    </style>
</head>

<body>
    <div id="global-loader"></div>
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="{{ asset('img/phrmologo.png') }}" alt="PHRMO Logo" class="sidebar-logo"
                onerror="this.style.display='none'">
            <div class="sidebar-title-container">
                <span class="sidebar-title-main" style="line-height: 1.3; font-size: 14.5px; padding-top: 2px;">Human Resource<br>Data Management<br>System</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-item">
                <a href="{{ route('dashboard') }}"
                    class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-house-door"></i>
                    <span>Dashboard</span>
                </a>
            </div>

            @if(auth()->check() && (auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin()))
                <div class="nav-item">
                    <a href="{{ route('all-data.index') }}"
                        class="nav-link {{ request()->routeIs('all-data.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-table"></i>
                        <span>All Data</span>
                    </a>
                </div>
            @endif

            @if(auth()->check() && (auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin()))
                <div class="nav-item">
                    <a href="{{ route('plantilla.index') }}"
                        class="nav-link {{ request()->routeIs('plantilla.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-person-badge"></i>
                        <span>Inventory of Personnel</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a href="{{ route('job-orders.index') }}"
                        class="nav-link {{ request()->routeIs('job-orders.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-file-earmark-person"></i>
                        <span>Job Orders</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a href="{{ route('casual.index') }}"
                        class="nav-link {{ request()->routeIs('casual.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-person-lines-fill"></i>
                        <span>Casual</span>
                    </a>
                </div>

                @if(Route::has('permanent.index'))
                    <div class="nav-item">
                        <a href="{{ route('permanent.index') }}"
                            class="nav-link {{ request()->routeIs('permanent.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-person-badge-fill"></i>
                            <span>Permanent</span>
                        </a>
                    </div>
                @endif
            @endif

            @if(auth()->check() && (auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin() || auth()->user()->isSalaryAdmin()))
                <div class="nav-item">
                    <a href="{{ route('retirement.index') }}"
                        class="nav-link {{ request()->routeIs('retirement.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-clock-history"></i>
                        <span>Retirement</span>
                        @php
                            $retCount = \App\Models\PlantillaRecord::retirementDue()->count();
                        @endphp
                        @if($retCount > 0)
                            <span
                                style="margin-left:auto; background:#dc3545; color:white; font-size:10px; font-weight:bold; padding:2px 8px; border-radius:99px;">
                                {{ $retCount }}
                            </span>
                        @endif
                    </a>
                </div>
            @endif

            @if(auth()->check() && (auth()->user()->isSuperAdmin() || auth()->user()->isSalaryAdmin()))
                <div class="nav-item">
                    <a href="{{ route('step-increment.hub') }}"
                        class="nav-link {{ request()->routeIs('step-increment.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-building-fill-gear"></i>
                        <span>Plantilla of Personnel</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a href="{{ route('salary-grades.index') }}"
                        class="nav-link {{ request()->routeIs('salary-grades.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-cash-coin"></i>
                        <span>Salary Grades</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('salary-schedules.index') }}"
                        class="nav-link {{ request()->routeIs('salary-schedules.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-layers"></i>
                        <span>SSL Schedules</span>
                    </a>
                </div>
            @endif

            @if(auth()->check() && auth()->user()->isSuperAdmin())
                <div class="nav-item">
                    <a href="{{ route('imports.index') }}"
                        class="nav-link {{ request()->routeIs('imports.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-file-earmark-spreadsheet"></i>
                        <span>Import Data</span>
                    </a>
                </div>
            @endif

            <div class="nav-item">
                <a href="{{ route('profile.edit') }}"
                    class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-person-circle"></i>
                    <span>Profile</span>
                </a>
            </div>

            @if(auth()->check() && (auth()->user()->isSuperAdmin() || auth()->user()->isInventoryAdmin()))
                @if(Route::has('users.index'))
                    <div class="nav-item">
                        <a href="{{ route('users.index') }}"
                            class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-people-fill"></i>
                            <span>User Management</span>
                        </a>
                    </div>
                @endif
                <div class="nav-item">
                    <a href="{{ route('audit-logs.index') }}"
                        class="nav-link {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-shield-lock"></i>
                        <span>Audit Trail</span>
                    </a>
                </div>
            @endif
        </nav>

        <div class="sidebar-footer">
            <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">
                @csrf
            </form>
            <button onclick="openLogoutModal()" class="nav-link"
                style="width:100%;border:none;background:none;cursor:pointer;text-align:left;">
                <i class="nav-icon bi bi-box-arrow-right"></i>
                <span>Log Out</span>
            </button>
        </div>
    </div>

    <!-- Premium Logout Modal -->
    <div id="logoutModal" class="logout-overlay">
        <div class="logout-modal-card">
            <button type="button" class="logout-modal-close" onclick="closeLogoutModal()" title="Close">
                <i class="bi bi-x-lg"></i>
            </button>
            <div class="logout-modal-top">
                <div class="logout-modal-icon-wrap">
                    <i class="bi bi-box-arrow-right"></i>
                </div>
                <h2 class="logout-modal-title">Sign Out?</h2>
                <p class="logout-modal-subtitle">You're about to leave the HDMS- Human Resource Data Management System portal.</p>
            </div>
            <div class="logout-modal-body">
                <div class="logout-modal-info">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span>Any unsaved changes will be lost. Make sure you've saved your work before signing out.</span>
                </div>
                <div class="logout-modal-actions">
                    <button type="button" class="logout-btn-cancel" onclick="closeLogoutModal()">
                        <i class="bi bi-arrow-left"></i> Stay
                    </button>
                    <button type="button" class="logout-btn-confirm"
                        onclick="document.getElementById('logout-form').submit()">
                        <i class="bi bi-box-arrow-right"></i> Yes, Sign Out
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="topbar">
            <div style="font-weight:600;font-size:15px;">
                @if(auth()->check())
                    {{ auth()->user()->name }}
                    <span style="font-size:12px;opacity:.7;margin-left:8px;">
                        {{ auth()->user()->role_label }}
                    </span>
                @endif
            </div>
            <div style="display: flex; align-items: center; gap: 1rem;">
                <form action="{{ route('search') }}" method="GET"
                    style="display: flex; align-items: center; background: #f1f5f9; border-radius: 99px; padding: 4px 16px; border: 1px solid #e2e8f0;">
                    <i class="bi bi-search" style="color: #64748b; font-size: 14px;"></i>
                    <input type="text" name="q" placeholder="Global search..." value="{{ request('q') }}"
                        style="background: transparent; border: none; outline: none; padding: 6px 10px; font-size: 14px; color: #334155; width: 200px;">
                </form>
                <div class="dropdown">
                    <button class="dropdown-toggle"
                        style="background: none; border: none; color: inherit; font-size: 1.3rem; cursor: pointer; position: relative; display: flex; align-items: center;"
                        type="button" data-bs-toggle="dropdown" aria-expanded="false"
                        onclick="markNotificationsAsRead(this)">
                        <i class="bi bi-bell"></i>
                        @if(isset($unreadNotifCount) && $unreadNotifCount > 0)
                            <span class="notif-badge"
                                style="position: absolute; top: -6px; right: -6px; background-color: #ff4444; color: white; border-radius: 50%; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: bold;">{{ $unreadNotifCount }}</span>
                        @endif
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow" style="width: 300px; padding: 0; z-index: 1050;">
                        <li class="p-3 border-bottom bg-light fw-bold text-dark" style="font-size: 14px;">Recent
                            Activity</li>
                        @forelse($recentActivity ?? [] as $log)
                            @php $isVacated = $log->action === 'Vacated Position'; @endphp
                            <li>
                                <a class="dropdown-item py-2 border-bottom" href="{{ route('audit-logs.index') }}"
                                    style="white-space: normal; line-height: 1.4; {{ $isVacated ? 'border-left: 3px solid #f97316; padding-left: 10px;' : '' }}">
                                    <div class="fw-bold" style="font-size: 13px; {{ $isVacated ? 'color:#c2410c;' : '' }}">
                                        @if($isVacated)<i class="bi bi-person-dash me-1"></i>@endif
                                        {{ $log->user ? $log->user->name : 'System' }}
                                        <span style="font-size:11px;font-weight:400;margin-left:4px;">&mdash;
                                            {{ $log->action }}</span>
                                    </div>
                                    <div class="text-muted" style="font-size: 12px; margin-top: 2px;">
                                        {{ \Illuminate\Support\Str::limit($log->description, 80) }}
                                    </div>
                                    <div class="text-muted mt-1" style="font-size: 10px;">
                                        {{ $log->created_at->diffForHumans() }}
                                    </div>
                                </a>
                            </li>
                        @empty
                            <li class="p-3 text-center text-muted" style="font-size: 13px;">No recent activity</li>
                        @endforelse
                        <li class="text-center bg-light">
                            <a class="dropdown-item py-2 fw-bold text-primary" href="{{ route('audit-logs.index') }}"
                                style="font-size: 13px;">View All Activity Menu</a>
                        </li>
                    </ul>
                </div>

                <div class="dropdown">
                    <button class="theme-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"
                        style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; padding: 0.4rem 0.8rem; border-radius: 0.5rem; display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-weight: 500; font-size: 13px; height: fit-content; transition: all 0.2s;">
                        <i class="bi bi-palette"></i> <span>Theme</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li>
                            <h6 class="dropdown-header">Appearance</h6>
                        </li>
                        <li><a class="dropdown-item d-flex align-items-center" href="#"
                                onclick="setTheme('default'); return false;"><i class="bi bi-sun me-2"></i> Default
                                Light</a></li>
                        <li><a class="dropdown-item d-flex align-items-center" href="#"
                                onclick="setTheme('emerald-night'); return false;"><i class="bi bi-moon-stars me-2"></i>
                                Emerald Night</a></li>
                        <li><a class="dropdown-item d-flex align-items-center" href="#"
                                onclick="setTheme('theme-financial'); return false;"><i
                                    class="bi bi-graph-up-arrow me-2"></i> Financial (Teal)</a></li>
                        <li><a class="dropdown-item d-flex align-items-center" href="#"
                                onclick="setTheme('theme-corona'); return false;"><i class="bi bi-moon me-2"></i> Corona
                                (Dark)</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="page-content">
            {{ $slot }}
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        // â”€â”€ Global Flatpickr Init: applies beautiful calendar to ALL date inputs â”€â”€
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('input[type="date"]').forEach(function (input) {
                // Preserve the current value
                var currentValue = input.value;

                flatpickr(input, {
                    dateFormat: 'Y-m-d',       // keeps the same format Laravel expects
                    defaultDate: currentValue || null,
                    allowInput: true,           // allows typing directly
                    disableMobile: false,       // use native on mobile
                    animate: true,
                    monthSelectorType: 'dropdown',
                    onReady: function (selectedDates, dateStr, instance) {
                        // Ensure the value attribute is set for form submission
                        if (currentValue) instance.setDate(currentValue, false);
                    }
                });
            });
        });
    </script>
    <script>
        function openLogoutModal() {
            document.getElementById('logoutModal').classList.add('active');
        }
        function closeLogoutModal() {
            document.getElementById('logoutModal').classList.remove('active');
        }
        document.addEventListener('DOMContentLoaded', function () {
            var overlay = document.getElementById('logoutModal');
            if (overlay) overlay.addEventListener('click', function (e) {
                if (e.target === overlay) closeLogoutModal();
            });
        });
        function setTheme(theme) {
            document.body.classList.remove('emerald-night', 'theme-financial', 'theme-corona');
            if (theme !== 'default') {
                document.body.classList.add(theme);
            }
            localStorage.setItem('app-theme', theme);
            updateThemeToggleText(theme);
        }

        function updateThemeToggleText(theme) {
            const toggles = document.querySelectorAll('.theme-toggle span');
            toggles.forEach(t => {
                if (theme === 'emerald-night') t.innerText = ' Emerald Night';
                else if (theme === 'theme-financial') t.innerText = ' Financial';
                else if (theme === 'theme-corona') t.innerText = ' Corona Dark';
                else t.innerText = ' Default Light';
            });
            const toggleIcons = document.querySelectorAll('.theme-toggle i');
            toggleIcons.forEach(i => {
                i.className = '';
                if (theme === 'emerald-night') i.className = 'bi bi-moon-stars';
                else if (theme === 'theme-financial') i.className = 'bi bi-graph-up-arrow';
                else if (theme === 'theme-corona') i.className = 'bi bi-moon';
                else i.className = 'bi bi-sun';
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Check legacy preference first
            let savedTheme = localStorage.getItem('app-theme');
            if (!savedTheme && localStorage.getItem('emerald-night') === '1') {
                savedTheme = 'emerald-night';
                localStorage.setItem('app-theme', 'emerald-night');
                localStorage.removeItem('emerald-night');
            }
            if (savedTheme) {
                setTheme(savedTheme);
            } else {
                setTheme('default');
            }

            // Global Enter Key Search Submitter
            document.querySelectorAll('input[name="search"]').forEach(function (input) {
                input.addEventListener('keypress', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        let form = this.closest('form');
                        if (form) form.submit();
                    }
                });
            });
        });

        function markNotificationsAsRead(button) {
            // Find the badge inside this button and hide it
            const badge = button.querySelector('.notif-badge');
            if (badge) {
                badge.style.display = 'none';

                // Send AJAX request to update the user's last read timestamp
                fetch('{{ route("activity-logs.mark-read") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }).catch(error => console.error('Error marking notifications as read:', error));
            }
        }

        // â”€â”€ Global Page Transition Loader â”€â”€
        document.addEventListener('DOMContentLoaded', () => {
            const loader = document.getElementById('global-loader');
            
            if (loader) {
                document.addEventListener('click', function(e) {
                    const anchor = e.target.closest('a');
                    if (!anchor) return;
                    
                    const href = anchor.getAttribute('href');
                    const target = anchor.getAttribute('target');
                    
                    // Exclude javascript links, anchors, or new tabs
                    if (!href || href.startsWith('#') || href.startsWith('javascript:') || target === '_blank') {
                        return;
                    }

                    // Also exclude links that might just be modals
                    if (anchor.hasAttribute('data-bs-toggle')) {
                        return;
                    }
                    
                    loader.classList.add('active');
                    
                    // Failsafe: hide loader after 15 seconds if navigation failed or was a download
                    setTimeout(() => {
                        loader.classList.remove('active');
                    }, 15000);
                });

                // Forms that don't open in new tabs
                document.addEventListener('submit', function(e) {
                    const form = e.target;
                    if (form.getAttribute('target') !== '_blank') {
                        loader.classList.add('active');
                        setTimeout(() => {
                            loader.classList.remove('active');
                        }, 15000);
                    }
                });

                // Hide loader when page is restored from bfcach
                window.addEventListener('pageshow', function() {
                    loader.classList.remove('active');
                });
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // â”€â”€ Global AJAX Export Interceptor for Download Links â”€â”€
        document.addEventListener('DOMContentLoaded', () => {
            document.addEventListener('click', async function(e) {
                const anchor = e.target.closest('a');
                if (!anchor) return;
                
                const href = anchor.getAttribute('href');
                if (!href) return;
                
                // Identify if this is an export link by checking href or title
                const title = anchor.getAttribute('title') || '';
                const innerText = anchor.textContent.toLowerCase();
                
                const isPdfExport = (href.toLowerCase().includes('export') && href.toLowerCase().includes('pdf')) || 
                                    title.toLowerCase().includes('export pdf') || 
                                    href.toLowerCase().includes('/pdf/') || 
                                    innerText.includes('export pdf') ||
                                    innerText.includes('print form 9');
                                    
                const isExcelExport = (href.toLowerCase().includes('export') && (href.toLowerCase().includes('excel') || href.toLowerCase().includes('xlsx'))) || 
                                      title.toLowerCase().includes('export excel') ||
                                      innerText.includes('export excel');
                
                if (isPdfExport || isExcelExport) {
                    // Make sure it's not handled by another JS (like a modal opener)
                    if (anchor.hasAttribute('data-bs-toggle')) return;

                    e.preventDefault();
                    
                    // Stop the global loader bar from staying active
                    const globalLoader = document.getElementById('global-loader');
                    if (globalLoader) globalLoader.classList.remove('active');

                    const type = isPdfExport ? 'pdf' : 'excel';
                    
                    Swal.fire({
                        title: 'Generating ' + (type === 'pdf' ? 'PDF' : 'Excel') + '...',
                        html: 'This may take a moment depending on the number of records. Please do not close the window.',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });
                    
                    try {
                        const response = await fetch(href, {
                            method: 'GET',
                            headers: { 'Accept': type === 'pdf' ? 'application/pdf' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' }
                        });

                        if (!response.ok) throw new Error('Server returned an error while generating the file.');

                        let filename = type === 'pdf' ? 'export.pdf' : 'export.xlsx';
                        const disposition = response.headers.get('Content-Disposition');
                        if (disposition && disposition.indexOf('filename=') !== -1) {
                            filename = disposition.split('filename=')[1].replace(/["']/g, '').trim();
                        }

                        const blob = await response.blob();
                        const downloadUrl = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.style.display = 'none';
                        a.href = downloadUrl;
                        a.download = filename;
                        document.body.appendChild(a);
                        a.click();
                        
                        window.URL.revokeObjectURL(downloadUrl);
                        document.body.removeChild(a);

                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Your file has been downloaded successfully.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } catch (error) {
                        Swal.fire({ icon: 'error', title: 'Export Failed', text: 'There was an issue generating your file. ' + error.message });
                    }
                }
            });
        });
    </script>
</body>

</html>