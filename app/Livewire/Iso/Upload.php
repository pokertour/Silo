<?php

namespace App\Livewire\Iso;

use App\Jobs\CalculateIsoChecksum;
use App\Jobs\ImportIsoFromUrl;
use App\Models\Group;
use App\Models\IsoFile;
use App\Models\IsoImport;
use App\Models\StorageLocation;
use App\Models\Tag;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class Upload extends Component
{
    use WithFileUploads;

    public $tempFilePath = '';

    public $originalFileName = '';

    public $name = '';

    public $version = '';

    public $architecture = '';

    public $os_type = '';

    public $description = '';

    public $expected_checksum = '';

    public $importUrl = '';

    public $searchTag = '';

    public $selectedTags = [];

    public $availableTags = [];

    public $group_id = '';

    public $storage_id = '';

    public $groups;

    public $storageLocations;

    /** Onglet ouvert à l'arrivée : « file » ou « url » (?source=url). */
    public string $initialSource = 'file';

    public function mount()
    {
        $this->initialSource = request()->query('source') === 'url' ? 'url' : 'file';

        // Limiter aux groupes de l'utilisateur
        if (auth()->user()->hasRole('admin')) {
            $this->groups = Group::all();
            $this->storageLocations = StorageLocation::all();
        } else {
            $this->groups = auth()->user()->groups;
            $this->storageLocations = StorageLocation::where('is_default', true)->get();
        }

        $defaultStorage = StorageLocation::where('is_default', true)->first();
        if ($defaultStorage) {
            $this->storage_id = $defaultStorage->id;
        }
    }

    public function updatedOriginalFileName($value)
    {
        // Pré-remplit le nom depuis le fichier déposé si l'utilisateur n'a rien saisi
        if (empty($this->name) && $value) {
            $this->name = ucfirst(str_replace(['_', '-'], ' ', pathinfo($value, PATHINFO_FILENAME)));
        }
    }

    public function updatedSearchTag()
    {
        if (strlen($this->searchTag) > 0) {
            $this->availableTags = Tag::where('name', 'like', '%'.$this->searchTag.'%')
                ->take(5)
                ->get();
        } else {
            $this->availableTags = [];
        }
    }

    public function addTag($tagName)
    {
        $tagName = is_string($tagName) ? trim($tagName) : '';
        if ($tagName !== '' && mb_strlen($tagName) <= 50 && count($this->selectedTags) < 20 && ! in_array($tagName, $this->selectedTags, true)) {
            $this->selectedTags[] = $tagName;
        }
        $this->searchTag = '';
        $this->availableTags = [];
    }

    public function removeTag($tagName)
    {
        $this->selectedTags = array_values(array_filter($this->selectedTags, fn ($t) => $t !== $tagName));
    }

    private const TAG_RULES = [
        'selectedTags' => ['array', 'max:20'],
        'selectedTags.*' => ['string', 'max:50'],
    ];

    public const ALLOWED_EXTENSIONS = ['iso', 'img', 'bin', 'raw', 'qcow2', 'vhd', 'vhdx', 'vmdk', 'wim', 'esd', 'zip', '7z', 'tar', 'gz', 'xz'];

    /**
     * Vérifie que le groupe et le stockage choisis sont autorisés pour
     * l'utilisateur courant. Retourne false (avec erreur) sinon.
     */
    private function authorizeTargets(): bool
    {
        if (auth()->user()->hasRole('admin')) {
            return true;
        }

        if (! empty($this->group_id) && ! auth()->user()->groups->contains('id', (int) $this->group_id)) {
            $this->addError('group_id', __("app.Vous n'appartenez pas à ce groupe."));

            return false;
        }

        if (! StorageLocation::find($this->storage_id)?->is_default) {
            $this->addError('storage_id', __("app.Cet emplacement de stockage n'est pas autorisé."));

            return false;
        }

        return true;
    }

    public function importFromUrl()
    {
        $this->validate([
            'importUrl' => ['required', 'url:http,https', 'max:2048'],
            'name' => 'required|string|max:255',
            'version' => 'nullable|string|max:50',
            'architecture' => 'nullable|string|max:50',
            'os_type' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'expected_checksum' => ['nullable', 'string', 'regex:/^[a-fA-F0-9]{64}$/'],
            'group_id' => 'nullable|exists:groups,id',
            'storage_id' => 'required|exists:storage_locations,id',
            ...self::TAG_RULES,
        ]);

        if (! $this->authorizeTargets()) {
            return;
        }

        $attributes = [
            'name' => $this->name,
            'version' => $this->version,
            'architecture' => $this->architecture,
            'os_type' => $this->os_type,
            'description' => $this->description,
            'expected_checksum' => $this->expected_checksum ? strtolower($this->expected_checksum) : null,
            'disk' => StorageLocation::find($this->storage_id)->name,
            'user_id' => auth()->id(),
            'group_id' => empty($this->group_id) ? null : $this->group_id,
        ];
        $tags = array_values($this->selectedTags);

        // Suivi visible dans le panneau « Imports en cours »
        $import = IsoImport::create([
            'user_id' => auth()->id(),
            'url' => $this->importUrl,
            'name' => $this->name,
            'options' => ['attributes' => $attributes, 'tags' => $tags],
            'status' => IsoImport::PENDING,
        ]);

        ImportIsoFromUrl::dispatch($this->importUrl, $attributes, $tags, $import->id);

        $this->dispatch('toastMagic',
            status: 'info',
            title: __('app.Import lancé'),
            message: __('app.Suivez sa progression dans « Imports en cours ».')
        );

        return $this->redirect('/dashboard', navigate: true);
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'version' => 'nullable|string|max:50',
            'architecture' => 'nullable|string|max:50',
            'os_type' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'expected_checksum' => ['nullable', 'string', 'regex:/^[a-fA-F0-9]{64}$/'],
            // Seul un fichier assemblé pour l'utilisateur courant peut être finalisé
            'tempFilePath' => ['required', 'string', 'regex:#^temp_iso/'.auth()->id().'_[A-Za-z0-9]{40}$#'],
            'originalFileName' => ['nullable', 'string', 'max:255'],
            ...self::TAG_RULES,
            'group_id' => 'nullable|exists:groups,id',
            'storage_id' => 'required|exists:storage_locations,id',
        ]);

        // La liste affichée est déjà filtrée côté mount(), mais on revalide
        // côté serveur : un payload Livewire modifié ne doit pas permettre
        // d'écrire dans un groupe ou un stockage non autorisé.
        if (! $this->authorizeTargets()) {
            return;
        }

        $disk = StorageLocation::find($this->storage_id)->name;

        $absoluteTempPath = Storage::disk('local')->path($this->tempFilePath);
        if (! file_exists($absoluteTempPath)) {
            $this->addError('tempFilePath', __('app.Le fichier téléversé est introuvable.'));

            return;
        }

        $extension = strtolower(pathinfo($this->originalFileName, PATHINFO_EXTENSION));
        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            $extension = 'iso';
        }

        $size = filesize($absoluteTempPath);
        $finalPath = 'iso_files/'.basename($this->tempFilePath).'.'.$extension;

        // Move to final destination
        $in = fopen($absoluteTempPath, 'rb');
        Storage::disk($disk)->writeStream($finalPath, $in);
        fclose($in);
        unlink($absoluteTempPath);

        $isoFile = IsoFile::create([
            'name' => $this->name,
            'version' => $this->version,
            'architecture' => $this->architecture,
            'os_type' => $this->os_type,
            'description' => $this->description,
            'file_path' => $finalPath,
            'disk' => $disk,
            'size' => $size,
            'checksum' => null, // Calculé en arrière-plan par CalculateIsoChecksum
            'expected_checksum' => $this->expected_checksum ? strtolower($this->expected_checksum) : null,
            'user_id' => auth()->id(),
            'group_id' => empty($this->group_id) ? null : $this->group_id,
        ]);

        if (! empty($this->selectedTags)) {
            $tagIds = [];
            foreach ($this->selectedTags as $tagName) {
                $tag = Tag::firstOrCreate([
                    'name' => $tagName,
                ], [
                    'color' => sprintf('#%06X', mt_rand(0, 0xFFFFFF)),
                ]);
                $tagIds[] = $tag->id;
            }
            $isoFile->tags()->sync($tagIds);
        }

        CalculateIsoChecksum::dispatch($isoFile);

        $this->dispatch('toastMagic',
            status: 'success',
            title: __('app.Succès'),
            message: __('app.Fichier ISO téléversé avec succès.')
        );

        return $this->redirect('/dashboard', navigate: true);
    }

    public function render()
    {
        return view('livewire.iso.upload');
    }
}
