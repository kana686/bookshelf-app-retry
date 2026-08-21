<?php

namespace Tests\Feature\Relationships;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanRelationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_userリレーションが正しく定義されている()
    {
        $readingPlan = ReadingPlan::has('user')->first();

        if ($readingPlan) {
            $this->assertNotNull($readingPlan->user);
            $this->assertInstanceOf(User::class, $readingPlan->user);
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_bookリレーションが正しく定義されている()
    {
        $readingPlan = ReadingPlan::has('book')->first();

        if ($readingPlan) {
            $this->assertNotNull($readingPlan->book);
            $this->assertInstanceOf(Book::class, $readingPlan->book);
        } else {
            $this->assertTrue(true);
        }
    }
}
