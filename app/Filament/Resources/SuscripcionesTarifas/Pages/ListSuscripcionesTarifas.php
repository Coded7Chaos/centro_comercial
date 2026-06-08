<?php

namespace App\Filament\Resources\SuscripcionesTarifas\Pages;

use App\Filament\Resources\SuscripcionesTarifas\SuscripcionesTarifasResource;
use App\Models\DescuentoTiempo;
use App\Models\TamanoEtiqueta;
use App\Models\TamanoPrecio;
use Filament\Resources\Pages\Page;
use Illuminate\Validation\Rule;

class ListSuscripcionesTarifas extends Page
{
    protected static string $resource = SuscripcionesTarifasResource::class;

    protected string $view = 'filament.pages.custom-tarifas';

    // Properties for Size Labels
    public $etiquetas = [];

    public $etiquetaId = null;

    public $etiquetaNombre = '';

    public $etiquetaDesde = '';

    public $etiquetaHasta = '';

    // Properties for Size Prices
    public $precios = [];

    public $precioId = null;

    public $precioEtiquetaId = '';

    public $precioMensual = '';

    // Properties for Time Discounts
    public $descuentos = [];

    public $descuentoId = null;

    public $descuentoMinMeses = '';

    public $descuentoGlobal = '';

    public function mount(): void
    {
        $this->refreshData();
    }

    public function refreshData(): void
    {
        $this->etiquetas = TamanoEtiqueta::orderBy('desde', 'asc')->get();
        $this->precios = TamanoPrecio::with('etiqueta')->get();
        $this->descuentos = DescuentoTiempo::orderBy('min_meses', 'asc')->get();
    }

    public function cancelEdit(): void
    {
        $this->resetFormFields();
    }

    private function resetFormFields(): void
    {
        $this->etiquetaId = null;
        $this->etiquetaNombre = '';
        $this->etiquetaDesde = '';
        $this->etiquetaHasta = '';

        $this->precioId = null;
        $this->precioEtiquetaId = '';
        $this->precioMensual = '';

        $this->descuentoId = null;
        $this->descuentoMinMeses = '';
        $this->descuentoGlobal = '';
    }

    // --- CRUD ETIQUETAS DE TAMAÑO ---
    public function saveEtiqueta()
    {
        $this->validate([
            'etiquetaNombre' => [
                'required',
                'string',
                'max:50',
                Rule::unique('tamano_etiquetas', 'nombre')->ignore($this->etiquetaId),
            ],
            'etiquetaDesde' => 'required|numeric|min:0',
            'etiquetaHasta' => 'required|numeric|gt:etiquetaDesde',
        ], [
            'etiquetaNombre.unique' => 'Ya existe una etiqueta con este nombre.',
            'etiquetaHasta.gt' => 'El valor "Hasta" debe ser mayor que "Desde".',
        ]);

        if ($this->etiquetaRangeOverlapsExisting()) {
            $message = 'Ya existe una etiqueta de tamaño en el rango escogido';

            $this->addError('etiquetaDesde', $message);
            $this->addError('etiquetaHasta', $message);

            return;
        }

        TamanoEtiqueta::updateOrCreate(
            ['id' => $this->etiquetaId],
            [
                'nombre' => $this->etiquetaNombre,
                'desde' => $this->etiquetaDesde,
                'hasta' => $this->etiquetaHasta,
            ]
        );

        session()->flash('success', $this->etiquetaId ? 'Etiqueta de tamaño actualizada correctamente.' : 'Etiqueta de tamaño agregada correctamente.');
        $this->resetFormFields();
        $this->refreshData();
    }

    private function etiquetaRangeOverlapsExisting(): bool
    {
        $desde = (float) $this->etiquetaDesde;
        $hasta = (float) $this->etiquetaHasta;

        return TamanoEtiqueta::query()
            ->when($this->etiquetaId, fn ($query) => $query->whereKeyNot($this->etiquetaId))
            ->where('desde', '<=', $hasta)
            ->where('hasta', '>=', $desde)
            ->exists();
    }

    public function editEtiqueta($id)
    {
        $this->resetFormFields();
        $etiqueta = TamanoEtiqueta::findOrFail($id);
        $this->etiquetaId = $etiqueta->id;
        $this->etiquetaNombre = $etiqueta->nombre;
        $this->etiquetaDesde = $etiqueta->desde;
        $this->etiquetaHasta = $etiqueta->hasta;
    }

    public function deleteEtiqueta($id)
    {
        $etiqueta = TamanoEtiqueta::findOrFail($id);
        $etiqueta->delete();

        session()->flash('success', 'Etiqueta de tamaño eliminada correctamente.');
        $this->resetFormFields();
        $this->refreshData();
    }

    // --- CRUD PRECIOS POR TAMAÑO ---
    public function savePrecio()
    {
        $this->validate([
            'precioEtiquetaId' => [
                'required',
                'exists:tamano_etiquetas,id',
                Rule::unique('tamano_precios', 'tamano_etiqueta_id')->ignore($this->precioId),
            ],
            'precioMensual' => 'required|numeric|min:0',
        ], [
            'precioEtiquetaId.unique' => 'Esta etiqueta de tamaño ya tiene un precio asignado.',
        ]);

        TamanoPrecio::updateOrCreate(
            ['id' => $this->precioId],
            [
                'tamano_etiqueta_id' => $this->precioEtiquetaId,
                'precio_mensual' => $this->precioMensual,
            ]
        );

        session()->flash('success', $this->precioId ? 'Precio por tamaño actualizado correctamente.' : 'Precio por tamaño asignado correctamente.');
        $this->resetFormFields();
        $this->refreshData();
    }

    public function editPrecio($id)
    {
        $this->resetFormFields();
        $precio = TamanoPrecio::findOrFail($id);
        $this->precioId = $precio->id;
        $this->precioEtiquetaId = $precio->tamano_etiqueta_id;
        $this->precioMensual = $precio->precio_mensual;
    }

    public function deletePrecio($id)
    {
        $precio = TamanoPrecio::findOrFail($id);
        $precio->delete();

        session()->flash('success', 'Precio por tamaño eliminado correctamente.');
        $this->resetFormFields();
        $this->refreshData();
    }

    // --- CRUD DESCUENTOS POR TIEMPO ---
    public function saveDescuento()
    {
        $this->validate([
            'descuentoMinMeses' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('descuentos_tiempo', 'min_meses')->ignore($this->descuentoId),
            ],
            'descuentoGlobal' => 'required|numeric|between:0,100',
        ], [
            'descuentoMinMeses.unique' => 'Ya existe una regla para este mínimo de meses.',
        ]);

        DescuentoTiempo::updateOrCreate(
            ['id' => $this->descuentoId],
            [
                'min_meses' => $this->descuentoMinMeses,
                'descuento' => $this->descuentoGlobal,
            ]
        );

        session()->flash('success', $this->descuentoId ? 'Regla de descuento actualizada correctamente.' : 'Regla de descuento agregada correctamente.');
        $this->resetFormFields();
        $this->refreshData();
    }

    public function editDescuento($id)
    {
        $this->resetFormFields();
        $descuento = DescuentoTiempo::findOrFail($id);
        $this->descuentoId = $descuento->id;
        $this->descuentoMinMeses = $descuento->min_meses;
        $this->descuentoGlobal = $descuento->descuento;
    }

    public function deleteDescuento($id)
    {
        $descuento = DescuentoTiempo::findOrFail($id);
        $descuento->delete();

        session()->flash('success', 'Regla de descuento eliminada correctamente.');
        $this->resetFormFields();
        $this->refreshData();
    }
}
