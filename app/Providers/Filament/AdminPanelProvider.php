<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->globalSearch(false)
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            // Leaflet para mapas y Datalabels para gráficos
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn(): string => Blade::render("@vite('resources/js/leaflet-field.js')") . 
                    '<script>' .
                    '(function() {' .
                    '    const permanentLabelsPlugin = {' .
                    '        id: "permanentLabels",' .
                    '        afterDatasetsDraw(chart, args, options) {' .
                    '            if (chart.options.plugins?.permanentLabels?.display === false) return;' .
                    '            const { ctx } = chart;' .
                    '            ctx.save();' .
                    '            const isHorizontal = chart.options.indexAxis === "y";' .
                    '            const isStacked = chart.options.scales?.x?.stacked || chart.options.scales?.y?.stacked;' .
                    '            chart.data.datasets.forEach((dataset, i) => {' .
                    '                const meta = chart.getDatasetMeta(i);' .
                    '                if (meta.hidden) return;' .
                    '                meta.data.forEach((element, index) => {' .
                    '                    const dataValue = dataset.data[index];' .
                    '                    if (dataValue === 0 || dataValue === null || dataValue === undefined) return;' .
                    '                    if (chart.config.type === "line") {' .
                    '                        const labelOptions = chart.options.plugins?.permanentLabels || {};' .
                    '                        if (labelOptions.mode === "last" && index !== dataset.data.length - 1) return;' .
                    '                        ctx.font = "bold 10px Inter, sans-serif";' .
                    '                        let text = "Bs. " + Number(dataValue).toLocaleString();' .
                    '                        const textWidth = ctx.measureText(text).width;' .
                    '                        const pillWidth = textWidth + 14;' .
                    '                        const pillHeight = 20;' .
                    '                        const offsets = labelOptions.yOffsets || [-18, 18, -40, 40];' .
                    '                        const pillX = Math.min(element.x + pillWidth / 2 + 12, chart.chartArea.right - pillWidth / 2);' .
                    '                        const pillY = Math.max(chart.chartArea.top + pillHeight / 2, Math.min(element.y + offsets[i % offsets.length], chart.chartArea.bottom - pillHeight / 2));' .
                    '                        ctx.beginPath();' .
                    '                        const r = 10;' .
                    '                        ctx.moveTo(pillX - pillWidth/2 + r, pillY - pillHeight/2);' .
                    '                        ctx.lineTo(pillX + pillWidth/2 - r, pillY - pillHeight/2);' .
                    '                        ctx.quadraticCurveTo(pillX + pillWidth/2, pillY - pillHeight/2, pillX + pillWidth/2, pillY - pillHeight/2 + r);' .
                    '                        ctx.lineTo(pillX + pillWidth/2, pillY + pillHeight/2 - r);' .
                    '                        ctx.quadraticCurveTo(pillX + pillWidth/2, pillY + pillHeight/2, pillX + pillWidth/2 - r, pillY + pillHeight/2);' .
                    '                        ctx.lineTo(pillX - pillWidth/2 + r, pillY + pillHeight/2);' .
                    '                        ctx.quadraticCurveTo(pillX - pillWidth/2, pillY + pillHeight/2, pillX - pillWidth/2, pillY + pillHeight/2 - r);' .
                    '                        ctx.lineTo(pillX - pillWidth/2, pillY - pillHeight/2 + r);' .
                    '                        ctx.quadraticCurveTo(pillX - pillWidth/2, pillY - pillHeight/2, pillX - pillWidth/2 + r, pillY - pillHeight/2);' .
                    '                        ctx.closePath();' .
                    '                        ctx.fillStyle = dataset.borderColor || "#0f172a";' .
                    '                        ctx.fill();' .
                    '                        ctx.fillStyle = "#ffffff";' .
                    '                        ctx.textAlign = "center";' .
                    '                        ctx.textBaseline = "middle";' .
                    '                        ctx.fillText(text, pillX, pillY);' .
                    '                    }' .
                    '                    else if (chart.config.type === "bar") {' .
                    '                        ctx.font = "bold 10px Inter, sans-serif";' .
                    '                        const center = element.tooltipPosition();' .
                    '                        if (isHorizontal && !isStacked) {' .
                    '                            let text = "Bs. " + Number(dataValue).toLocaleString();' .
                    '                            const textWidth = ctx.measureText(text).width;' .
                    '                            const pillWidth = textWidth + 12;' .
                    '                            const pillHeight = 18;' .
                    '                            const pillX = element.x + pillWidth/2 + 6;' .
                    '                            const pillY = element.y;' .
                    '                            ctx.beginPath();' .
                    '                            const r = 9;' .
                    '                            ctx.moveTo(pillX - pillWidth/2 + r, pillY - pillHeight/2);' .
                    '                            ctx.lineTo(pillX + pillWidth/2 - r, pillY - pillHeight/2);' .
                    '                            ctx.quadraticCurveTo(pillX + pillWidth/2, pillY - pillHeight/2, pillX + pillWidth/2, pillY - pillHeight/2 + r);' .
                    '                            ctx.lineTo(pillX + pillWidth/2, pillY + pillHeight/2 - r);' .
                    '                            ctx.quadraticCurveTo(pillX + pillWidth/2, pillY + pillHeight/2, pillX + pillWidth/2 - r, pillY + pillHeight/2);' .
                    '                            ctx.lineTo(pillX - pillWidth/2 + r, pillY + pillHeight/2);' .
                    '                            ctx.quadraticCurveTo(pillX - pillWidth/2, pillY + pillHeight/2, pillX - pillWidth/2, pillY + pillHeight/2 - r);' .
                    '                            ctx.lineTo(pillX - pillWidth/2, pillY - pillHeight/2 + r);' .
                    '                            ctx.quadraticCurveTo(pillX - pillWidth/2, pillY - pillHeight/2, pillX - pillWidth/2 + r, pillY - pillHeight/2);' .
                    '                            ctx.closePath();' .
                    '                            ctx.fillStyle = "#0f172a";' .
                    '                            ctx.fill();' .
                    '                            ctx.fillStyle = "#ffffff";' .
                    '                            ctx.textAlign = "center";' .
                    '                            ctx.textBaseline = "middle";' .
                    '                            ctx.fillText(text, pillX, pillY);' .
                    '                        } else if (!isHorizontal && !isStacked) {' .
                    '                            let text = "Bs. " + Number(dataValue).toLocaleString();' .
                    '                            const textWidth = ctx.measureText(text).width;' .
                    '                            const pillWidth = textWidth + 12;' .
                    '                            const pillHeight = 18;' .
                    '                            const pillX = center.x;' .
                    '                            const pillY = center.y - 12;' .
                    '                            ctx.beginPath();' .
                    '                            const r = 9;' .
                    '                            ctx.moveTo(pillX - pillWidth/2 + r, pillY - pillHeight/2);' .
                    '                            ctx.lineTo(pillX + pillWidth/2 - r, pillY - pillHeight/2);' .
                    '                            ctx.quadraticCurveTo(pillX + pillWidth/2, pillY - pillHeight/2, pillX + pillWidth/2, pillY - pillHeight/2 + r);' .
                    '                            ctx.lineTo(pillX + pillWidth/2, pillY + pillHeight/2 - r);' .
                    '                            ctx.quadraticCurveTo(pillX + pillWidth/2, pillY + pillHeight/2, pillX + pillWidth/2 - r, pillY + pillHeight/2);' .
                    '                            ctx.lineTo(pillX - pillWidth/2 + r, pillY + pillHeight/2);' .
                    '                            ctx.quadraticCurveTo(pillX - pillWidth/2, pillY + pillHeight/2, pillX - pillWidth/2, pillY + pillHeight/2 - r);' .
                    '                            ctx.lineTo(pillX - pillWidth/2, pillY - pillHeight/2 + r);' .
                    '                            ctx.quadraticCurveTo(pillX - pillWidth/2, pillY - pillHeight/2, pillX - pillWidth/2 + r, pillY - pillHeight/2);' .
                    '                            ctx.closePath();' .
                    '                            ctx.fillStyle = "#0f172a";' .
                    '                            ctx.fill();' .
                    '                            ctx.fillStyle = "#ffffff";' .
                    '                            ctx.textAlign = "center";' .
                    '                            ctx.textBaseline = "middle";' .
                    '                            ctx.fillText(text, pillX, pillY);' .
                    '                        } else {' .
                    '                            const text = dataValue.toString();' .
                    '                            ctx.beginPath();' .
                    '                            ctx.arc(center.x, center.y, 9, 0, 2 * Math.PI);' .
                    '                            ctx.fillStyle = "#0f172a";' .
                    '                            ctx.fill();' .
                    '                            ctx.fillStyle = "#ffffff";' .
                    '                            ctx.textAlign = "center";' .
                    '                            ctx.textBaseline = "middle";' .
                    '                            ctx.fillText(text, center.x, center.y);' .
                    '                        }' .
                    '                    }' .
                    '                    else if (["pie", "doughnut"].includes(chart.config.type)) {' .
                    '                        ctx.font = "bold 10px Inter, sans-serif";' .
                    '                        let sum = dataset.data.reduce((a, b) => a + Number(b), 0);' .
                    '                        let percentage = (dataValue * 100 / sum).toFixed(1) + "%";' .
                    '                        let textLine1 = "Bs. " + Number(dataValue).toLocaleString();' .
                    '                        let textLine2 = "(" + percentage + ")";' .
                    '                        const center = element.tooltipPosition();' .
                    '                        ctx.save();' .
                    '                        let maxW = Math.max(ctx.measureText(textLine1).width, ctx.measureText(textLine2).width);' .
                    '                        let w = maxW + 12;' .
                    '                        let h = 28;' .
                    '                        let r = 6;' .
                    '                        ctx.beginPath();' .
                    '                        ctx.moveTo(center.x - w/2 + r, center.y - h/2);' .
                    '                        ctx.lineTo(center.x + w/2 - r, center.y - h/2);' .
                    '                        ctx.quadraticCurveTo(center.x + w/2, center.y - h/2, center.x + w/2, center.y - h/2 + r);' .
                    '                        ctx.lineTo(center.x + w/2, center.y + h/2 - r);' .
                    '                        ctx.quadraticCurveTo(center.x + w/2, center.y + h/2, center.x + w/2 - r, center.y + h/2);' .
                    '                        ctx.lineTo(center.x - w/2 + r, center.y + h/2);' .
                    '                        ctx.quadraticCurveTo(center.x - w/2, center.y + h/2, center.x - w/2, center.y + h/2 - r);' .
                    '                        ctx.lineTo(center.x - w/2, center.y - h/2 + r);' .
                    '                        ctx.quadraticCurveTo(center.x - w/2, center.y - h/2, center.x - w/2 + r, center.y - h/2);' .
                    '                        ctx.closePath();' .
                    '                        ctx.fillStyle = "#0f172a";' .
                    '                        ctx.fill();' .
                    '                        ctx.restore();' .
                    '                        ctx.fillStyle = "#ffffff";' .
                    '                        ctx.textAlign = "center";' .
                    '                        ctx.textBaseline = "middle";' .
                    '                        ctx.fillText(textLine1, center.x, center.y - 6);' .
                    '                        ctx.fillText(textLine2, center.x, center.y + 6);' .
                    '                    }' .
                    '                });' .
                    '            });' .
                    '            ctx.restore();' .
                    '        }' .
                    '    };' .
                    '    window.filamentChartJsGlobalPlugins = window.filamentChartJsGlobalPlugins || [];' .
                    '    if (!window.filamentChartJsGlobalPlugins.some(p => p.id === "permanentLabels")) {' .
                    '        window.filamentChartJsGlobalPlugins.push(permanentLabelsPlugin);' .
                    '    }' .
                    '})();' .
                    '</script>'
            )
            ->renderHook(
                PanelsRenderHook::PAGE_START,
                fn(): string => view('filament.components.verify-email-banner')
            )
            ->renderHook(
                PanelsRenderHook::TOPBAR_START,
                fn (): string => Blade::render('
                    <x-filament::button
                        :href="url(\'/\')"
                        tag="a"
                        icon="heroicon-m-arrow-left"
                        color="gray"
                        size="sm"
                        variant="outline"
                        class="ml-4"
                    >
                        Ver Sitio
                    </x-filament::button>
                '),
            )
            ->renderHook(
                PanelsRenderHook::TOPBAR_END,
                function (): string {
                    $infra = \App\Support\ActiveInfraestructura::get();
                    if (! $infra) return '';
                    $url = route('filament.admin.pages.seleccionar-infraestructura');
                    return Blade::render('
                        <a href="' . $url . '"
                           class="mr-4 inline-flex items-center gap-1.5 rounded-xl border border-amber-300 bg-amber-50 hover:bg-amber-100 px-3 py-1.5 text-xs font-bold text-amber-800 transition-colors shadow-sm"
                           title="Cambiar infraestructura activa">
                            <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span>' . e($infra->nombre) . '</span>
                            <svg class="w-3 h-3 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                            </svg>
                        </a>
                    ');
                },
            )
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                AccountWidget::class,
                \App\Filament\Widgets\InfraestructuraDashboardFilter::class,
                \App\Filament\Widgets\StatsOverview::class,
                \App\Filament\Widgets\IngresosMensualesChart::class,
                \App\Filament\Widgets\CobrosPorEstadoChart::class,
                \App\Filament\Widgets\OcupacionPorPisoChart::class,
                \App\Filament\Widgets\MetodoPagoChart::class,
                \App\Filament\Widgets\TopMorososWidget::class,
                \App\Filament\Widgets\CostoOportunidadVacanciaChart::class,
                \App\Filament\Widgets\PerdidasMensualesVacanciaChart::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                \App\Http\Middleware\RequiereInfraestructuraActiva::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
