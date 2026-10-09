@extends('layouts.app')

@push('scripts')
    @vite('resources/js/pages/admin/settings/theme.js')
@endpush

@section('content')
<div class="card p-3 shadow-sm" id="admin_theme_page">
    {{-- Detailed Comment: Header bar for Theme Settings with navigation link back to Facility Profile --}}
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
            <h2 class="m-0 fw-bold"><i class="fa-solid fa-palette text-primary me-2"></i>Theme & Branding Settings</h2>
            <div class="text-muted small">Customize application colors, navbar header, footer, buttons, text colors, and facility logo.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.profile') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-building me-1"></i> Facility Profile
            </a>
            <button type="button" class="btn btn-sm btn-outline-danger" id="reset_theme_btn">
                <i class="fa-solid fa-rotate-left me-1"></i> Reset to Defaults
            </button>
            <button type="button" class="btn btn-sm btn-primary fw-bold" id="save_theme_btn">
                <i class="fa-solid fa-floppy-disk me-1"></i> Save Theme Changes
            </button>
        </div>
    </div>
    <hr class="mt-1 mb-3">

    {{-- Detailed Comment: One-Click Theme Preset section --}}
    <div class="alert alert-light border d-flex flex-wrap align-items-center justify-content-between p-3 mb-4 rounded-3 shadow-xs">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle p-2 bg-primary-subtle text-primary">
                <i class="fa-solid fa-wand-magic-sparkles fa-lg"></i>
            </div>
            <div>
                <strong class="d-block">One-Click Theme Presets</strong>
                <span class="text-muted small">Instantly apply predefined branding palettes to your clinic.</span>
            </div>
        </div>
        <div class="d-flex gap-2 mt-2 mt-md-0">
            {{-- User-specified Teal & Lime 2-color preset from image temp/7ff09159-09b0-4991-8e0d-138c622001ad.jfif --}}
            <button type="button" class="btn btn-sm btn-outline-dark d-flex align-items-center gap-2 px-3 py-2 preset-btn" id="preset_teal_lime"
                data-header-bg="#027f9f"
                data-header-accent="#b2c10e"
                data-header-text="#ffffff"
                data-app-bg="#f8f9fa"
                data-footer-bg="#f1f5f8"
                data-footer-text="#495057"
                data-sidebar-bg="#e9ecef"
                data-sidebar-text="#212529"
                data-primary-btn-bg="#027f9f"
                data-primary-btn-text="#ffffff"
                data-secondary-btn-bg="#b2c10e"
                data-secondary-btn-text="#212529"
                data-text-color="#212529">
                <span class="d-inline-flex rounded-circle border" style="width: 18px; height: 18px; background: linear-gradient(135deg, #027f9f 50%, #b2c10e 50%);"></span>
                <span class="fw-semibold">Teal & Lime (2-Color Brand)</span>
            </button>

            {{-- Original Classic Clinic Warm Orange --}}
            <button type="button" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-2 px-3 py-2 preset-btn" id="preset_warm_orange"
                data-header-bg="#f4c79f"
                data-header-accent="#ffa500"
                data-header-text="#212529"
                data-app-bg="#f8f9fa"
                data-footer-bg="#f8f9fa"
                data-footer-text="#6c757d"
                data-sidebar-bg="#e9ecef"
                data-sidebar-text="#212529"
                data-primary-btn-bg="#0d6efd"
                data-primary-btn-text="#ffffff"
                data-secondary-btn-bg="#6c757d"
                data-secondary-btn-text="#ffffff"
                data-text-color="#212529">
                <span class="d-inline-flex rounded-circle border" style="width: 18px; height: 18px; background: linear-gradient(135deg, #f4c79f 50%, #ffa500 50%);"></span>
                <span>Original Warm Orange</span>
            </button>
        </div>
    </div>

    <form id="theme_settings_form" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="theme_name" id="theme_name" value="{{ $theme->theme_name ?? 'Custom Theme' }}">

        <div class="row g-4">
            {{-- Left Column: Color Controls & Logo Upload --}}
            <div class="col-lg-7 d-flex flex-column gap-3">

                {{-- Facility Logo Card --}}
                <div class="card border">
                    <div class="card-header bg-light py-2 fw-bold d-flex align-items-center gap-2">
                        <i class="fa-solid fa-image text-primary"></i> Facility Logo
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-4 flex-wrap">
                            <div class="border rounded p-2 bg-white text-center shadow-xs">
                                <img src="{{ asset($theme->logo_path ?? 'images/logo.png') }}"
                                     alt="Logo Preview"
                                     id="theme_logo_preview"
                                     style="height: 70px; max-width: 180px; object-fit: contain;">
                            </div>
                            <div class="flex-grow-1">
                                <label for="logo_file" class="form-label small fw-bold mb-1">Upload New Logo</label>
                                <input class="form-control form-control-sm" type="file" id="logo_file" name="logo_file" accept=".png,.jpg,.jpeg,.svg,.webp">
                                <div class="form-text small">Accepted formats: PNG, JPG, SVG, WebP. Recommended height: 80px (transparent PNG). Max size: 2MB.</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Header (Navbar) Styling Card --}}
                <div class="card border">
                    <div class="card-header bg-light py-2 fw-bold d-flex align-items-center gap-2">
                        <i class="fa-solid fa-window-maximize text-primary"></i> Header (Navbar) Styling
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold" for="header_bg">Header Background</label>
                                <div class="input-group input-group-sm">
                                    <input type="color" class="form-control form-control-color" id="header_bg_picker" value="{{ $theme->header_bg ?? '#f4c79f' }}">
                                    <input type="text" class="form-control text-uppercase" id="header_bg" name="header_bg" value="{{ $theme->header_bg ?? '#f4c79f' }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold" for="header_text_color">Header Text & Icons</label>
                                <div class="input-group input-group-sm">
                                    <input type="color" class="form-control form-control-color" id="header_text_color_picker" value="{{ $theme->header_text_color ?? '#212529' }}">
                                    <input type="text" class="form-control text-uppercase" id="header_text_color" name="header_text_color" value="{{ $theme->header_text_color ?? '#212529' }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold" for="header_accent_color">Bottom Accent Strip</label>
                                <div class="input-group input-group-sm">
                                    <input type="color" class="form-control form-control-color" id="header_accent_color_picker" value="{{ $theme->header_accent_color ?? '#ffa500' }}">
                                    <input type="text" class="form-control text-uppercase" id="header_accent_color" name="header_accent_color" value="{{ $theme->header_accent_color ?? '#ffa500' }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Application Background & Body Text Card --}}
                <div class="card border">
                    <div class="card-header bg-light py-2 fw-bold d-flex align-items-center gap-2">
                        <i class="fa-solid fa-desktop text-primary"></i> App Background & Body Text
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold" for="app_background">App Body Background</label>
                                <div class="input-group input-group-sm">
                                    <input type="color" class="form-control form-control-color" id="app_background_picker" value="{{ $theme->app_background ?? '#f8f9fa' }}">
                                    <input type="text" class="form-control text-uppercase" id="app_background" name="app_background" value="{{ $theme->app_background ?? '#f8f9fa' }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold" for="text_color">General Body Text</label>
                                <div class="input-group input-group-sm">
                                    <input type="color" class="form-control form-control-color" id="text_color_picker" value="{{ $theme->text_color ?? '#212529' }}">
                                    <input type="text" class="form-control text-uppercase" id="text_color" name="text_color" value="{{ $theme->text_color ?? '#212529' }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sidebar Navigation Card --}}
                <div class="card border">
                    <div class="card-header bg-light py-2 fw-bold d-flex align-items-center gap-2">
                        <i class="fa-solid fa-bars text-primary"></i> Sidebar Navigation
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold" for="sidebar_bg">Sidebar Background</label>
                                <div class="input-group input-group-sm">
                                    <input type="color" class="form-control form-control-color" id="sidebar_bg_picker" value="{{ $theme->sidebar_bg ?? '#e9ecef' }}">
                                    <input type="text" class="form-control text-uppercase" id="sidebar_bg" name="sidebar_bg" value="{{ $theme->sidebar_bg ?? '#e9ecef' }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold" for="sidebar_text_color">Sidebar Text & Links</label>
                                <div class="input-group input-group-sm">
                                    <input type="color" class="form-control form-control-color" id="sidebar_text_color_picker" value="{{ $theme->sidebar_text_color ?? '#212529' }}">
                                    <input type="text" class="form-control text-uppercase" id="sidebar_text_color" name="sidebar_text_color" value="{{ $theme->sidebar_text_color ?? '#212529' }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons Styling Card --}}
                <div class="card border">
                    <div class="card-header bg-light py-2 fw-bold d-flex align-items-center gap-2">
                        <i class="fa-solid fa-square-check text-primary"></i> Buttons Styling
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold" for="primary_button_bg">Primary Button Background</label>
                                <div class="input-group input-group-sm">
                                    <input type="color" class="form-control form-control-color" id="primary_button_bg_picker" value="{{ $theme->primary_button_bg ?? '#0d6efd' }}">
                                    <input type="text" class="form-control text-uppercase" id="primary_button_bg" name="primary_button_bg" value="{{ $theme->primary_button_bg ?? '#0d6efd' }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold" for="primary_button_text">Primary Button Text</label>
                                <div class="input-group input-group-sm">
                                    <input type="color" class="form-control form-control-color" id="primary_button_text_picker" value="{{ $theme->primary_button_text ?? '#ffffff' }}">
                                    <input type="text" class="form-control text-uppercase" id="primary_button_text" name="primary_button_text" value="{{ $theme->primary_button_text ?? '#ffffff' }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold" for="secondary_button_bg">Secondary Button Background</label>
                                <div class="input-group input-group-sm">
                                    <input type="color" class="form-control form-control-color" id="secondary_button_bg_picker" value="{{ $theme->secondary_button_bg ?? '#6c757d' }}">
                                    <input type="text" class="form-control text-uppercase" id="secondary_button_bg" name="secondary_button_bg" value="{{ $theme->secondary_button_bg ?? '#6c757d' }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold" for="secondary_button_text">Secondary Button Text</label>
                                <div class="input-group input-group-sm">
                                    <input type="color" class="form-control form-control-color" id="secondary_button_text_picker" value="{{ $theme->secondary_button_text ?? '#ffffff' }}">
                                    <input type="text" class="form-control text-uppercase" id="secondary_button_text" name="secondary_button_text" value="{{ $theme->secondary_button_text ?? '#ffffff' }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Application Footer Styling Card --}}
                <div class="card border">
                    <div class="card-header bg-light py-2 fw-bold d-flex align-items-center gap-2">
                        <i class="fa-solid fa-shoe-prints text-primary"></i> Application Footer Styling
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold" for="footer_bg">Footer Background</label>
                                <div class="input-group input-group-sm">
                                    <input type="color" class="form-control form-control-color" id="footer_bg_picker" value="{{ $theme->footer_bg ?? '#f8f9fa' }}">
                                    <input type="text" class="form-control text-uppercase" id="footer_bg" name="footer_bg" value="{{ $theme->footer_bg ?? '#f8f9fa' }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold" for="footer_text_color">Footer Text Color</label>
                                <div class="input-group input-group-sm">
                                    <input type="color" class="form-control form-control-color" id="footer_text_color_picker" value="{{ $theme->footer_text_color ?? '#6c757d' }}">
                                    <input type="text" class="form-control text-uppercase" id="footer_text_color" name="footer_text_color" value="{{ $theme->footer_text_color ?? '#6c757d' }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Right Column: Interactive Real-Time Live Preview --}}
            <div class="col-lg-5">
                <div class="card border sticky-top" style="top: 1rem; z-index: 10;">
                    <div class="card-header bg-dark text-white py-2 d-flex justify-content-between align-items-center">
                        <span class="fw-bold"><i class="fa-solid fa-eye me-1 text-warning"></i> Live Real-Time Preview</span>
                        <span class="badge bg-secondary small">Dynamic Mockup</span>
                    </div>
                    <div class="card-body p-0">
                        {{-- Mockup Window Container --}}
                        <div class="preview-app-container border rounded m-2 overflow-hidden shadow-xs" id="preview_app_body" style="background-color: {{ $theme->app_background ?? '#f8f9fa' }}; color: {{ $theme->text_color ?? '#212529' }}; min-height: 440px;">
                            
                            {{-- Mock Navbar --}}
                            <div class="preview-navbar p-2 d-flex justify-content-between align-items-center" id="preview_navbar" style="background-color: {{ $theme->header_bg ?? '#f4c79f' }}; color: {{ $theme->header_text_color ?? '#212529' }};">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-bars fa-sm"></i>
                                    <img src="{{ asset($theme->logo_path ?? 'images/logo.png') }}"
                                         alt="Navbar Logo"
                                         id="preview_nav_logo"
                                         style="height: 32px; max-width: 90px; object-fit: contain;">
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-white text-dark border px-2 py-1"><i class="fa-solid fa-user-shield me-1"></i> Admin</span>
                                </div>
                            </div>
                            <div class="preview-accent-strip" id="preview_accent_strip" style="background-color: {{ $theme->header_accent_color ?? '#ffa500' }}; height: 4px; width: 100%;"></div>

                            {{-- Mock Main Layout: Sidebar + Main Area --}}
                            <div class="d-flex" style="min-height: 330px;">
                                {{-- Mock Sidebar --}}
                                <div class="preview-sidebar p-2 border-end" id="preview_sidebar" style="width: 140px; background-color: {{ $theme->sidebar_bg ?? '#e9ecef' }}; color: {{ $theme->sidebar_text_color ?? '#212529' }};">
                                    <div class="small fw-bold mb-2 text-uppercase opacity-75" style="font-size: 10px;">Menu</div>
                                    <ul class="list-unstyled d-flex flex-column gap-1 mb-0" style="font-size: 11px;">
                                        <li class="p-1 rounded bg-white fw-bold shadow-xs"><i class="fa-solid fa-gauge me-1"></i> Dashboard</li>
                                        <li class="p-1 rounded"><i class="fa-solid fa-users me-1"></i> Patients</li>
                                        <li class="p-1 rounded"><i class="fa-solid fa-sliders me-1"></i> Settings</li>
                                    </ul>
                                </div>

                                {{-- Mock Content Area --}}
                                <div class="preview-content flex-grow-1 p-3">
                                    <div class="card p-2 border bg-white mb-2 shadow-xs">
                                        <h6 class="m-0 fw-bold" id="preview_card_title" style="color: inherit;">Patient Overview</h6>
                                        <p class="small mb-2 text-muted" style="font-size: 11px;">Active consultation queue and metrics.</p>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm px-2 py-1" id="preview_btn_primary"
                                                style="background-color: {{ $theme->primary_button_bg ?? '#0d6efd' }}; color: {{ $theme->primary_button_text ?? '#ffffff' }}; font-size: 11px; font-weight: bold;">
                                                Primary Action
                                            </button>
                                            <button type="button" class="btn btn-sm px-2 py-1" id="preview_btn_secondary"
                                                style="background-color: {{ $theme->secondary_button_bg ?? '#6c757d' }}; color: {{ $theme->secondary_button_text ?? '#ffffff' }}; font-size: 11px;">
                                                Secondary
                                            </button>
                                        </div>
                                    </div>
                                    <div class="p-2 border rounded bg-white small" style="font-size: 11px;">
                                        <i class="fa-solid fa-circle-check text-success me-1"></i> System status: Online & Connected
                                    </div>
                                </div>
                            </div>

                            {{-- Mock Footer --}}
                            <div class="preview-footer border-top px-2 py-1 d-flex justify-content-between align-items-center" id="preview_footer"
                                style="background-color: {{ $theme->footer_bg ?? '#f8f9fa' }}; color: {{ $theme->footer_text_color ?? '#6c757d' }}; font-size: 10px;">
                                <span>&copy; {{ date('Y') }} {{ $profile->HOSP_NAME ?? config('app.name') }}</span>
                                <span class="badge bg-secondary-subtle text-secondary border">v2.0</span>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
