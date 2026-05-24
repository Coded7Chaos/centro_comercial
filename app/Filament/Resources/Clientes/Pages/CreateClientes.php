<?php

namespace App\Filament\Resources\Clientes\Pages;

use App\Filament\Resources\Clientes\ClientesResource;
use Filament\Resources\Pages\CreateRecord;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class CreateClientes extends CreateRecord
{
    use WithFileUploads;

    protected static string $resource = ClientesResource::class;

    protected string $view = 'filament.pages.form-cliente';

    // Propiedades para los archivos
    public $foto_upload;
    public $documentos_upload = []; // Array de arrays ['tipo' => '', 'archivo' => null]

    public function updatedFotoUpload()
    {
        $this->validate([
            'foto_upload' => 'image|max:15360', // 15MB
        ]);
    }

    public function addDocumento()
    {
        $this->documentos_upload[] = [
            'tipo' => 'contrato',
            'archivo' => null,
        ];
    }

    public function removeDocumento($index)
    {
        unset($this->documentos_upload[$index]);
        $this->documentos_upload = array_values($this->documentos_upload);
    }

    protected function handleRecordCreation(array $data): Model
    {
        // Validar todos los documentos antes de procesar
        $this->validate([
            'documentos_upload.*.archivo' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,webp|max:102400',
            'documentos_upload.*.tipo' => 'required|in:contrato,ci',
        ]);

        // 1. Extraer datos del usuario del formulario
        $userData = [
            'nombres' => $data['nombres'],
            'apellido_paterno' => $data['apellido_paterno'],
            'apellido_materno' => $data['apellido_materno'] ?? null,
            'email' => $data['email'],
            'password' => Hash::make(Str::random(16)),
        ];

        unset($data['nombres'], $data['apellido_paterno'], $data['apellido_materno'], $data['email']);

        // 3. Crear el Usuario
        $user = User::create($userData);
        $user->assignRole('cliente');
        $user->sendEmailVerificationNotification();

        // 4. Vincular y crear cliente
        $data['user_id'] = $user->id;
        $cliente = parent::handleRecordCreation($data);

        // 5. Manejar Foto de Perfil (Livewire - Guardado explícito)
        if ($this->foto_upload) {
            $path = $this->foto_upload->store('clientes-fotos', 'public');
            $cliente->foto = $path;
            $cliente->save();
        }

        // 6. Manejar Documentos Adjuntos (Livewire)
        foreach ($this->documentos_upload as $doc) {
            if ($doc['archivo']) {
                $path = $doc['archivo']->store('clientes-documentos', 'public');
                $cliente->documentos()->create([
                    'tipo' => $doc['tipo'],
                    'url' => $path,
                ]);
            }
        }

        return $cliente;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Cliente y cuenta de usuario creados exitosamente.';
    }
}
