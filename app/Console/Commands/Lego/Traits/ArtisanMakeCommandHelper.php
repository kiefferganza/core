<?php

namespace App\Console\Commands\Lego\Traits;

use Illuminate\Support\Arr;

trait ArtisanMakeCommandHelper
{
  use CanListModules;

  private function makeNamespaceOptions()
  {
    return [
      'cast',
      'channel',
      'command',
      'component',
      'controller',
      'enum',
      'event',
      'exception',
      'factory',
      'filament-cluster',
      'filament-exporter',
      'filament-importer',
      'filament-issue',
      'filament-page',
      'filament-panel',
      'filament-relation-manager',
      'filament-resource',
      'filament-settings-page',
      'filament-theme',
      'filament-user',
      'filament-widget',
      'form-field',
      'form-layout',
      'infolist-entry',
      'infolist-layout',
      'job',
      'listener',
      'livewire',
      'livewire-form',
      'livewire-table',
      'mail',
      'middleware',
      'migration',
      'model',
      'module',
      'notification',
      'observer',
      'policy',
      'provider',
      'request',
      'resource',
      'rule',
      'scope',
      'seeder',
      'setting',
      'settings-migration',
      'table-column',
      'test',
      'view',
    ];
  }

  private function makeNamespaceChoice()
  {
    return $this->generateChoices('Select make command', $this->makeNamespaceOptions());
  }

  private function getFileName()
  {
    $fileName = null;

    while (empty($fileName)) $fileName = $this->ask('What is the file name?');

    return $fileName;
  }

  private function getFlags()
  {
    return $this->ask('Make request flag(s) (optional)');
  }

  private function showCatalog()
  {
    $this->readModulesManifestFile();

    if ($this->hasNoModules()) return;

    $modules = Arr::pluck($this->getModules(), 'name');

    return $this->generateChoices('Select Module', $modules);
  }

  private function generateChoices($question, $choices, $multiple = false)
  {
    return $this->choice(
      question: $question,
      choices: $choices,
      multiple: $multiple
    );
  }
}
