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
                    '                        ctx.font = "bold 10px Inter, sans-serif";' .
                    '                        ctx.fillStyle = "#334155";' .
                    '                        ctx.textAlign = "center";' .
                    '                        ctx.textBaseline = "bottom";' .
                    '                        let text = "Bs. " + Number(dataValue).toLocaleString();' .
                    '                        ctx.fillText(text, element.x, element.y - 6);' .
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
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                AccountWidget::class,
                \App\Filament\Widgets\StatsOverview::class,
                \App\Filament\Widgets\IngresosMensualesChart::class,
                \App\Filament\Widgets\CobrosPorEstadoChart::class,
                \App\Filament\Widgets\OcupacionPorPisoChart::class,
                \App\Filament\Widgets\MetodoPagoChart::class,
                \App\Filament\Widgets\TopMorososWidget::class,
                \App\Filament\Widgets\CostoOportunidadVacanciaChart::class,
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
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
