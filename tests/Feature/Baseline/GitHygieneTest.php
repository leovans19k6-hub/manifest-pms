<?php

namespace Tests\Feature\Baseline;

use Tests\TestCase;

class GitHygieneTest extends TestCase
{
    public function test_local_environment_file_is_ignored_by_git_policy(): void
    {
        $gitignore = file_get_contents(base_path('.gitignore'));

        $this->assertStringContainsString(".env\n", $gitignore);
        $this->assertStringContainsString('/vendor', $gitignore);
        $this->assertStringContainsString('/node_modules', $gitignore);
        $this->assertStringContainsString('/public/build', $gitignore);
        $this->assertStringContainsString('/_skeleton_backup/', $gitignore);
    }
}
