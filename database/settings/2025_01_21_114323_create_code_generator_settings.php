<?php

use App\Enums\ModelTool;
use App\Enums\ProgrammingLanguage;
use App\Enums\LLM;
use App\Enums\Sequence;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('generator.programming_language', ProgrammingLanguage::PHP->value);
        $this->migrator->add('generator.model_tool', ModelTool::NuSMV->value);
        $this->migrator->add('generator.llm_code', LLM::ChatGPT->value);
        $this->migrator->add('generator.llm_formal', LLM::ChatGPT->value);
        $this->migrator->add('generator.llm_validation', LLM::ChatGPT->value);
        $this->migrator->add('generator.iteration', 2);
        $this->migrator->add('generator.sequence', Sequence::Code_Formal_Validation->value);

    }
};
