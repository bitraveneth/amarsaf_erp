<?php

namespace Tests\Feature;

use App\Support\Learning\LearningHubRepository;
use Tests\TestCase;

class TroubleshootingLearningHubTest extends TestCase
{
    public function test_troubleshooting_course_explains_when_and_why(): void
    {
        $module = LearningHubRepository::module('troubleshooting');

        $this->assertNotNull($module);
        $this->assertCount(10, $module['troubles'] ?? []);

        foreach ($module['troubles'] as $trouble) {
            $this->assertNotSame('', trim($trouble['when']['en'] ?? ''));
            $this->assertNotSame('', trim($trouble['why']['en'] ?? ''));
            $this->assertNotSame('', trim($trouble['when']['bn'] ?? ''));
            $this->assertNotSame('', trim($trouble['why']['bn'] ?? ''));
        }

        $lessons = collect($module['course']['lessons'] ?? []);
        $this->assertCount(10, $lessons->where('type', 'trouble'));
        $this->assertSame(3, $module['course']['quiz_question_count'] ?? 0);
    }
}
