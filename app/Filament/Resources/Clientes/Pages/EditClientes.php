<?php

namespace App\Filament\Resources\Clientes\Pages;

use App\Filament\Resources\Clientes\ClientesResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

use Illuminate\Database\Eloquent\Model;

use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class EditClientes extends EditRecord
{
    use WithFileUploads;

    protected static string $resource = ClientesResource::class;

    protected string $view = 'filament.pages.form-cliente';

    // Propiedades para los archivos
    public $foto_upload;
    public $documentos_upload = []; // Array de arrays ['tipo' => '', 'archivo' => null, 'existente' => 'url']

    public function updatedFotoUpload()
    {
        $this->validate([
            'foto_upload' => 'image|max:15360', // 15MB
        ]);
    }

    public function mount($record): void
    {
        parent::mount($record);

        // Inicializar documentos existentes
        foreach ($this->record->documentos as $doc) {
            $this->documentos_upload[] = [
                'tipo' => $doc->tipo,
                'archivo' => null,
                'existente' => $doc->url,
                'id' => $doc->id,
            ];
        }
    }

    public function addDocumento()
    {
        $this->documentos_upload[] = [
            'tipo' => 'contrato',
            'archivo' => null,
            'existente' => null,
        ];
    }

    public function removeDocumento($index)
    {
        $doc = $this->documentos_upload[$index];
        
        // Si ya existía en la BD, lo borramos de verdad y eliminamos el archivo físico
        if (isset($doc['id'])) {
            $docModel = \App\Models\ClientesDocumentos::find($doc['id']);
            if ($docModel) {
                // Borrar archivo físico
                Storage::disk('public')->delete($docModel->url);
                // Borrar registro DB
                $docModel->delete();
            }
        }

        unset($this->documentos_upload[$index]);
        $this->documentos_upload = array_values($this->documentos_upload);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Validar todos los documentos antes de procesar
        $this->validate([
            'documentos_upload.*.archivo' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,webp|max:102400',
            'documentos_upload.*.tipo' => 'required|in:contrato,ci',
        ]);

        // 1. Actualizar datos del usuario asociado
        if ($record->user) {
            $record->user->update([
                'nombres' => $data['nombres'],
                'apellido_paterno' => $data['apellido_paterno'],
                'apellido_materno' => $data['apellido_materno'],
                'email' => $data['email'],
            ]);
        }

        unset($data['nombres'], $data['apellido_paterno'], $data['apellido_materno'], $data['email']);

        // 3. Actualizar el registro del cliente
        $cliente = parent::handleRecordUpdate($record, $data);

        // 4. Manejar Foto de Perfil (Livewire - Guardado explícito para asegurar que se guarde)
        if ($this->foto_upload) {
            $path = $this->foto_upload->store('clientes-fotos', 'public');
            $cliente->foto = $path;
            $cliente->save();
        }

        // 5. Manejar Documentos Adjuntos
        foreach ($this->documentos_upload as $doc) {
            // Caso A: Documento Nuevo
            if ($doc['archivo'] && !isset($doc['id'])) {
                $path = $doc['archivo']->store('clientes-documentos', 'public');
                $cliente->documentos()->create([
                    'tipo' => $doc['tipo'],
                    'url' => $path,
                ]);
            } 
            // Caso B: Documento Existente (Actualizar solo el tipo)
            elseif (isset($doc['id'])) {
                \App\Models\ClientesDocumentos::where('id', $doc['id'])->update([
                    'tipo' => $doc['tipo'],
                ]);
            }
        }

        return $cliente;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Cliente y cuenta de usuario actualizados correctamente.';
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
