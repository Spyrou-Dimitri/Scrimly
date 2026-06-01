<?php

namespace App\Livewire\Forms;

use App\Enums\DefaultTeam;
use App\Enums\Language;
use App\Enums\LolGoal;
use App\Enums\LolServeur;
use App\Jobs\ProcessUploadImageLogoTeam;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class EditTeamForm extends Form
{
    #[Validate]
    public string $team_name = '';

    #[Validate]
    public ?LolServeur $server = null;

    #[Validate]
    public ?LolGoal $goal = null;

    #[Validate]
    public ?Language $language = null;

    #[Validate]
    public string $description = '';

    #[Validate]
    public $logo = null;

    #[Validate]
    public string $default_logo = 'Demacia';

    public ?Team $team = null;

    public function setTeam(Team $team): void
    {
        $this->team = $team;
        $this->team_name = $team->name;
        $this->server = $team->server;
        $this->goal = $team->goal;
        $this->language = $team->language;
        $this->description = $team->description ?? '';
        $this->default_logo = $team->logo_type === 'default'
            ? $team->logo_value
            : DefaultTeam::DEMACIA->value;
    }

    protected function rules(): array
    {
        return [
            'team_name' => ['required', 'string', 'min:3', 'max:30', Rule::unique('teams', 'name')->ignore($this->team?->id)],
            'server' => ['required', Rule::enum(LolServeur::class)],
            'goal' => ['required', Rule::enum(LolGoal::class)],
            'language' => ['required', Rule::enum(Language::class)],
            'description' => ['nullable', 'string', 'max:1000'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'default_logo' => ['required', Rule::enum(DefaultTeam::class)],
        ];
    }

    public function attributes(): array
    {
        return [
            'team_name' => 'pages/team/edit.team_name',
            'server' => 'pages/team/edit.server',
            'goal' => 'pages/team/edit.goal',
            'language' => 'pages/team/edit.language',
            'description' => 'pages/team/edit.description',
            'logo' => 'pages/team/edit.logo',
            'default_logo' => 'pages/team/edit.default_logo',
        ];
    }

    public function update(bool $applyPresetLogo): string
    {
        Gate::authorize('manageTeam', User::class);

        $validated = $this->validate();

        $logoType = $this->team->logo_type;
        $logoValue = $this->team->logo_value;

        if ($validated['logo']) {
            if ($this->team->logo_type === 'upload' && $this->team->logo_value) {
                $this->deleteStoredUploadLogo($this->team->logo_value);
            }

            $extension = $validated['logo']->extension() ?: $validated['logo']->getClientOriginalExtension();
            $newOriginalFileName = uniqid().'.'.$extension;
            $fullPathToOriginal = $validated['logo']->storeAs(
                config('logoTeam.original_path'),
                $newOriginalFileName,
                ['disk' => config('logoTeam.disk')]
            );

            if ($fullPathToOriginal) {
                ProcessUploadImageLogoTeam::dispatchSync($fullPathToOriginal, $newOriginalFileName);
                $logoType = 'upload';
                $logoValue = $newOriginalFileName;
            }
        } elseif ($applyPresetLogo) {
            if ($this->team->logo_type === 'upload' && $this->team->logo_value) {
                $this->deleteStoredUploadLogo($this->team->logo_value);
            }

            $logoType = 'default';
            $logoValue = DefaultTeam::from($validated['default_logo'])->value;
        }

        $newSlug = Str::slug($validated['team_name']);

        $this->team->update([
            'name' => $validated['team_name'],
            'slug' => $newSlug,
            'description' => $validated['description'],
            'language' => $validated['language'],
            'server' => $validated['server'],
            'goal' => $validated['goal'],
            'logo_type' => $logoType,
            'logo_value' => $logoValue,
        ]);

        return $newSlug;
    }

    private function deleteStoredUploadLogo(string $filename): void
    {
        $disk = Storage::disk(config('logoTeam.disk'));
        $disk->delete(config('logoTeam.original_path').'/'.$filename);

        foreach (config('logoTeam.sizes', []) as $size) {
            $directory = sprintf(config('logoTeam.variant_pattern'), $size['width'], $size['height']);
            $disk->delete($directory.'/'.$filename);
        }
    }
}
